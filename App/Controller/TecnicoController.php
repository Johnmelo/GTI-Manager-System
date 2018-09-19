<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \App\Model\PasswordUtil;

class TecnicoController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){

      //LOADING AND PREPARE INFORMATIOS ABOUT CALL REQUEST
      $chamado = Container::getClass("Chamado");
      $chamados = $chamado->fetchAll();
      $chamados_abertos = $chamado->getChamadosByStatus("AGUARDANDO");
      $chamados_atendimentos = $chamado->getChamadosByStatus("ATENDIMENTO");
      $chamados_finalizados = $chamado->getChamadosByStatus("FINALIZADO");
      $myRequests = [];
      $myRequestsFinished = [];
      foreach ($chamados_atendimentos as $request) {
        if($request['id_tecnico_responsavel'] == $_SESSION['user_id']){
          $myRequests[] = $request;
        }
      }
      foreach ($chamados_finalizados as $request) {
        if($request['id_tecnico_responsavel'] == $_SESSION['user_id']){
          $myRequestsFinished[] = $request;
        }
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT USERS TO IDENTIFY
      //OPEN REQUEST TECHNICIAN AND CLIENT THAT HAVE REQUESTED
      $user = Container::getClass("Usuario");
      $users = $user->fetchAll();
      $array_users_names = [];
      foreach ($users as $client) {
        $array_users_names[$client['id']]['nome'] = $client['nome'];
        $array_users_names[$client['id']]['setor'] = $client['setor'];
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $array_servicos_names = [];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }
      //-------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT CALL REQUEST
      $requisicao_atendendimento = Container::getClass("SolicitarChamado");
      $requisicoes_atendimento = $requisicao_atendendimento->fetchAll();
      $requisicoes_atendimento_aguardando = [];
      foreach ($requisicoes_atendimento as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_atendimento_aguardando[] = $request;
        }
      }
     //---------------------------------------------------

     $local = Container::getClass("Local");
     $locais = $local->fetchAll();
     $array_locais = [];
     foreach ($locais as $local) {
         $array_locais[$local['id']]['nome'] = $local['nome'];
         $array_locais[$local['id']]['tipo'] = $local['tipo'];
         $array_locais[$local['id']]['ativo'] = $local['ativo'];
     }
    //---------------------------------------------------

      //SENDING VALUES TO TECHNICIAN VIEW PAGE
      $this->view->myRequests = $myRequests;
      $this->view->myRequestsFinished = $myRequestsFinished;
      $this->view->openRequests = $chamados_abertos;
      $this->view->requisicoes_atendimento = $requisicoes_atendimento_aguardando;
      $this->view->users_names = $array_users_names;
      $this->view->service_names = $array_servicos_names;
      $this->view->requests_attendance = $chamados_atendimentos;
      $this->view->locais = $array_locais;
      //------------------------------------------------------------------------

