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

  public function logout(){
    session_start();
    $_SESSION = array();
    session_destroy();
    header('Location: /gticchla/public/');
  }

}
?>
