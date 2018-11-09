<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \App\Model\Token;
use \SON\Db\DBConnector;

class ClienteController extends Action{

  public function index(){
    session_start();
    if($_SESSION['user_role'] === "CLIENTE"){
      // Get the token for WebSocket
      $token = new Token($_SESSION['user_id']);

      $Chamado = Container::getClass("Chamado");
      $inQueueTickets = $Chamado->getUsersInQueueTickets($_SESSION['user_id']);
      $inProcessTickets = $Chamado->getUsersInProcessTickets($_SESSION['user_id']);
      $this->view->inQueueTickets = $inQueueTickets;
      $this->view->inProcessTickets = $inProcessTickets;
      $this->view->token = \json_encode($token->data);
      $this->render('clientes');
    }else{
      $this->forbidenAccess();
    }
  }

  public function solicitar_atendimento(){
    session_start();
    if($_SESSION['user_role'] == "CLIENTE"){
      // Get the token for WebSocket
      $token = new Token($_SESSION['user_id']);

      $SolicitarChamado = Container::getClass("SolicitarChamado");
      $activeTicketRequests = $SolicitarChamado->getUsersActiveTicketRequests($_SESSION['user_id']);
      $this->view->activeTicketRequests = $activeTicketRequests;
      $this->view->token = \json_encode($token->data);
      $this->render('cliente_chamado_request');
    }else{
      $this->forbidenAccess();
    }
  }

  public function client_register_call_request(){
    try {
      if (
        isset($_POST['id_servico']) &&
        isset($_POST['id_local']) &&
        (isset($_POST['descricao']) && !preg_match('/^\s*$/', $_POST['descricao']))
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
          throw new \Exception();
        }
      } else {
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'insuficient_data')));
      }
    } catch (\Exception $e) {
      header('Content-Type: application/json; charset=UTF-8');
      header('HTTP/1.1 400');
      die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
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

  public function client_cancel_call_request() {
      session_start();
      if ($_SESSION['user_role'] === 'CLIENTE') {
        try {
          if (isset($_POST['request_id']) && isset($_POST['requestType'])) {
            $solicitacao = ($_POST['requestType'] === "pending_acceptance") ? Container::getClass("SolicitarChamado") : Container::getClass("Chamado");
            $updated = $solicitacao->updateColumnById("status", "CANCELADA", $_POST['request_id']);

            // Return the ticket or ticket request back'
            if ($updated) {
              if ($_POST['requestType'] === "open_request") {
                $ticket = $solicitacao->getTicketById($_POST['request_id']);
                if ($ticket) {
                  header('Content-Type: application/json; charset=UTF-8');
                  echo json_encode(array('event' => 'success', 'type' => 'cancelled_ticket', 'ticket' => $ticket));
                }
              } else {
                $request = $solicitacao->getTicketRequestById($_POST['request_id']);
                if ($request) {
                  header('Content-Type: application/json; charset=UTF-8');
                  echo json_encode(array('event' => 'success', 'type' => 'cancelled_ticket_request', 'request' => $request));
                }
              }
            } else {
              throw new \Exception();
            }
          } else {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'missing_data')));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 500');
          die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
        }
      } else {
          $this->forbidenAccess();
      }
  }
}
?>