      //RENDERING PAGE
      $this->render('tecnicos');
      //------------------------------------------------------------------------

    }else{
      $this->forbidenAccess();
    }
  }

  public function technician_select_request(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){

      //LOADING AND PREPARE INFORMATIONS ABOUT USERS TO IDENTIFY
      //OPEN REQUEST TECHNICIAN AND CLIENT THAT HAVE REQUESTED
      $user = Container::getClass("Usuario");
      $users = $user->fetchAll();
      $users_names = [];
      foreach ($users as $client) {
        $users_names[$client['id']]['nome'] = $client['nome'];
        $users_names[$client['id']]['setor'] = $client['setor'];
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $servicos_names = [];
      foreach ($servicos as $service) {
        $servicos_names[$service['id']] = $service['nome'];
      }
      //-------------------------------------------------------

      if(isset($_POST['btnDetail'])){

        //GET INFORMATIONS ABOUT REQUEST
        $requestDb = Container::getClass("Chamado");
        $request = $requestDb->findById($_POST['btnDetail']);
        //------------------------------------------------------------------------

        $this->view->request = $request;
        $this->view->users = $users_names;
        $this->view->services = $servicos_names;

        $this->render('technician_view_open_request');
      }elseif(isset($_POST['btnJoin'])){

        $requestDb = Container::getClass("Chamado");
        $requestDb->updateColumnById("id_tecnico_responsavel",$_SESSION['user_id'],$_POST['btnJoin']);
        $requestDb->updateColumnById("status","ATENDIMENTO",$_POST['btnJoin']);
        $requestDb->updateColumnById("prazo",$_POST['prazo'],$_POST['btnJoin']);
        header('Location: /gticchla/public/tecnico');
      }
    }else{
      $this->forbidenAccess();
    }

  }

  public function technician_history () {
      session_start();
      if($_SESSION['user_role'] === "TECNICO") {

        $requestDb = Container::getClass("Chamado");
        $requests = $requestDb->fetchAll();
        $requestsFinished = [];

        foreach ($requests as $request) {
          if(($request['id_tecnico_responsavel'] == $_SESSION['user_id']) && ($request['status'] == "FINALIZADO")){
            $requestsFinished[] = $request;
          }
        }

        $userDb = Container::getClass("Usuario");
        $users = $userDb->fetchAll();
        $user_info=[];
        foreach ($users as $user) {
          $user_info[$user['id']]['nome'] = $user['nome'];
          $user_info[$user['id']]['setor'] = $user['setor'];
        }

        //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
        $servico = Container::getClass("Servico");
        $servicos = $servico->fetchAll();
        $array_servicos_names = [];
        foreach ($servicos as $service) {
          $array_servicos_names[$service['id']] = $service['nome'];
        }
        //-------------------------------------------------------

        // LOAD AND PREPARE PLACES INFORMATION
        $local = Container::getClass("Local");
        $locais = $local->fetchAll();
        $array_locais = [];
        foreach ($locais as $local) {
            $array_locais[$local['id']]['nome'] = $local['nome'];
            $array_locais[$local['id']]['tipo'] = $local['tipo'];
            $array_locais[$local['id']]['ativo'] = $local['ativo'];
        }
        //---------------------------------------------------

        $this->view->requests = $requestsFinished;
        $this->view->user_info = $user_info;
        $this->view->service_names = $array_servicos_names;
        $this->view->locais = $array_locais;
        $this->render('technician_request_history');
      } else {
          $this->forbidenAccess();
      }
  }

  public function technician_account_settings () {
      session_start();
      if($_SESSION['user_role'] === "TECNICO") {
          $this->render('technician_account_settings');
      } else {
          $this->forbidenAccess();
      }
  }

  public function change_password(){
    session_start();
    if(($_SESSION['user_role'] === "GERENTE")||($_SESSION['user_role'] === "CLIENTE")||($_SESSION['user_role'] === "TECNICO")) {
      if(isset($_POST['current_password']) && isset($_POST['new_password'])){
        $pass = $_POST['current_password'];
        $userDb = Container::getClass("Usuario");
        $user = $userDb->findById($_SESSION['user_id']);
        $isCorrectPw = PasswordUtil::verify($pass, $user['password_hash']);
        if($isCorrectPw){
          ob_start();
          // Generate the hash of the new password ...
          $new_hash = PasswordUtil::hash($_POST['new_password']);
          // ... and store it
          $userDb->updateColumnById("password_hash",$new_hash,$_SESSION['user_id']);
          // Clean the buffer so it doesn't get sent to the Ajax call instead of the JSON below.
          $buffer = ob_get_clean();
          header('Content-Type: application/json; charset=UTF-8');
          if (strpos($buffer, "UPDATED successfully") !== false) {
            echo json_encode(array('event' => 'success', 'type' => 'password_reset', 'message' => 'Senha alterada com sucesso'));
          } else {
            header("HTTP/1.1 500 Internal Server Error");
            echo json_encode(array('event' => 'error', 'type' => 'db_error', 'message' => $buffer));
          }
      } else {
        ob_end_clean();
        header("HTTP/1.1 401 Unauthorized");
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('event' => 'error', 'type' => 'wrong_current_password', 'message' => 'Senha atual incorreta'));
      }
      }
    } else {
        $this->forbidenAccess();
    }
  }

    public function refuse_support_request() {
        session_start();
        if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
            if (isset($_POST['request_id']) && isset($_POST['refusal_reason'])) {
                $date = new \DateTime("now", new \DateTimeZone('America/Fortaleza'));
                $date = $date->format("Y-m-d H:i:s");
                $request = Container::getClass("SolicitarChamado");
                $request->updateColumnById("status", "RECUSADA", $_POST['request_id']);
                $request->updateColumnById("data_recusado", $date, $_POST['request_id']);
                $request->updateColumnById("id_recusante", $_SESSION['user_id'], $_POST['request_id']);
                $request->updateColumnById("motivo_recusa", $_POST['refusal_reason'], $_POST['request_id']);
            }
        } else {
            $this->forbidenAccess();
        }
    }
}
?>
