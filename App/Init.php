<?php
namespace App;
use SON\Init\Bootstrap;

#initialization class
class Init extends Bootstrap{
  #Root url = /gticchla/public/
  protected function initRoutes(){
    //INDEX ROUTES-------------------------------------------------------------------------------------------------------------
    $ar['home'] = array('route' => '/gticchla/public/' , 'controller' => 'indexController', 'action' => 'index');
    $ar['solicitar_acesso'] = array('route' => '/gticchla/public/solicitar_acesso' , 'controller' => 'indexController', 'action' => 'solicitar_acesso');
    $ar['index_logar'] = array('route' => '/gticchla/public/logar' , 'controller' => 'indexController', 'action' => 'logar');
    $ar['index_logout'] = array('route' => '/gticchla/public/logout' , 'controller' => 'indexController', 'action' => 'logout');
    $ar['get_request_info'] = array('route' => '/gticchla/public/get_request_info', 'controller' => 'indexController', 'action' => 'get_request_info');
    //--------------------------------------------------------------------------------------------------------------------------

    //ADMIN ROUTES--------------------------------------------------------------------------------------------------------------
    $ar['admin_panel'] = array('route' => '/gticchla/public/admin' , 'controller' => 'gerenteController', 'action' => 'index');
    $ar['admin_panel_cadastrar_usuario'] = array('route' => '/gticchla/public/admin/cadastro_usuarios' , 'controller' => 'gerenteController', 'action' => 'cadastro_usuario_index');
    $ar['cadastrar_clientes'] = array('route' => '/gticchla/public/admin/cadastrar_clientes' , 'controller' => 'gerenteController', 'action' => 'cadastrar_clientes');
    $ar['admin_panel_open_client_request'] = array('route' => '/gticchla/public/admin/open_call_request' , 'controller' => 'gerenteController', 'action' => 'open_call_request');
    //---------------------------------------------------------------------------------------------------------------------------

    //CLIENT ROUTES--------------------------------------------------------------------------------------------------------------
    $ar['cliente_panel'] = array('route' => '/gticchla/public/cliente' , 'controller' => 'clienteController', 'action' => 'index');
    $ar['cliente_panel_solicitar_atendimento_index'] = array('route' => '/gticchla/public/cliente/solicitar_atendimento' , 'controller' => 'clienteController', 'action' => 'solicitar_atendimento');
    $ar['cliente_panel_solicitar_atendimento_submit'] = array('route' => '/gticchla/public/cliente/client_register_call_request' , 'controller' => 'clienteController', 'action' => 'client_register_call_request');
    //----------------------------------------------------------------------------------------------------------------------------

    //TECHNICIAN ROUTES-----------------------------------------------------------------------------------------------------------
    $ar['technician_panel'] = array('route' => '/gticchla/public/tecnico' , 'controller' => 'tecnicoController', 'action' => 'index');
    $ar['technician_panel_select_request'] = array('route' => '/gticchla/public/technician_select_request' , 'controller' => 'tecnicoController', 'action' => 'technician_select_request');
    //----------------------------------------------------------------------------------------------------------------------------

    $this->setRoutes($ar);
  }

  public static function getDb(){
    //o interessante aqui é envolver com try/catch remember about exceptions consequences
    $db = new \PDO("mysql:host=localhost;dbname=gtichamados","root","");
    return $db;
  }
}
?>
