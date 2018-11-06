<?php
namespace App;
use SON\Init\Bootstrap;

#initialization class
class Init extends Bootstrap{
  #Root url = /gtic/public/
  protected function initRoutes(){
    //INDEX ROUTES-------------------------------------------------------------------------------------------------------------
    $ar['home'] = array('route' => '/gtic/public/' , 'controller' => 'indexController', 'action' => 'index');
    $ar['solicitar_acesso'] = array('route' => '/gtic/public/solicitar_acesso' , 'controller' => 'indexController', 'action' => 'solicitar_acesso');
    $ar['index_logar'] = array('route' => '/gtic/public/logar' , 'controller' => 'indexController', 'action' => 'logar');
    $ar['index_logout'] = array('route' => '/gtic/public/logout' , 'controller' => 'indexController', 'action' => 'logout');
    $ar['get_services_suggestions'] = array('route' => '/gtic/public/get_services_suggestions' , 'controller' => 'indexController', 'action' => 'get_services_suggestions');
    $ar['get_locales_suggestions'] = array('route' => '/gtic/public/get_locales_suggestions' , 'controller' => 'indexController', 'action' => 'get_locales_suggestions');
    $ar['get_users_suggestions'] = array('route' => '/gtic/public/get_users_suggestions' , 'controller' => 'indexController', 'action' => 'get_users_suggestions');
    //--------------------------------------------------------------------------------------------------------------------------

    //ADMIN ROUTES--------------------------------------------------------------------------------------------------------------
    $ar['admin_panel'] = array('route' => '/gtic/public/admin' , 'controller' => 'gerenteController', 'action' => 'index');
    $ar['admin_panel_cadastrar_usuario'] = array('route' => '/gtic/public/admin/cadastro_usuarios' , 'controller' => 'gerenteController', 'action' => 'cadastro_usuario_index');
    $ar['cadastrar_clientes'] = array('route' => '/gtic/public/admin/cadastrar_usuarios' , 'controller' => 'gerenteController', 'action' => 'cadastrar_usuarios');
    $ar['archive_account_request'] = array('route' => '/gtic/public/admin/archive_account_request' , 'controller' => 'gerenteController', 'action' => 'archive_account_request');
    $ar['admin_panel_open_client_request'] = array('route' => '/gtic/public/admin/open_call_request' , 'controller' => 'gerenteController', 'action' => 'open_call_request');
    $ar['admin_panel_finalize_request'] = array('route' => '/gtic/public/admin/finalize_request' , 'controller' => 'gerenteController', 'action' => 'finalize_request');
    $ar['admin_panel_account_settings'] = array('route' => '/gtic/public/admin/configuracoes' , 'controller' => 'gerenteController', 'action' => 'admin_account_settings');
    $ar['admin_panel_solicitar_atendimento_index'] = array('route' => '/gtic/public/admin/solicitar_atendimento' , 'controller' => 'gerenteController', 'action' => 'solicitar_atendimento');
    //---------------------------------------------------------------------------------------------------------------------------
    $ar['change_password'] = array('route' => '/gtic/public/change_password' , 'controller' => 'tecnicoController', 'action' => 'change_password');
    //CLIENT ROUTES--------------------------------------------------------------------------------------------------------------
    $ar['cliente_panel'] = array('route' => '/gtic/public/cliente' , 'controller' => 'clienteController', 'action' => 'index');
    $ar['cliente_panel_solicitar_atendimento_index'] = array('route' => '/gtic/public/cliente/solicitar_atendimento' , 'controller' => 'clienteController', 'action' => 'solicitar_atendimento');
    $ar['cliente_panel_solicitar_atendimento_submit'] = array('route' => '/gtic/public/cliente/client_register_call_request' , 'controller' => 'clienteController', 'action' => 'client_register_call_request');
    $ar['cliente_panel_cancelar_solicitacao_atendimento'] = array('route' => '/gtic/public/cliente/client_cancel_call_request' , 'controller' => 'clienteController', 'action' => 'client_cancel_call_request');
    $ar['cliente_panel_historico_chamados'] = array('route' => '/gtic/public/cliente/historico_chamados' , 'controller' => 'clienteController', 'action' => 'client_request_history');
    $ar['cliente_panel_account_settings'] = array('route' => '/gtic/public/cliente/configuracoes' , 'controller' => 'clienteController', 'action' => 'cliente_account_settings');
    //----------------------------------------------------------------------------------------------------------------------------

    //TECHNICIAN ROUTES-----------------------------------------------------------------------------------------------------------
    $ar['technician_panel'] = array('route' => '/gtic/public/tecnico' , 'controller' => 'tecnicoController', 'action' => 'index');
    $ar['technician_panel_select_request'] = array('route' => '/gtic/public/technician_select_request' , 'controller' => 'tecnicoController', 'action' => 'technician_select_request');
    $ar['technician_panel_history'] = array('route' => '/gtic/public/tecnico/historico_chamados' , 'controller' => 'tecnicoController', 'action' => 'technician_history');
    $ar['technician_panel_solicitar_atendimento_index'] = array('route' => '/gtic/public/tecnico/solicitar_atendimento' , 'controller' => 'tecnicoController', 'action' => 'solicitar_atendimento');
    $ar['technician_panel_solicitar_atendimento_submit'] = array('route' => '/gtic/public/tecnico/register_call_request' , 'controller' => 'tecnicoController', 'action' => 'register_call_request');
    $ar['technician_panel_account_settings'] = array('route' => '/gtic/public/tecnico/configuracoes' , 'controller' => 'tecnicoController', 'action' => 'technician_account_settings');
    $ar['technician_panel_refuse_support_request'] = array('route' => '/gtic/public/tecnico/refuse_support_request' , 'controller' => 'tecnicoController', 'action' => 'refuse_support_request');
    //----------------------------------------------------------------------------------------------------------------------------

    $this->setRoutes($ar);
  }
}
?>
