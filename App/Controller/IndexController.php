<?php
namespace App\Controller;
use SON\Controller\Action;
use \App\Model\Chamado;
use \App\Model\SolicitarAcesso;
use \SON\Di\Container;

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
    $email = $_POST['emailCliente'];
    $login = $_POST['loginCliente'];
    $setor = $_POST['setorCliente'];
    $matricula = $_POST['matriculaCliente'];

    $acesso = Container::getClass("SolicitarAcesso");
    $acesso->save($nome,$email,$login,$setor,$matricula);
    echo 'dados:'.$nome.'-'.$email.'-'.$login.'-'.$setor.'-'.$matricula.'-'.'cadastrados!';
  }

  public function logar(){
    if(isset($_POST['login_accept'])){
      if(($_POST['username'] != '') && ($_POST['password'] != '')){
        $login = $_POST['username'];
        $pass = $_POST['password'];

        $acesso = Container::getClass("Usuario");
        $user = $acesso->findByLogin($login);
        if($user['password'] == $pass){
            echo 'bem vindo :'.$user['login'].' - '.$user['password'];

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
              header('Location: /gticchla/public/cliente');
            }else if($role['tecnico'] == 1){
              $_SESSION['user_role'] = "TECNICO";
              header('Location: /gticchla/public/tecnico');
            }else if($role['gerente'] == 1){
              $_SESSION['user_role'] = "GERENTE";
              header('Location: /gticchla/public/admin');
            }

            echo '</br> role: '.$_SESSION['user_role'];
        }
      }else{
        echo "<script>alert('digite login e senha!'); history.back();</script>";
      }


    }else{
      echo 'n conseguiu logar';
    }
  }

  public function get_request_info(){
    if (isset($_POST)) {
      if (isset($_POST['request_id'])) {
        $solicitacao_chamado = Container::getClass("SolicitarChamado");
        $requests = $solicitacao_chamado->getChamadosById($_POST['request_id'])[0];
        $arr = array(
          "id_solicitacao_field" => $requests["id_solicitacao"],
          "cliente_field" => $requests["id_cliente"], // Client name afterwards instead of id
          "servico_field" => $requests["id_servico"], // Service title afterwards instead of id
          "descricao_field" => $requests["descricao"],
          "solicitacao_chamado_status_field" => $requests["solicitacao_chamado_status"],
          "chamado_status_field" => $requests["chamado_status"],
          "data_solicitacao_field" => (isset($requests["data_solicitacao"])) ? date('d/m/Y',strtotime($request[0]["data_solicitacao"])) : NULL,
          "data_abertura_field" => (isset($requests["data_abertura"])) ? date('d/m/Y',strtotime($request[0]["data_abertura"])) : NULL,
          "data_finalizado_field" => (isset($requests["data_finalizado"])) ? date('d/m/Y',strtotime($request[0]["data_finalizado"])) : NULL,
          "prazo_field" => $requests["prazo"],
          "tecnico_abertura_field" => $requests["id_tecnico_abertura"], // Technician name afterwards instead of id
          "tecnico_responsavel_field" => $requests["id_tecnico_responsavel"], // Technician name afterwards instead of id
          "parecer_tecnico_field" => $requests["parecer_tecnico"]
        );
        echo json_encode($arr);
      }
    }
  }

  public function logout(){
    session_start();
    $_SESSION = array();
    session_destroy();
    header('Location: /gticchla/public/');
  }

}
?>
