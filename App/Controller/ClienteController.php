<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;

class ClienteController extends Action{

  public function index(){
    session_start();
    if($_SESSION['user_role'] === "CLIENTE"){
      $Chamado = Container::getClass("Chamado");
      $openedServiceRequests = $Chamado->getUserOpenedRequests($_SESSION['user_id']);
      $this->view->openedServiceRequests = $openedServiceRequests;
      $this->render('clientes');
    }else{
      $this->forbidenAccess();
    }
  }

  public function solicitar_atendimento(){
    session_start();
    if($_SESSION['user_role'] == "CLIENTE"){
      $SolicitarChamado = Container::getClass("SolicitarChamado");
      $serviceRequests = $SolicitarChamado->getUserServiceRequests($_SESSION['user_id']);
      $this->view->serviceRequests = $serviceRequests;
      $this->render('cliente_chamado_request');
    }else{
      $this->forbidenAccess();
    }
  }

  public function client_register_call_request(){
    $id_servico = $_POST['id_servico'];
    $id_local = $_POST['id_local'];
    $descricao = $_POST['descricao'];

    session_start();
    $id_usuario = $_SESSION['user_id'];

    $requisicao = Container::getClass("SolicitarChamado");
    $requisicao->save($id_usuario,$id_servico,$id_local,$descricao);
    header('Location: ./solicitar_atendimento');

  }

  public function client_request_history() {
      session_start();
      if($_SESSION['user_role'] === "CLIENTE") {
        $Chamado = Container::getClass("Chamado");
        $openedServiceRequests = $Chamado->getUserOpenedRequests($_SESSION['user_id']);
        $this->view->openedServiceRequests = $openedServiceRequests;
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
