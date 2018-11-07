<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;
use \SON\Db\DBConnector;
use \App\Model\Email;
use \App\Model\Token;

class GerenteController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      // Get the token for WebSocket
      $token = new Token($_SESSION['user_id']);

      $SolicitarAcesso = Container::getClass("SolicitarAcesso");
      $SolicitarChamado = Container::getClass("SolicitarChamado");
      $Chamado = Container::getClass("Chamado");

      $unreviewedAccountRequests = $SolicitarAcesso->getUnreviewedRequests();
      $activeTicketRequests = $SolicitarChamado->getActiveTicketRequests();
      $inQueueTickets = $Chamado->getInQueueTickets();
      $inProcessTickets = $Chamado->getInProcessTickets();

      $this->view->token = \json_encode($token->data);
      $this->view->unreviewedAccountRequests = $unreviewedAccountRequests;
      $this->view->activeTicketRequests = $activeTicketRequests;
      $this->view->inQueueTickets = $inQueueTickets;
      $this->view->inProcessTickets = $inProcessTickets;
      $this->render('gerentes');
    }else{
      $this->forbidenAccess();
    }

  }

  public function cadastro_usuario_index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      $requisicao_acesso = Container::getClass("SolicitarAcesso");
      $unreviewedAccountRequests = $requisicao_acesso->getUnreviewedRequests();
      $this->view->unreviewedAccountRequests = $unreviewedAccountRequests;
      $this->render('cadastro_usuario_index');
    }else{
      $this->forbidenAccess();
    }


  }

  public function admin_account_settings () {
      session_start();
      if($_SESSION['user_role'] === "GERENTE") {
          $this->render('admin_account_settings');
      } else {
          $this->forbidenAccess();
      }
  }

  public function cadastrar_usuarios(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      // Check if wat was submitted is a list of accounts selected, a new
      // user manually created or a request which was edited

      // If it's one or more directly accepted requests
      if (isset($_POST['requests'])) {
        $successfulIds = [];
        $invalidEmails = [];
        $failedToSendEmail = [];
        $alreadyInUseEmails = [];
        $alreadyInUseLogins = [];
        $alreadyInUseRegistrationNumbers = [];
        foreach ($_POST['requests'] as $request) {
          // Register only users with unique email, username and
          // registration number and with valid email
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

                  array_push($successfulIds, $request['idSolicitacao']);

                  // Send email
                  try {
                    $email = new Email();
                    if (!$email->requestGrantedNotification($request['nome'],$request['email'])) {
                      array_push($failedToSendEmail, $request['email']);
                    }
                  } catch (\Exception $e) {
                    array_push($failedToSendEmail, $request['email']);
                  }
                } else {
                  array_push($alreadyInUseRegistrationNumbers, $request['matricula']);
                }
              } else {
                array_push($alreadyInUseEmails, $request['email']);
              }
            } else {
              array_push($alreadyInUseLogins, $request['usuario']);
            }
          } else {
            array_push($invalidEmails, $request['email']);
          }
        }

        // If some accounts failed to be registered, notify about them
        if (count($invalidEmails)
        || count($failedToSendEmail)
        || count($alreadyInUseLogins)
        || count($alreadyInUseEmails)
        || count($alreadyInUseRegistrationNumbers)) {
            $failedData = array(
              'successful_ids' => $successfulIds,
              'invalid_emails'=> $invalidEmails,
              'failed_to_send_email'=> $failedToSendEmail,
              'used_logins' => $alreadyInUseLogins,
              'used_emails' => $alreadyInUseEmails,
              'used_registration_numbers' => $alreadyInUseRegistrationNumbers
            );
            header('Content-Type: application/json; charset=UTF-8');
            header('HTTP/1.1 400');
            die(json_encode(array('event' => 'error', 'type' => 'prevented_registrations', 'data' => $failedData)));
        }

        // Inform the request id that was accepted
        header('Content-Type: application/json; charset=UTF-8');
        die(json_encode(
            array(
                'event' => 'info',
                'type' => 'request_accepted',
                'data' => ['requestIds' => $successfulIds]
            )
        ));

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

                // store the modifications
                $requisicao_acessoDb->updateColumnById("nome", $request['nome'], $request['idSolicitacao']);
                $requisicao_acessoDb->updateColumnById("email", $request['email'], $request['idSolicitacao']);
                $requisicao_acessoDb->updateColumnById("login", $request['usuario'], $request['idSolicitacao']);
                $requisicao_acessoDb->updateColumnById("setor", $request['setor'], $request['idSolicitacao']);
                $requisicao_acessoDb->updateColumnById("matricula", $request['matricula'], $request['idSolicitacao']);

                // role
                $cliente_role = $clienteDb->findByLogin($request['usuario']);
                $user_role =  Container::getClass("UsuarioRole");
                $user_role->save($cliente_role['id'],$request['isClient'],$request['isTechnician'],$request['isAdmin']);

                // Send email
                $emailSent = true;
                try {
                  $email = new Email();
                  if (!$email->requestGrantedNotification($request['nome'],$request['email'])) {
                    $emailSent = false;
                  }
                } catch (\Exception $e) {
                  $emailSent = false;
                }

                // Inform the request id that was accepted
                header('Content-Type: application/json; charset=UTF-8');
                die(json_encode(
                    array(
                        'event' => 'info',
                        'type' => 'request_accepted',
                        'data' => [
                          'requestId' => $request['idSolicitacao'],
                          'emailSent' => $emailSent
                        ]
                    )
                ));
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
                $emailSent = true;
                try {
                  $email = new Email();
                  if (!$email->requestGrantedNotification($request['nome'],$request['email'])) {
                    $emailSent = false;
                  }
                } catch (\Exception $e) {
                  $emailSent = false;
                }

                // Inform if email was not sent
                if ($emailSent === false) {
                  header('Content-Type: application/json; charset=UTF-8');
                  die(json_encode(
                      array(
                          'event' => 'info',
                          'type' => 'email_notification_failed'
                      )
                  ));
                }
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
        if ($_POST['send_email'] == "true" && (!isset($_POST['email_message']) || !preg_match('/^\s*$/', $_POST['email_message']))) {
          header('Content-Type: application/json; charset=UTF-8');
          header('HTTP/1.1 400');
          die(json_encode(array('event' => 'error', 'type' => 'insuficient_email_data')));
        } else {
          $failedToSendEmail = [];
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
               try {
                 $email = new Email();
                 if (!$email->requestRefusedNotification($request['nome'],$request['email'], $_POST['email_message'])) {
                   array_push($failedToSendEmail, $request['email']);
                 }
               } catch (\Exception $e) {
                 array_push($failedToSendEmail, $request['email']);
               }
            }
          }
        }

        $responseData = [];
        if (count($failedToSendEmail)) {
          $responseData["failedToSendEmail"] = $failedToSendEmail;
        }
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('event' => 'info', 'type' => 'response', 'data' => $responseData));
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
      if(
          isset($_POST['request_id']) &&
          isset($_POST['technical_opinion']) &&
          !preg_match('/^\s*$/', $_POST['technical_opinion'])
      ){
        $ticketID = $_POST['request_id'];
        $parecer = $_POST['technical_opinion'];
        $status = "FINALIZADO";
        $date = new \DateTime("now", new \DateTimeZone("America/Recife"));
        $date->setTimestamp(time());
        $data_finalizado = $date->format("Y-m-d H:i:s");
        $Chamado = Container::getClass("Chamado");
        $Chamado->updateColumnById("status", $status, $ticketID);
        $Chamado->updateColumnById("parecer_tecnico", $parecer, $ticketID);
        $Chamado->updateColumnById("data_finalizado", $data_finalizado, $ticketID);

        // Return the ticket with updated data
        $ticket = $Chamado->getTicketById($ticketID);
        if ($ticket) {
          header('Content-Type: application/json; charset=UTF-8');
          echo json_encode(array('event' => 'success', 'type' => 'finalized_ticket', 'ticket' => $ticket));
        }
      } else {
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        die(json_encode(array('event' => 'error', 'type' => 'missing_data')));
      }
    }
  }

  public function open_call_request(){
    session_start();
    if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")){
      // Check if the necessary data was sent
      if(isset($_POST['call_request_id']) && isset($_POST['deadline_value'])){
        // Check if the data was sent in the expected format
        if(preg_match("/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}/", $_POST['deadline_value']) === 1) {
          try {
            $db = DBConnector::getInstance();
            try {
              $db->beginTransaction();
              // Update the service request status and get its info
              // to store in the accepted requests table
              $requisicao_acessoDb = Container::getClass("SolicitarChamado");
              $requisicao_acessoDb->updateColumnById("status","ATENDIDA",$_POST['call_request_id']);
              $requisicao = $requisicao_acessoDb->findById($_POST['call_request_id']);

              // Save the service request as accepted by saving its data
              // in the accepted requests table
              $date = new \DateTime($_POST['deadline_value'], new \DateTimeZone("America/Recife"));
              $prazo = $date->format("Y-m-d H:i:s");
              $chamadoDb = Container::getClass("Chamado");
              $ticketId = $chamadoDb->save($requisicao['id_servico'],$requisicao['id_local'],$requisicao['id'],$_SESSION['user_id'],$requisicao['id_cliente'],$prazo,$requisicao['descricao']);

              if ($ticketId !== false) {
                // Return the new ticket data
                $ticket = $chamadoDb->getTicketById($ticketId);
                $db->commit();
                header('Content-Type: application/json; charset=UTF-8');
                echo json_encode(array('event' => 'success', 'type' => 'new_ticket', 'ticket' => $ticket));
              } else {
                $db->rollback();
                header('Content-Type: application/json; charset=UTF-8');
                header('HTTP/1.1 500');
                die(json_encode(array('event' => 'error', 'type' => 'db_op_failed')));
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

  public function solicitar_atendimento() {
      session_start();
      if($_SESSION['user_role'] == "GERENTE") {
        // Get the token for WebSocket
        $token = new Token($_SESSION['user_id']);
        $this->view->token = \json_encode($token->data);
        $this->render('solicitar_atendimento');
      } else {
        $this->forbidenAccess();
      }
  }

  private function isLoginInUse($login) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByLogin($login);
      return ($usuario === false) ? false : true;
  }

  private function isEmailInUse($email) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByEmail($email);
      return ($usuario === false) ? false : true;
  }

  private function isRegistrationNumberInUse($matricula) {
      $usuarios = Container::getClass("Usuario");
      $usuario = $usuarios->findByRegistrationNumber($matricula);
      return ($usuario === false) ? false : true;
  }
}
?>
