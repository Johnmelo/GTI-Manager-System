<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \App\Model\PasswordUtil;
use \App\Model\Token;
use \SON\Db\DBConnector;

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
        $inProcessTickets = $Chamado->getInProcessTickets(true);

        $techniciansInProcessTickets = [];
        $otherTechniciansInProcessTickets = [];

        \array_walk($inProcessTickets, function($ticket)use(&$techniciansInProcessTickets, &$otherTechniciansInProcessTickets) {
          $ticketTechniciansIDs = array_column($ticket['responsaveis'], 'id_tecnico');
          $technicianDataIndex = array_search($_SESSION['user_id'], $ticketTechniciansIDs);
          if ($technicianDataIndex !== false) {
            $status = $ticket['responsaveis'][$technicianDataIndex]['status'];
            if ($status === "1") {
              \array_push($techniciansInProcessTickets, $ticket);
            } else {
              \array_push($otherTechniciansInProcessTickets, $ticket);
            }
          } else {
            \array_push($otherTechniciansInProcessTickets, $ticket);
          }
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
      if(isset($_POST['ticket_id']) && isset($_POST['deadline_value']) && isset($_POST['technicians_list']) && count($_POST['technicians_list']) > 0){
        // Check if the data was sent in the expected format
        if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $_POST['deadline_value']) === 1) {
          try {
            $db = DBConnector::getInstance();
            try {
              // Update the service request status and details
              $ticketID = $_POST['ticket_id'];
              $date = new \DateTime($_POST['deadline_value'], new \DateTimeZone("America/Recife"));
              $prazo = $date->format("Y-m-d H:i:s");
              $date = new \DateTime("now", new \DateTimeZone("America/Recife"));
              $date->setTimestamp(time());
              $data_assumido = $date->format("Y-m-d H:i:s");
              $Chamado = Container::getClass("Chamado");
              $db->beginTransaction();
              $Chamado->updateColumnById("data_assumido", $data_assumido, $ticketID);
              $Chamado->updateColumnById("status", "ATENDIMENTO", $ticketID);
              $Chamado->updateColumnById("prazo", $prazo, $ticketID);
              // Associate the ticket with technicians
              $Chamado = Container::getClass("Chamado");
              \array_walk($_POST['technicians_list'], function($techData)use($Chamado, $ticketID) {
                $activity = \preg_match('/^\s*$/', $techData['technicianActivity']) ? null : $techData['technicianActivity'];
                if ($techData['technicianID'] === $_SESSION['user_id']) {
                  $Chamado->setTicketTechnicians($ticketID, $_SESSION['user_id'], $activity, 1);
                } else {
                  $Chamado->setTicketTechnicians($ticketID, $techData['technicianID'], $activity);
                }
              });
              $db->commit();
              $ticket = $Chamado->getTicketById($ticketID);
              if ($ticket) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array('event' => 'success', 'type' => 'acquired_ticket', 'ticket' => $ticket));
              }
            } catch (\Exception $e) {
              $db->rollback();
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 500');
              die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
            }
          } catch (\Exception $e) {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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
        // Get the token for WebSocket
        $token = new Token($_SESSION['user_id']);
        $this->view->token = \json_encode($token->data);
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
        if ($service_id !== null && $place_id !== null && $client_id !== null && $deadline !== null && $description !== null && !preg_match('/^\s*$/', $description)) {
          // Check if the deadline date was sent in the expected format
          if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $deadline) === 1) {
            try {
              $db = DBConnector::getInstance();
              try {
                // Adjust the date to the DB format
                $date = new \DateTime($deadline, new \DateTimeZone("America/Recife"));
                $deadline_db = $date->format("Y-m-d H:i:s");
                // Store the request in the pending requests table
                // and immediately after alter the status on the pending table
                $Request = Container::getClass("SolicitarChamado");
                $db->beginTransaction();
                $open_request_id = $Request->save($client_id,$service_id,$place_id,$description);
                $Request->updateColumnById("status", "ATENDIDA", $open_request_id);
                // Save the service request as accepted by saving its data
                // in the accepted requests table
                $Chamado = Container::getClass("Chamado");
                $ticketID = $Chamado->save($service_id, $place_id, $open_request_id, $_SESSION['user_id'], $client_id, $deadline_db, $description);
                if ($ticketID !== false) {
                  $db->commit();
                  // Return the ticket data
                  $ticket = $Chamado->getTicketById($ticketID);
                  header('Content-Type: application/json; charset=UTF-8');
                  echo json_encode(array('event' => 'success', 'type' => 'new_ticket', 'ticket' => $ticket));
                } else {
                  throw new \Exception();
                }
              } catch (\Exception $e) {
                $db->rollback();
                header('Content-Type: application/json; charset=UTF-8');
                header('HTTP/1.1 500');
                die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
              }
            } catch (\Exception $e) {
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 400');
              die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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
        try {
          $pass = $_POST['current_password'];
          $userDb = Container::getClass("Usuario");
          $user = $userDb->findById($_SESSION['user_id']);
          $isCorrectPw = PasswordUtil::verify($pass, $user['password_hash']);
          if ($isCorrectPw) {
            // Generate the hash of the new password ...
            $new_hash = PasswordUtil::hash($_POST['new_password']);
            // ... and store it
            try {
              $updatedPassw = $userDb->updateColumnById("password_hash", $new_hash, $_SESSION['user_id']);
              header('Content-Type: application/json; charset=UTF-8');
              if ($updatedPassw) {
                echo json_encode(array('event' => 'success', 'type' => 'password_reset', 'message' => 'Senha alterada com sucesso'));
              } else {
                throw new \Exception();
              }
            } catch (\Exception $e) {
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 500');
              die(json_encode(array('event' => 'error', 'type' => 'db_op_failed', 'message' => "A senha não foi alterada")));
            }
          } else {
            header("HTTP/1.1 401 Unauthorized");
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'error', 'type' => 'wrong_current_password', 'message' => 'Senha atual incorreta'));
          }
        } catch (\Exception $e) {
          header("HTTP/1.1 500 Internal Server Error");
          echo json_encode(array('event' => 'error'));
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
          try {
            $db = DBConnector::getInstance();
            try {
              $requestId = $_POST['request_id'];
              $date = new \DateTime("now", new \DateTimeZone('America/Fortaleza'));
              $date = $date->format("Y-m-d H:i:s");
              $SolicitarChamado = Container::getClass("SolicitarChamado");
              $db->beginTransaction();
              $SolicitarChamado->updateColumnById("status", "RECUSADA", $requestId);
              $SolicitarChamado->updateColumnById("data_recusado", $date, $requestId);
              $SolicitarChamado->updateColumnById("id_recusante", $_SESSION['user_id'], $requestId);
              $SolicitarChamado->updateColumnById("motivo_recusa", $_POST['refusal_reason'], $requestId);
              $db->commit();
              $ticketRequest = $SolicitarChamado->getTicketRequestById($requestId);
              header('Content-Type: application/json; charset=UTF-8');
              echo json_encode(array('event' => 'success', 'type' => 'ticket_request_refused', 'request' => $ticketRequest));
            } catch (\Exception $e) {
              $db->rollback();
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 500');
              die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
            }
          } catch (\Exception $e) {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function update_ticket_responsible_technicians() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticket_id']) && isset($_POST['technicians_list'])) {
        // Check if each technician ID appears only one time
        $techniciansIDs = array_column($_POST['technicians_list'], 'technicianID');
        if (count($techniciansIDs) === count(array_flip($techniciansIDs))) {
          try {
            $db = DBConnector::getInstance();
            try {
              $ticketID = $_POST['ticket_id'];
              $newRespTechniciansData = $_POST['technicians_list'];
              $Chamado = Container::getClass("Chamado");
              $db->beginTransaction();
              // Get previous data
              $ticketData = $Chamado->getTicketById($ticketID);
              // For each previously defined technician and his responsibility ...
              \array_walk($ticketData['responsaveis'], function($dbRespData)use($newRespTechniciansData, $Chamado, $ticketID) {
                $sentDataIndex = \array_search($dbRespData['id_tecnico'], array_column($newRespTechniciansData, 'technicianID'));
                if ($sentDataIndex && $sentDataIndex !== false) {
                  if ($dbRespData['status'] === "0" || $dbRespData['id_tecnico'] === $_SESSION['user_id'] || $_SESSION['user_role'] === "GERENTE") {
                    // ... Update the definitions if allowed
                    $responsibility = $newRespTechniciansData[$sentDataIndex]['technicianActivity'];
                    $responsibility = \preg_replace('/^\s*|\s*$/', '', $responsibility);
                    $responsibility = (\preg_match('/^\s*$/', $responsibility)) ? null : $responsibility;
                    if ($responsibility !== $dbRespData['atividade']  || $dbRespData['status'] == "0") {
                      // Update the responsibility in the DB if it was changed
                      $status = $dbRespData['id_tecnico'] === $_SESSION['user_id'] ? 1 : $dbRespData['status'];
                      $Chamado->setTicketTechnicians($ticketID, $dbRespData['id_tecnico'], $responsibility, $status);
                    }
                  }
                } else {
                  if ($dbRespData['status'] === "0" || $dbRespData['id_tecnico'] === $_SESSION['user_id'] || $_SESSION['user_role'] === "GERENTE") {
                    // ... Remove the definitions which isn't present in the submitted list
                    $Chamado->deleteTicketTechnicianResponsibility($ticketID, $dbRespData['id_tecnico']);
                  }
                }
              });

              if ($newRespTechniciansData === '') {
                // If all technicians were removed, mark the ticket back as "in queue"
                $Chamado->updateColumnById("status", "AGUARDANDO", $ticketID);
              } else {
                // Get the newly added technicians
                $newTechRespData = \array_filter($newRespTechniciansData, function($respData)use($ticketData) {
                    // Get the responsibilities data that isn't in the database
                    if ($ticketData['responsaveis'] === null) {
                        return true;
                    } else {
                        return (\array_search($respData['technicianID'], \array_column($ticketData['responsaveis'], 'id_tecnico')) === false);
                    }
                });
                // Store them in the database
                \array_walk($newTechRespData, function($newRespData)use($Chamado, $ticketID) {
                    $responsibility = $newRespData['technicianActivity'];
                    $responsibility = \preg_replace('/^\s*|\s*$/', '', $responsibility);
                    $responsibility = (\preg_match('/^\s*$/', $responsibility)) ? null : $responsibility;
                    $status = $newRespData['technicianID'] === $_SESSION['user_id'] || $_SESSION['user_role'] === "GERENTE" ? 1 : 0;
                    $Chamado->setTicketTechnicians($ticketID, $newRespData['technicianID'], $responsibility, $status);
                });
              }

              $db->commit();
              $ticket = $Chamado->getTicketById($ticketID);
              if ($ticket) {
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array('event' => 'success', 'type' => 'ticket_responsible_technicians_updated', 'ticket' => $ticket));
              }
            } catch (\Exception $e) {
              $db->rollback();
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 500');
              die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
            }
          } catch (\Exception $e) {
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
          }
        } else {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'not_unique_tech_ids')));
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

  public function respond_ticket_sharing_invitation() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID']) && isset($_POST['response']) && isset($_POST['responsibility'])) {
        try {
          $db = DBConnector::getInstance();
          try {
            $Chamado = Container::getClass("Chamado");
            $status = ($_POST['response'] === "accepted") ? 1 : -1;
            $responseType = 'ticket_sharing_invitation_';
            $responseType .= ($status === 1) ? 'accepted' : 'declined';
            $db->beginTransaction();
            $Chamado->setTicketTechnicians($_POST['ticketID'], $_SESSION['user_id'], $_POST['responsibility'], $status);
            $db->commit();
            $ticket = $Chamado->getTicketById($_POST['ticketID']);
            if ($ticket) {
              header('Content-Type: application/json; charset=UTF-8');
              echo json_encode(array('event' => 'success', 'type' => $responseType, 'ticket' => $ticket));
            }
          } catch (\Exception $e) {
            $db->rollback();
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 500');
            die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function responsibility_done() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID'])) {
        try {
          $Chamado = Container::getClass("Chamado");
          $Chamado->setResponsibilityDone($_POST['ticketID'], $_SESSION['user_id']);
          $ticket = $Chamado->getTicketById($_POST['ticketID']);
          if ($ticket) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'responsibility_done', 'ticket' => $ticket));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function save_responsibility_change() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID']) && isset($_POST['technicianID']) && isset($_POST['responsibility'])) {
        try {
          $Chamado = Container::getClass("Chamado");
          $Chamado->saveResponsibilityChange($_POST['ticketID'], $_POST['technicianID'], $_POST['responsibility']);
          $ticket = $Chamado->getTicketById($_POST['ticketID']);
          if ($ticket) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'responsibility_change_saved', 'ticket' => $ticket));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function reaquire_ticket() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID']) && isset($_POST['responsibility'])) {
        try {
          $Chamado = Container::getClass("Chamado");
          $Chamado->setTicketTechnicians($_POST['ticketID'], $_SESSION['user_id'], $_POST['responsibility'], '1');
          $ticket = $Chamado->getTicketById($_POST['ticketID']);
          if ($ticket) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'ticket_reaquired', 'ticket' => $ticket));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function discard_invite() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID']) && isset($_POST['technicianID'])) {
        try {
          $Chamado = Container::getClass("Chamado");
          $Chamado->deleteTicketTechnicianResponsibility($_POST['ticketID'], $_POST['technicianID']);
          $ticket = $Chamado->getTicketById($_POST['ticketID']);
          if ($ticket) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'ticket_sharing_invitation_discarded', 'ticket' => $ticket));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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

  public function invite_technician() {
    session_start();
    if ($_SESSION['user_role'] == "GERENTE" || $_SESSION['user_role'] == "TECNICO") {
      if (isset($_POST['ticketID']) && isset($_POST['technicianID']) && isset($_POST['responsibility'])) {
        try {
          $Chamado = Container::getClass("Chamado");
          $Chamado->setTicketTechnicians($_POST['ticketID'], $_POST['technicianID'], $_POST['responsibility'], '0');
          $ticket = $Chamado->getTicketById($_POST['ticketID']);
          if ($ticket) {
            header('Content-Type: application/json; charset=UTF-8');
            echo json_encode(array('event' => 'success', 'type' => 'technician_invited', 'ticket' => $ticket));
          }
        } catch (\Exception $e) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'db_conn_failed')));
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
}
?>
