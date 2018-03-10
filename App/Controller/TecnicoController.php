<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;

class TecnicoController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){

      //LOADING AND PREPARE INFORMATIOS ABOUT CALL REQUEST
      $chamado = Container::getClass("Chamado");
      $chamados = $chamado->fetchAll();
      $chamados_abertos = $chamado->getChamadosByStatus("AGUARDANDO");
      $chamados_atendimentos = $chamado->getChamadosByStatus("ATENDIMENTO");
      $chamados_finalizados = $chamado->getChamadosByStatus("FINALIZADO");
      $myRequests = [];
      $myRequestsFinished = [];
      foreach ($chamados_atendimentos as $request) {
        if($request['id_tecnico_responsavel'] == $_SESSION['user_id']){
          $myRequests[] = $request;
        }
      }
      foreach ($chamados_finalizados as $request) {
        if($request['id_tecnico_responsavel'] == $_SESSION['user_id']){
          $myRequestsFinished[] = $request;
        }
      }

      $requisicao_atendendimento = Container::getClass("SolicitarChamado");
      $requisicoes_atendimento = $requisicao_atendendimento->fetchAll();
      $requisicoes_atendimento_aguardando = [];
      foreach ($requisicoes_atendimento as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_atendimento_aguardando[] = $request;
        }
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT USERS TO IDENTIFY
      //OPEN REQUEST TECHNICIAN AND CLIENT THAT HAVE REQUESTED
      $user = Container::getClass("Usuario");
      $users = $user->fetchAll();
      $array_users_names = [];
      foreach ($users as $client) {
        $array_users_names[$client['id']]['nome'] = $client['nome'];
        $array_users_names[$client['id']]['setor'] = $client['setor'];
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $array_servicos_names = [];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }
      //-------------------------------------------------------

      //SENDING VALUES TO TECHNICIAN VIEW PAGE
      $this->view->myRequests = $myRequests;
      $this->view->myRequestsFinished = $myRequestsFinished;
      $this->view->openRequests = $chamados_abertos;
      $this->view->requisicoes_atendimento = $requisicoes_atendimento_aguardando;
      $this->view->users_names = $array_users_names;
      $this->view->service_names = $array_servicos_names;
      //------------------------------------------------------------------------

      //RENDERING PAGE
      $this->render('tecnicos');
      //------------------------------------------------------------------------

    }else{
      $this->forbidenAccess();
    }
  }

  public function technician_select_request(){
    session_start();
    if($_SESSION['user_role'] === "TECNICO"){

      //LOADING AND PREPARE INFORMATIONS ABOUT USERS TO IDENTIFY
      //OPEN REQUEST TECHNICIAN AND CLIENT THAT HAVE REQUESTED
      $user = Container::getClass("Usuario");
      $users = $user->fetchAll();
      $users_names = [];
      foreach ($users as $client) {
        $users_names[$client['id']]['nome'] = $client['nome'];
        $users_names[$client['id']]['setor'] = $client['setor'];
      }
      //------------------------------------------------------------------------

      //LOADING AND PREPARE INFORMATIONS ABOUT SERVICES
      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $servicos_names = [];
      foreach ($servicos as $service) {
        $servicos_names[$service['id']] = $service['nome'];
      }
      //-------------------------------------------------------

      if(isset($_POST['btnDetail'])){

        //GET INFORMATIONS ABOUT REQUEST
        $requestDb = Container::getClass("Chamado");
        $request = $requestDb->findById($_POST['btnDetail']);
        //------------------------------------------------------------------------

        $this->view->request = $request;
        $this->view->users = $users_names;
        $this->view->services = $servicos_names;

        $this->render('technician_view_open_request');
      }elseif(isset($_POST['btnJoin'])){

        $requestDb = Container::getClass("Chamado");
        $requestDb->updateColumnById("id_tecnico_responsavel",$_SESSION['user_id'],$_POST['btnJoin']);
        $requestDb->updateColumnById("status","ATENDIMENTO",$_POST['btnJoin']);
        header('Location: /gticchla/public/tecnico');
      }
    }else{
      $this->forbidenAccess();
    }

  }
}
?>
