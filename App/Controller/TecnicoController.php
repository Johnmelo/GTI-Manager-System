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
      $this->view->users_names = $array_users_names;
      $this->view->service_names = $array_servicos_names;
      //------------------------------------------------------------------------

      //REDERING PAGE
      $this->render('tecnicos');
      //------------------------------------------------------------------------

    }else{
      $this->forbidenAccess();
    }
  }
}
?>
