<?php
namespace App\Controller;
use SON\Controller\Action;
use \SON\Di\Container;

class GerenteController extends Action{
  public function index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){

      $requisicao_acesso = Container::getClass("SolicitarAcesso");
      $requisicoes = $requisicao_acesso->fetchAll();
      $requisicoes_acesso_aguardando = [];
      foreach ($requisicoes as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_acesso_aguardando[] = $request;
        }
      }

      $chamado = Container::getClass("Chamado");
      $chamados = $chamado->fetchAll();
      $chamados_abertos = $chamado->getChamadosByStatus("AGUARDANDO");
      $chamados_atendimentos = $chamado->getChamadosByStatus("ATENDIMENTO");
      $chamados_finalizados = $chamado->getChamadosByStatus("FINALIZADO");
      $count = count($chamados);

      $servico = Container::getClass("Servico");
      $servicos = $servico->fetchAll();
      $array_servicos_names = [];
      foreach ($servicos as $service) {
        $array_servicos_names[$service['id']] = $service['nome'];
      }

      $requisicao_atendendimento = Container::getClass("SolicitarChamado");
      $requisicoes_atendimento = $requisicao_atendendimento->fetchAll();
      $requisicoes_atendimento_aguardando = [];
      foreach ($requisicoes_atendimento as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_atendimento_aguardando[] = $request;
        }
      }

      $cliente = Container::getClass("Usuario");
      $clientes = $cliente->fetchAll();
      $array_clients_names = [];
      foreach ($clientes as $client) {
        $array_clients_names[$client['id']]['nome'] = $client['nome'];
        $array_clients_names[$client['id']]['setor'] = $client['setor'];
      }

      //atribuindo para a view
      $this->view->clients_names = $array_clients_names;
      $this->view->service_names = $array_servicos_names;
      $this->view->requisicoes_atendimento = $requisicoes_atendimento_aguardando;
      $this->view->requisicoes = $requisicoes_acesso_aguardando;
      $this->view->chamados = $chamados;
      $this->view->chamados_abertos = $chamados_abertos;
      $this->view->chamados_atendimentos = $chamados_atendimentos;
      $this->view->chamados_finalizados = $chamados_finalizados;
      $this->view->count = $count - 1;
      $this->render('gerentes');
    }else{
      $this->forbidenAccess();
    }

  }

  public function cadastro_usuario_index(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      $requisicao_acesso = Container::getClass("SolicitarAcesso");
      $requisicoes = $requisicao_acesso->fetchAll();
      $requisicoes_aguardando = [];

      foreach ($requisicoes as $request) {
        if($request['status'] == "AGUARDANDO"){
          $requisicoes_aguardando[] = $request;
        }
      }

      $this->view->requisicoes = $requisicoes_aguardando;
      $this->render('cadastro_usuario_index');
    }else{
      $this->forbidenAccess();
    }


  }

  public function cadastrar_usuarios(){
    session_start();
    if($_SESSION['user_role'] == "GERENTE"){
      if(isset($_POST['f_accept']) && isset($_POST['selections'])){

        $clients = [];
        $technicians = [];
        $managers = [];

        if(isset($_POST['client_role'])){
          foreach ($_POST['client_role'] as $request) {
            $clients[$request] = 1;
          }
        }

        if(isset($_POST['technician_role'])){
          foreach ($_POST['technician_role'] as $request) {
            $technicians[$request] = 1;
          }
        }

        if(isset($_POST['manager_role'])){
          foreach ($_POST['manager_role'] as $request) {
            $managers[$request] = 1;
          }
        }

        foreach ($_POST['selections'] as $cadastro) {

          $requisicao_acessoDb = Container::getClass("SolicitarAcesso");
          $requisicao = $requisicao_acessoDb->findById($cadastro);

          $clienteDb = Container::getClass("Usuario");
          $clienteDb->save($requisicao['nome'],$requisicao['email'],$requisicao['login'],$requisicao['setor'],$requisicao['matricula']);

          $requisicao_acessoDb->updateColumnById("status","ATENDIDA",$requisicao['id']);

          $cliente_role = $clienteDb->findByLogin($requisicao['login']);
          $user_role =  Container::getClass("UsuarioRole");

          $client_role;
          $technician_role;
          $manager_role;

          if(isset($clients[$cadastro]) && $clients[$cadastro] != null){
            $client_role = $clients[$cadastro];
          }else{
            $client_role = 0;
          }

          if(isset($technicians[$cadastro]) && $technicians[$cadastro] != null){
            $technician_role = $technicians[$cadastro];
          }else{
            $technician_role = 0;
          }

          if(isset($managers[$cadastro]) && $managers[$cadastro] != null){
            $manager_role = $managers[$cadastro];
          }else{
            $manager_role = 0;
          }

          $user_role->save($cliente_role['id'],$client_role,$technician_role,$manager_role);
          echo "<script>alert('Dados cadastrados!');</script>";
          header('Location: /gticchla/public/admin/cadastro_usuarios');
        }

      }else{
        echo "<script>alert('Não existe requisição aguardando ou não foi selecionada alguma para atender!'); history.back();</script>";
      }
    }else{
      $this->forbidenAccess();
    }

  }

  public function finalize_request(){
    session_start();
    if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")){
      if((isset($_POST['technical_opinion']))&&(isset($_POST['request_id']))){
        $id = $_POST['request_id'];
        $parecer = $_POST['technical_opinion'];
        $status = "FINALIZADO";
        $today = getdate();
        $data_finalizado = ''.$today['year'].'-'.$today['mon'].'-'.$today['mday'];
        $chamadoDb = Container::getClass("Chamado");
        $chamadoDb->updateColumnById("status",$status,$id);
        $chamadoDb->updateColumnById("parecer_tecnico",$parecer,$id);
        $chamadoDb->updateColumnById("data_finalizado",$data_finalizado,$id);
      }
    }
  }
  public function open_call_request(){
    session_start();
    if(($_SESSION['user_role'] == "GERENTE")||($_SESSION['user_role'] == "TECNICO")){
      if(isset($_POST['admin_open_client_call_request']) && isset($_POST['selections_call_request'])){
        $today = getdate();
        $date = ''.$today['year'].'-'.$today['mon'].'-'.$today['mday'];

        foreach ($_POST['selections_call_request'] as $id_request) {
          $requisicao_acessoDb = Container::getClass("SolicitarChamado");
          $requisicao = $requisicao_acessoDb->findById($id_request);

          $chamadoDb = Container::getClass("Chamado");
          $chamadoDb->save($requisicao['id_servico'],$requisicao['id'],$date,$_SESSION['user_id'],$requisicao['id_cliente'],$requisicao['descricao']);
          $request = Container::getClass("SolicitarChamado");
          $request->updateColumnById("status","ATENDIDA",$id_request);
        }
        echo "<script>alert('Dados cadastrados!');</script>";
        header('Location: /gticchla/public/');

      }else{
        echo "<script>alert('Não existe requisição aguardando ou não foi selecionada alguma para atender!'); history.back();</script>";
      }
    }else{
      $this->forbidenAccess();
    }
  }

    public function refuse_support_request() {
        session_start();
        if ($_SESSION['user_role'] == "GERENTE") {
            if (isset($_POST['request_id']) && isset($_POST['refusal_reason'])) {
                $date = date("Y-m-d");
                $request = Container::getClass("SolicitarChamado");
                $request->updateColumnById("status", "RECUSADA", $_POST['request_id']);
                $request->updateColumnById("data_recusado", $date, $_POST['request_id']);
                $request->updateColumnById("id_recusante", $_SESSION['user_id'], $_POST['request_id']);
                $request->updateColumnById("motivo_recusa", $_POST['refusal_reason'], $_POST['request_id']);
            }
        } else {
            $this->forbidenAccess();
        }
    }
  // public function change_password(){
  //   session_start();
  //   if(($_SESSION['user_role'] === "GERENTE")||($_SESSION['user_role'] === "CLIENTE")||($_SESSION['user_role'] === "TECNICO")) {
  //     if(isset($_POST['current_password']) && isset($_POST['new_password'])){
  //       $pass = $_POST['current_password'];
  //       $userDb = Container::getClass("Usuario");
  //       $user = $userDb->findById($_SESSION['user_id']);
  //       if($pass == $user['password']){
  //         $userDb->updateColumnById("password",$_POST['new_password'],$_SESSION['user_id']);
  //       }
  //     }
  //   } else {
  //       $this->forbidenAccess();
  //   }
  // }
}
?>
