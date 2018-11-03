<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \App\Model\PasswordUtil;
use \App\Model\Token;

class TecnicoController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){
        // Get the token for WebSocket
        $token = new Token($_SESSION['user_id']);

        $SolicitarChamado = Container::getClass("SolicitarChamado");
        $activeTicketRequests = $SolicitarChamado->getActiveTicketRequests();

        $Chamado = Container::getClass("Chamado");
        $inQueueTickets = $Chamado->getInQueueTickets();
        $inProcessTickets = $Chamado->getInProcessTickets();

        $techniciansInProcessTickets = [];
        $otherTechniciansInProcessTickets = [];

        $techniciansInProcessTickets = \array_filter($inProcessTickets, function($ticket) {
          $inProcess = ($ticket["status"] === "ATENDIMENTO");
          $techniciansTicket = ($ticket["id_tecnico_responsavel"] === $_SESSION["user_id"]);
            return ($inProcess && $techniciansTicket);
        });

        $otherTechniciansInProcessTickets = \array_filter($inProcessTickets, function($ticket) {
          $inProcess = ($ticket["status"] === "ATENDIMENTO");
          $otherTechniciansTicket = ($ticket["id_tecnico_responsavel"] !== $_SESSION["user_id"]);
            return ($inProcess && $otherTechniciansTicket);
        });

        $this->view->token = \json_encode($token->data);
        $this->view->activeTicketRequests = $activeTicketRequests;
        $this->view->inQueueTickets = $inQueueTickets;
        $this->view->techniciansInProcessTickets = \array_values($techniciansInProcessTickets);
        $this->view->otherTechniciansInProcessTickets = \array_values($otherTechniciansInProcessTickets);
        $this->render('tecnicos');
    }else{
      $this->forbidenAccess();
    }
  }

  public function technician_select_request(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){
      // Check if the necessary data was sent
      if(isset($_POST['ticket_id']) && isset($_POST['deadline_value'])){
        // Check if the data was sent in the expected format
        if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $_POST['deadline_value']) === 1) {
          // Update the service request status and details
          $ticketID = $_POST['ticket_id'];
          $date = new \DateTime($_POST['deadline_value'], new \DateTimeZone("America/Recife"));
          $prazo = $date->format("Y-m-d H:i:s");
          $date = new \DateTime("now", new \DateTimeZone("America/Recife"));
          $date->setTimestamp(time());
          $data_assumido = $date->format("Y-m-d H:i:s");
          $Chamado = Container::getClass("Chamado");
          $Chamado->updateColumnById("id_tecnico_responsavel", $_SESSION['user_id'], $ticketID);
          $Chamado->updateColumnById("data_assumido", $data_assumido, $ticketID);
          $Chamado->updateColumnById("status", "ATENDIMENTO", $ticketID);
          $Chamado->updateColumnById("prazo", $prazo, $ticketID);
          $ticket = $Chamado->getTicketById($ticketID);
          if ($ticket) {
              header('Content-Type: application/json; charset=UTF-8');
              echo json_encode(array('event' => 'success', 'type' => 'acquired_ticket', 'ticket' => $ticket));
          }
        } else {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'deadline_wrong_format')));
        }
      } else {
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'missing_data')));
      }
    }else{
      $this->forbidenAccess();
    }
  }

  public function technician_history () {
      session_start();
      if($_SESSION['user_role'] === "TECNICO") {
          $Chamado = Container::getClass("Chamado");
          $closedTickets = $Chamado->getTechniciansClosedTickets($_SESSION['user_id']);
          $this->view->closedTickets = $closedTickets;
          $this->render('technician_request_history');
      } else {
          $this->forbidenAccess();
      }
  }

  public function solicitar_atendimento() {
      session_start();
      if($_SESSION['user_role'] == "TECNICO") {
        $this->render('solicitar_atendimento');
      } else {
        $this->forbidenAccess();
      }
  }

  public function register_call_request() {
      session_start();
      if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")) {
        // Check if the necessary data was sent
        $service_id  = $_POST['id_servico'];
        $place_id    = $_POST['id_local'];
        $client_id   = $_POST['id_usuario'];
        $deadline    = $_POST['prazo'];
        $description = $_POST['descricao'];
        if ($service_id !== null && $place_id !== null && $client_id !== null && $deadline !== null && $description !== null) {
          // Check if the deadline date was sent in the expected format
          if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $deadline) === 1)  {
            // Adjust the date to the DB format
            $date = new \DateTime($deadline, new \DateTimeZone("America/Recife"));
            $deadline_db = $date->format("Y-m-d H:i:s");
            // Store the request in the pending requests table
            // and immediately after alter the status on the pending table
            $Request = Container::getClass("SolicitarChamado");
            $open_request_id = $Request->save($client_id,$service_id,$place_id,$description);
            $Request->updateColumnById("status", "ATENDIDA", $open_request_id);
            // Save the service request as accepted by saving its data
            // in the accepted requests table
            $Chamado = Container::getClass("Chamado");
            $Chamado->save($service_id, $place_id, $open_request_id, $_SESSION['user_id'], $client_id, $deadline_db, $description);
          } else {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'deadline_wrong_format')));
          }
        } else {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'missing_data')));
        }
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
            if (
                isset($_POST['request_id']) &&
                (isset($_POST['refusal_reason']) && !preg_match('/^\s*$/', $_POST['refusal_reason']))
            ){
                $requestId = $_POST['request_id'];
                $date = new \DateTime("now", new \DateTimeZone('America/Fortaleza'));
                $date = $date->format("Y-m-d H:i:s");
                $SolicitarChamado = Container::getClass("SolicitarChamado");
                $SolicitarChamado->updateColumnById("status", "RECUSADA", $requestId);
                $SolicitarChamado->updateColumnById("data_recusado", $date, $requestId);
                $SolicitarChamado->updateColumnById("id_recusante", $_SESSION['user_id'], $requestId);
                $SolicitarChamado->updateColumnById("motivo_recusa", $_POST['refusal_reason'], $requestId);

                $ticketRequest = $SolicitarChamado->getTicketRequestById($requestId);
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array('event' => 'success', 'type' => 'ticket_request_refused', 'request' => $ticketRequest));
            } else {
                header('Content-Type: application/json; charset=UTF-8');
                header('HTTP/1.1 400');
                die(json_encode(array('event' => 'error', 'type' => 'missing_data')));
            }
        } else {
            $this->forbidenAccess();
        }
    }
}
?>
