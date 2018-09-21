<?php
namespace App\Controller;
use SON\Controller\Action;
use \App\Model\Chamado;
use \App\Model\SolicitarAcesso;
use \SON\Di\Container;
use \App\Model\Email;
use \App\Model\PasswordUtil;
use \App\Model\Local;

class IndexController extends Action{

  public function index(){
    session_start();
    if(!isset($_SESSION['user_role'])){
        $this->render('index');
    }else{
      if($_SESSION['user_role'] == "CLIENTE"){
        header('Location: /gticchla/public/cliente');
      }else if($_SESSION['user_role'] == "TECNICO"){
        header('Location: /gticchla/public/tecnico');
      }else if($_SESSION['user_role'] == "GERENTE"){
        header('Location: /gticchla/public/admin');
      }
    }

  }

  public function solicitar_acesso(){
    $nome = $_POST['nomeCliente'];
    $sobrenome = $_POST['sobrenomeCliente'];
    $email = $_POST['emailCliente'];
    $login = $_POST['loginCliente'];
    $setor = $_POST['setorCliente'];
    $matricula = $_POST['matriculaCliente'];

    // Check if there's already a registered user with the
    // submitted login, email or registration number
    if($this->isLoginInUse($email) == false) {
      if ($this->isEmailInUse($email) == false) {
        if ($this->isRegistrationNumberInUse($matricula) == false) {

          // Validate email address
          if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
              $acesso = Container::getClass("SolicitarAcesso");
              $acesso->save($nome." ".$sobrenome,$email,$login,$setor,$matricula);

              // Sending email notification
              $sendEmail = new Email();
              $sendEmail->accessRequestNotification($nome,$sobrenome,$login,$email,$setor,$matricula);
          } else {
              header('Content-Type: application/json; charset=UTF-8');
              header('HTTP/1.1 400');
              echo json_encode(array('event' => 'error', 'type' => 'invalid_email'));
          }

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
  }

  public function logar(){
    if(($_POST['username'] != '') && ($_POST['password'] != '')){
      $login = $_POST['username'];
      $pass = $_POST['password'];

      $acesso = Container::getClass("Usuario");
      $user = $acesso->findByLogin($login);
      $isCorrectPw = PasswordUtil::verify($pass, $user['password_hash']);
      if($isCorrectPw){
        $user_role = Container::getClass("UsuarioRole");
        $role = $user_role->findByIdUser($user['id']);

        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['nome'];
        $_SESSION['user_login'] = $user['login'];
        $_SESSION['user_turno'] = $user['turno'];
        $_SESSION['user_setor'] = $user['setor'];
        $_SESSION['user_matricula'] = $user['matricula'];

        if($role['cliente'] == 1){
          $_SESSION['user_role'] = "CLIENTE";
        }else if($role['tecnico'] == 1){
          $_SESSION['user_role'] = "TECNICO";
        }else if($role['gerente'] == 1){
          $_SESSION['user_role'] = "GERENTE";
        }

        // Inform last time user logged-in and update DB with the new date
        ob_start();
        $last_login = $user['data_ultimo_login'];
        $date = new \DateTime("now", new \DateTimeZone('America/Fortaleza'));
        $date = $date->format("Y-m-d H:i:s");
        $acesso->updateColumnById("data_ultimo_login", $date, $user['id']);
        ob_end_clean();
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('lastLogin' => $last_login));
      }else{
        header('Content-Type: application/json; charset=UTF-8');
        header('HTTP/1.1 400');
        echo json_encode(array('event' => 'error', 'type' => 'invalid_credentials'));
      }
    }else{
      header('Content-Type: application/json; charset=UTF-8');
      header('HTTP/1.1 400');
      echo json_encode(array('event' => 'error', 'type' => 'missing_data'));
    }
  }

  public function get_request_info(){
    if (isset($_POST)) {
      if (isset($_POST['request_id'])) {
        // Get request data
        $solicitacao_chamado = Container::getClass("SolicitarChamado");
        $requests = $solicitacao_chamado->getChamadosById($_POST['request_id'])[0];

        // Get client and technician data
        $user = Container::getClass("Usuario");
        $request_client = $user->findById($requests["id_cliente"]);
        $request_admission_technician = $user->findById($requests["id_tecnico_abertura"]);
        $request_responsible_technician = $user->findById($requests["id_tecnico_responsavel"]);

        // Get service data
        $servico = Container::getClass("Servico");
        $request_service = $servico->findById($requests["id_servico"]);

        // Get local data
        $local = Container::getClass("Local");
        $request_local = $local->findById($requests["id_local"]);

        $arr = array(
          "id_solicitacao_field" => $requests["id_solicitacao"],
          "id_chamado_field" => $requests["id_chamado"],
          "cliente_field" => $request_client['nome'],
          "local_field" => $request_local["nome"],
          "servico_field" => $request_service["nome"],
          "descricao_field" => $requests["descricao"],
          "solicitacao_chamado_status_field" => $requests["solicitacao_chamado_status"],
          "chamado_status_field" => $requests["chamado_status"],
          "data_solicitacao_field" => (isset($requests["data_solicitacao"])) ? date('d/m/Y H:i:s',strtotime($requests["data_solicitacao"])) : NULL,
          "data_abertura_field" => (isset($requests["data_abertura"])) ? date('d/m/Y H:i:s',strtotime($requests["data_abertura"])) : NULL,
          "data_finalizado_field" => (isset($requests["data_finalizado"])) ? date('d/m/Y H:i:s',strtotime($requests["data_finalizado"])) : NULL,
          "prazo_field" => (isset($requests["prazo"])) ? date('d/m/Y H:i:s',strtotime($requests["prazo"])) : NULL,
          "tecnico_abertura_field" => $request_admission_technician['nome'],
          "tecnico_responsavel_field" => $request_responsible_technician['nome'],
          "parecer_tecnico_field" => $requests["parecer_tecnico"]
        );
        echo json_encode($arr);
    } elseif (isset($_POST['call_request_id'])) {
        // Get request data
        $solicitacao_chamado = Container::getClass("SolicitarChamado");
        $requests = $solicitacao_chamado->getSolicitacoesById($_POST['call_request_id'])[0];

        // Get client data
        $user = Container::getClass("Usuario");
        $request_client = $user->findById($requests["id_cliente"]);

        // Get service data
        $servico = Container::getClass("Servico");
        $request_service = $servico->findById($requests["id_servico"]);

        // Get local data
        $local = Container::getClass("Local");
        $request_local = $local->findById($requests["id_local"]);

        $arr = array(
          "id_solicitacao_field" => $requests["id_solicitacao"],
          "data_solicitacao_field" => (isset($requests["data_solicitacao"])) ? date('d/m/Y H:i:s',strtotime($requests["data_solicitacao"])) : NULL,
          "cliente_field" => $request_client["nome"],
          "local_field" => $request_local["nome"],
          "servico_field" => $request_service["nome"],
          "descricao_field" => $requests["descricao"],
          "solicitacao_chamado_status_field" => $requests["solicitacao_chamado_status"]
        );
        echo json_encode($arr);
    }
    }
  }

  public function get_services_suggestions(){
    $suggestions = [];

    // get the data
    $Servico = Container::getClass("Servico");
    $servicos_db = $Servico->fetchAll();

    foreach ($servicos_db as $servico_db) {
      $servico = [
        "value" => $servico_db["nome"],
        "data"  => []
      ];
      $servico["data"]["id_servico"] = $servico_db["id"];
      switch ($servico_db["tipo"]) {
        case "Instalação": $servico["data"]["category"] = "Instalação"; break;
        case "Problema"  : $servico["data"]["category"] = "Problema"; break;
        case "Reserva"   : $servico["data"]["category"] = "Reserva"; break;
        case "Sites"     : $servico["data"]["category"] = "Sites"; break;
        default          : $servico["data"]["category"] = "Outros"; break;
      }
      array_push($suggestions, $servico);
    }
    header("Content-type:application/json");
    echo(json_encode($suggestions));
  }

  public function get_locales_suggestions() {
    $suggestions = [];

    // Get the data
    $Local = Container::getClass("Local");
    $db_locales = $Local->getCurrentPlaces();

    // Arrange the data to be used by the component
    foreach ($db_locales as $db_locale) {
      $locale = [
        "value" => $db_locale["nome"],
        "data"  => []
      ];
      $locale["data"]["id_local"] = $db_locale["id"];
      $locale["data"]["category"] = $db_locale["tipo"];
      array_push($suggestions, $locale);
    }
    header("Content-type:application/json");
    echo(json_encode($suggestions));
  }

  public function logout(){
    session_start();
    $_SESSION = array();
    session_destroy();
    header('Location: /gticchla/public/');
  }

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
