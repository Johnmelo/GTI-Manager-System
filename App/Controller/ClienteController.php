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

      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }

      foreach ($requisicoes as $request) {
        if($request['id_cliente'] == $_SESSION['user_id']){
          $this->view->requisicoes[] = $request;
        }
      }

      $servicos = $servico->fetchAll();

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
    $today = getdate();
    $date = ''.$today['year'].'-'.$today['mon'].'-'.$today['mday'];

    $requisicao = Container::getClass("SolicitarChamado");
    $requisicao->save($id_usuario,$id_servico,$descricao,$date);
    header('Location: ../cliente');

  }

  public function client_request_history() {
      session_start();
      if($_SESSION['user_role'] === "CLIENTE") {
          $this->render('cliente_historico');
      } else {
          $this->forbidenAccess();
      }
  }


}
?>
