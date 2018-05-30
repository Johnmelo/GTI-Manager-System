<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;

class ClienteController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] === "CLIENTE"){

      //LOADING AND PREPARE INFORMATIONS ABOUT CLIENT REQUESTS
      $chamado = Container::getClass("Chamado");
      $chamados = $chamado->getChamadosByColumn("id_cliente_solicitante", $_SESSION['user_id']);
      $chamados_abertos = [];
      $chamados_atendimento = [];
      foreach ($chamados as $request) {
        if($request['status'] == "AGUARDANDO"){
          $chamados_abertos[] = $request;
        }elseif ($request['status'] == "ATENDIMENTO") {
          $chamados_atendimento[] = $request;
        }
      }
      //-------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT CLIENT SERVICES
      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $array_servicos_names = [];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }
      //-------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT USERS TO IDENTIFY TECHNICIAN NAMES
      $user = Container::getClass("Usuario");
      $users = $user->fetchAll();
      $array_users_names = [];
      foreach ($users as $client) {
        $array_users_names[$client['id']]['nome'] = $client['nome'];
      }
      $array_users_names['NULL']['nome'] = "-";
      //--------------------------------------------------------

      //ATRIBUING VALUES TO THE VIEW CLIENT
      $this->view->chamados_abertos = $chamados_abertos;
      $this->view->chamados_atendimento = $chamados_atendimento;
      $this->view->services_names = $array_servicos_names;
      $this->view->users_names = $array_users_names;
      $this->render('clientes');
      //--------------------------------------------------------

    }else{
      $this->forbidenAccess();
    }

  }

  public function solicitar_atendimento(){
    session_start();
    if($_SESSION['user_role'] == "CLIENTE"){

      $servico = Container::getClass("Servico");
      $requisicao = Container::getClass("SolicitarChamado");
      $servicos = $servico->fetchAll();
      $requisicoes = $requisicao->fetchAll();

      $array_servicos_names = [];
      $myRequests =[];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }

      foreach ($requisicoes as $request) {
        if($request['id_cliente'] == $_SESSION['user_id']){
          $myRequests[] = $request;
        }
      }

      $servicos = $servico->fetchAll();
      $this->view->requisicoes = $myRequests;
      $this->view->servicos = $servicos;
      $this->view->requests_services_names = $array_servicos_names;

      $this->render('cliente_chamado_request');
    }else{
      $this->forbidenAccess();
    }

  }

  public function client_register_call_request(){
    $id_servico = $_POST['servico'];
    $descricao = $_POST['descricao'];

    session_start();
    $id_usuario = $_SESSION['user_id'];

    $requisicao = Container::getClass("SolicitarChamado");
    $requisicao->save($id_usuario,$id_servico,$descricao);
    header('Location: ./solicitar_atendimento');

  }

  public function client_request_history() {
      session_start();
      if($_SESSION['user_role'] === "CLIENTE") {
        $requestDb = Container::getClass("Chamado");
        $requests = $requestDb->fetchAll();
        $myRequests = [];

        foreach ($requests as $request) {
          if($request['id_cliente_solicitante'] == $_SESSION['user_id']){
            $myRequests[] = $request;
          }
        }

        $userDb = Container::getClass("Usuario");
        $users = $userDb->fetchAll();
        $user_info=[];
        foreach ($users as $user) {
          $user_info[$user['id']]['nome'] = $user['nome'];
          // $user_info[$user['id']]['setor'] = $user['setor'];
        }

        //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
        $servico = Container::getClass("Servico");
        $servicos = $servico->fetchAll();
        $array_servicos_names = [];
        foreach ($servicos as $service) {
          $array_servicos_names[$service['id']] = $service['nome'];
        }
        //-------------------------------------------------------

        $this->view->requests = $myRequests;
        $this->view->user_info = $user_info;
        $this->view->service_names = $array_servicos_names;
        $this->render('cliente_historico');
      } else {
          $this->forbidenAccess();
      }
  }

  public function cliente_account_settings () {
      session_start();
      if($_SESSION['user_role'] === "CLIENTE") {
          $this->render('cliente_account_settings');
      } else {
          $this->forbidenAccess();
      }
  }

  public function change_password(){
    session_start();
    if($_SESSION['user_role'] === "CLIENTE") {
      if(isset($_POST['current_password']) && isset($_POST['new_password'])){
        $pass = $_POST['current_password'];
        $userDb = Container::getClass("Usuario");
        $user = $userDb->findById($_SESSION['user_id']);
        if($pass == $user['password']){
          $userDb->updateColumnById("password",$_POST['new_password'],$_SESSION['user_id']);
        }
      }
    } else {
        $this->forbidenAccess();
    }
  }

  public function client_cancel_call_request() {
      session_start();
      if ($_SESSION['user_role'] === 'CLIENTE') {
          if (isset($_POST['request_id_list']) && isset($_POST['requestType'])) {
              $solicitacao = ($_POST['requestType'] === "pending_acceptance") ? Container::getClass("SolicitarChamado") : Container::getClass("Chamado");
              foreach ($_POST['request_id_list'] as $id) {
                  $solicitacao->updateColumnById("status", "CANCELADA", $id);
              }
          } else {
              header("HTTP/1.1 400 Bad Request");
          }
      } else {
          $this->forbidenAccess();
      }
  }


}
?>
