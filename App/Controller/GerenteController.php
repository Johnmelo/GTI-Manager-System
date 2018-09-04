<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \App\Model\Email;

class GerenteController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){

      $requisicao_acesso = Container::getClass("SolicitarAcesso");
      $requisicoes = $requisicao_acesso->fetchAll();
      $requisicoes_acesso_aguardando = [];
      foreach ($requisicoes as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_acesso_aguardando[] = $request;
        }
      }

      $chamado = Container::getClass("Chamado");
      $chamados = $chamado->fetchAll();
      $chamados_abertos = $chamado->getChamadosByStatus("AGUARDANDO");
      $chamados_atendimentos = $chamado->getChamadosByStatus("ATENDIMENTO");
      $chamados_finalizados = $chamado->getChamadosByStatus("FINALIZADO");
      $count = count($chamados);

      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $array_servicos_names = [];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }

      $requisicao_atendendimento = Container::getClass("SolicitarChamado");
      $requisicoes_atendimento = $requisicao_atendendimento->fetchAll();
      $requisicoes_atendimento_aguardando = [];
      foreach ($requisicoes_atendimento as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_atendimento_aguardando[] = $request;
        }
      }

      $cliente = Container::getClass("Usuario");
      $clientes = $cliente->fetchAll();
      $array_clients_names = [];
      foreach ($clientes as $client) {
        $array_clients_names[$client['id']]['nome'] = $client['nome'];
        $array_clients_names[$client['id']]['setor'] = $client['setor'];
      }

      //atribuindo para a view
      $this->view->clients_names = $array_clients_names;
      $this->view->service_names = $array_servicos_names;
      $this->view->requisicoes_atendimento = $requisicoes_atendimento_aguardando;
      $this->view->requisicoes = $requisicoes_acesso_aguardando;
      $this->view->chamados = $chamados;
      $this->view->chamados_abertos = $chamados_abertos;
      $this->view->chamados_atendimentos = $chamados_atendimentos;
      $this->view->chamados_finalizados = $chamados_finalizados;
      $this->view->count = $count - 1;
      $this->render('gerentes');
    }else{
      $this->forbidenAccess();
    }

  }

  public function cadastro_usuario_index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      $requisicao_acesso = Container::getClass("SolicitarAcesso");
      $requisicoes = $requisicao_acesso->fetchAll();
      $requisicoes_aguardando = [];

      foreach ($requisicoes as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_aguardando[] = $request;
        }
      }

      $this->view->requisicoes = $requisicoes_aguardando;
      $this->render('cadastro_usuario_index');
    }else{
      $this->forbidenAccess();
    }


  }

  public function cadastrar_usuarios(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      // Check if wat was submitted is a list of accounts selected, a new
      // user manually created or a request which was edited

      // If it's a list of selected requests
      if (isset($_POST['requests'])) {
        $invalidEmails = [];
        $alreadyInUseEmails = [];
        $alreadyInUseLogins = [];
        $alreadyInUseRegistrationNumbers = [];
        foreach ($_POST['requests'] as $request) {
          // Register only users with unique email, username and
          // registration number and with valid email
          if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            array_push($invalidEmails, $request['email']);
          }
          if ($this->isLoginInUse($request['usuario']) == true) {
            array_push($alreadyInUseLogins, $request['usuario']);
          }
          if ($this->isEmailInUse($request['email']) == true) {
            array_push($alreadyInUseEmails, $request['email']);
          }
          if ($this->isRegistrationNumberInUse($request['matricula']) == true) {
            array_push($alreadyInUseRegistrationNumbers, $request['matricula']);
          }
          if (filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
            if ($this->isLoginInUse($request['usuario']) == false) {
              if ($this->isEmailInUse($request['email']) == false) {
                if ($this->isRegistrationNumberInUse($request['matricula']) == false) {
                  // client admission
                  $clienteDb = Container::getClass("Usuario");
                  $clienteDb->save($request['nome'],$request['email'],$request['usuario'],$request['setor'],$request['matricula']);
                  $requisicao_acessoDb = Container::getClass("SolicitarAcesso");
                  $requisicao_acessoDb->updateColumnById("status","ATENDIDA",$request['idSolicitacao']);

                  // role
                  $cliente_role = $clienteDb->findByLogin($request['usuario']);
                  $user_role =  Container::getClass("UsuarioRole");
                  $user_role->save($cliente_role['id'],1,0,0);

                  // Send email
                  $email = new Email();
                  $email->requestGrantedNotification($request['nome'],$request['email']);
                }
              }
            }
          }
        }
        $failedData = array( 'invalid_emails'=> $invalidEmails, 'used_logins' => $alreadyInUseLogins, 'used_emails' => $alreadyInUseEmails, 'used_registration_numbers' => $alreadyInUseRegistrationNumbers);
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'prevented_registrations', 'data' => $failedData)));

      // If it's a edited request
      } elseif ($_POST['new_user'] && $_POST['new_user']['idSolicitacao']) {
        $request = $_POST['new_user'];
        // Check if email is valid and if username, email and
        // registration number are unique
        if (filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
          if ($this->isLoginInUse($request['usuario']) == false) {
            if ($this->isEmailInUse($request['email']) == false) {
              if ($this->isRegistrationNumberInUse($request['matricula']) == false) {
                // client admission
                $clienteDb = Container::getClass("Usuario");
                $clienteDb->save($request['nome'],$request['email'],$request['usuario'],$request['setor'],$request['matricula']);
                $requisicao_acessoDb = Container::getClass("SolicitarAcesso");
                $requisicao_acessoDb->updateColumnById("status","ATENDIDA",$request['idSolicitacao']);

                // role
                $cliente_role = $clienteDb->findByLogin($request['usuario']);
                $user_role =  Container::getClass("UsuarioRole");
                $user_role->save($cliente_role['id'],$request['isClient'],$request['isTechnician'],$request['isAdmin']);

                // Send email
                $email = new Email();
                $email->requestGrantedNotification($request['nome'],$request['email']);
              } else {
                // Error: the submitted registration number is already in use
                header('Content-Type: application/json; charset=UTF-8');
                header('HTTP/1.1 400');
                echo json_encode(array('event' => 'error', 'type' => 'registration_number_already_in_use'));
              }
            } else {
              // Error: the submitted email is already in use
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 400');
              echo json_encode(array('event' => 'error', 'type' => 'email_already_in_use'));
            }
          } else {
            // Error: the submitted login is already in use
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            echo json_encode(array('event' => 'error', 'type' => 'login_already_in_use'));
          }
        } else {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'invalid_email')));
        }

        // If it's a manually inserted user
      } elseif ($_POST['new_user']) {
        $request = $_POST['new_user'];
        // Check if email is valid and if username, email and
        // registration number are unique
        if (filter_var($request['email'], FILTER_VALIDATE_EMAIL)) {
          if ($this->isLoginInUse($request['usuario']) == false) {
            if ($this->isEmailInUse($request['email']) == false) {
              if ($this->isRegistrationNumberInUse($request['matricula']) == false) {
                // Client insertion
                $clienteDb = Container::getClass("Usuario");
                $clienteDb->save($request['nome'],$request['email'],$request['usuario'],$request['setor'],$request['matricula']);

                // role
                $cliente_role = $clienteDb->findByLogin($request['usuario']);
                $user_role =  Container::getClass("UsuarioRole");
                $user_role->save($cliente_role['id'],$request['isClient'],$request['isTechnician'],$request['isAdmin']);

                // Send email
                $email = new Email();
                $email->requestGrantedNotification($request['nome'],$request['email']);
              } else {
                header('Content-Type: application/json; charset=UTF-8');
                header('HTTP/1.1 400');
                die(json_encode(array('event' => 'error', 'type' => 'registration_number_already_in_use')));
              }
            } else {
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 400');
              die(json_encode(array('event' => 'error', 'type' => 'email_already_in_use')));
            }
          } else {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'login_already_in_use')));
          }
        } else {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          echo json_encode(array('event' => 'error', 'type' => 'invalid_email'));
        }
      }

    } else {
      $this->forbidenAccess();
    }
  }

  public function archive_account_request(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      if (isset($_POST['reason']) && isset($_POST['request_list']) && isset($_POST['send_email'])) {
        if ($_POST['send_email'] == "true" && (!isset($_POST['email_message']) || preg_match('/^\S*$/', $_POST['email_message']))) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'insuficient_email_data')));
        } else {
          foreach ($_POST['request_list'] as $request) {
            // Archive request

            // Update request row
            $date = new \DateTime("now", new \DateTimeZone('America/Fortaleza'));
            $date = $date->format("Y-m-d H:i:s");
            $requisicao_acessoDb = Container::getClass("SolicitarAcesso");
            $requisicao_acessoDb->updateColumnById("status","ARQUIVADA", $request['idSolicitacao']);
            $requisicao_acessoDb->updateColumnById("data_recusado", $date, $request['idSolicitacao']);
            $requisicao_acessoDb->updateColumnById("id_recusante", $_SESSION['user_id'], $request['idSolicitacao']);
            $requisicao_acessoDb->updateColumnById("motivo_recusa", $_POST['reason'], $request['idSolicitacao']);

            // Send email to requester if so chosen
            if ($_POST['send_email'] == "true") {
               $email = new Email();
               $email->requestRefusedNotification($request['nome'],$request['email'], $_POST['email_message']);
            }
          }
        }
      } else {
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'insuficient_data')));
      }
    } else {
      $this->forbidenAccess();
    }
  }

  public function finalize_request(){
    session_start();
    if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")){
      if((isset($_POST['technical_opinion']))&&(isset($_POST['request_id']))){
        $id = $_POST['request_id'];
        $parecer = $_POST['technical_opinion'];
        $status = "FINALIZADO";
        $today = getdate();
        $data_finalizado = ''.$today['year'].'-'.$today['mon'].'-'.$today['mday'];
        $chamadoDb = Container::getClass("Chamado");
        $chamadoDb->updateColumnById("status",$status,$id);
        $chamadoDb->updateColumnById("parecer_tecnico",$parecer,$id);
        $chamadoDb->updateColumnById("data_finalizado",$data_finalizado,$id);
      }
    }
  }
  public function open_call_request(){
    session_start();
    if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")){
      if(isset($_POST['admin_open_client_call_request']) && isset($_POST['selections_call_request'])){
        foreach ($_POST['selections_call_request'] as $id_request) {
          $requisicao_acessoDb = Container::getClass("SolicitarChamado");
          $requisicao = $requisicao_acessoDb->findById($id_request);

          $chamadoDb = Container::getClass("Chamado");
          $chamadoDb->save($requisicao['id_servico'],$requisicao['id'],$_SESSION['user_id'],$requisicao['id_cliente'],$requisicao['descricao']);
          $request = Container::getClass("SolicitarChamado");
          $request->updateColumnById("status","ATENDIDA",$id_request);
        }
        echo "<script>alert('Dados cadastrados!');</script>";
        header('Location: /gticchla/public/');

      }else{
        echo "<script>alert('Não existe requisição aguardando ou não foi selecionada alguma para atender!'); history.back();</script>";
      }
    }else{
      $this->forbidenAccess();
    }
  }
  // public function change_password(){
  //   session_start();
  //   if(($_SESSION['user_role'] === "GERENTE")||($_SESSION['user_role'] === "CLIENTE")||($_SESSION['user_role'] === "TECNICO")) {
  //     if(isset($_POST['current_password']) && isset($_POST['new_password'])){
  //       $pass = $_POST['current_password'];
  //       $userDb = Container::getClass("Usuario");
  //       $user = $userDb->findById($_SESSION['user_id']);
  //       if($pass == $user['password']){
  //         $userDb->updateColumnById("password",$_POST['new_password'],$_SESSION['user_id']);
  //       }
  //     }
  //   } else {
  //       $this->forbidenAccess();
  //   }
  // }

  private function isLoginInUse($login) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByLogin($login);
      if ($usuario == false) {
          return false;
      } else {
          return true;
      }
  }

  private function isEmailInUse($email) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByEmail($email);
      if ($usuario == false) {
        return false;
      } else {
        return true;
      }
  }

  private function isRegistrationNumberInUse($matricula) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByRegistrationNumber($matricula);
      if ($usuario == false) {
        return false;
      } else {
        return true;
      }
  }
}
?>
