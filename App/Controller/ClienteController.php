<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;

class ClienteController extends Action{

  public function index(){
    session_start();
    if($_SESSION['user_role'] === "CLIENTE"){
      $Chamado = Container::getClass("Chamado");
      $openTickets = $Chamado->getUsersOpenTickets($_SESSION['user_id']);
      $this->view->openTickets = $openTickets;
      $this->render('clientes');
    }else{
      $this->forbidenAccess();
    }
  }

  public function solicitar_atendimento(){
    session_start();
    if($_SESSION['user_role'] == "CLIENTE"){
      $SolicitarChamado = Container::getClass("SolicitarChamado");
      $activeTicketRequests = $SolicitarChamado->getUsersActiveTicketRequests($_SESSION['user_id']);
      $this->view->activeTicketRequests = $activeTicketRequests;
      $this->render('cliente_chamado_request');
    }else{
      $this->forbidenAccess();
    }
  }

  public function client_register_call_request(){
      if (
          isset($_POST['id_servico']) &&
          isset($_POST['id_local']) &&
          (isset($_POST['descricao']) && preg_match('/^\S*$/', $_POST['descricao']))
      ){
          $id_servico = $_POST['id_servico'];
          $id_local = $_POST['id_local'];
          $descricao = $_POST['descricao'];

          session_start();
          $id_usuario = $_SESSION['user_id'];

          $SolicitarChamado = Container::getClass("SolicitarChamado");
          $requestId = $SolicitarChamado->save($id_usuario,$id_servico,$id_local,$descricao);
          if ($requestId !== false) {
            $request = $SolicitarChamado->getTicketRequestById($requestId);
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'new_ticket_request', 'request' => $request));
          } else {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
          }
      } else {
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'insuficient_data')));
      }
  }

  public function client_request_history() {
      session_start();
      if($_SESSION['user_role'] === "CLIENTE") {
        $Chamado = Container::getClass("Chamado");
        $inactiveTickets = $Chamado->getUsersInactiveTickets($_SESSION['user_id']);
        $this->view->inactiveTickets = $inactiveTickets;
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
                  // $solicitacao->updateColumnById("status", "CANCELADA", $id);
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
