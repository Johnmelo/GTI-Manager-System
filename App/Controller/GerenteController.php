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
      if (isset($_POST['requests'])) {
        foreach ($_POST['requests'] as $request) {
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
      } elseif ($_POST['new_user'] && $_POST['new_user']['idSolicitacao']) {
        $request = $_POST['new_user'];

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
      } elseif ($_POST['new_user']) {
        $request = $_POST['new_user'];

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
        $today = getdate();
        $date = ''.$today['year'].'-'.$today['mon'].'-'.$today['mday'];

        foreach ($_POST['selections_call_request'] as $id_request) {
          $requisicao_acessoDb = Container::getClass("SolicitarChamado");
          $requisicao = $requisicao_acessoDb->findById($id_request);

          $chamadoDb = Container::getClass("Chamado");
          $chamadoDb->save($requisicao['id_servico'],$requisicao['id'],$date,$_SESSION['user_id'],$requisicao['id_cliente'],$requisicao['descricao']);
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
}
?>
