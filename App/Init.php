<?php
namespace App;
use SON\Init\Bootstrap;

#initialization class
class Init extends Bootstrap{
  #Root url = /gtic/public/
  protected function initRoutes(){
    //INDEX ROUTES-------------------------------------------------------------------------------------------------------------
    $routes['/gtic/public/'] = array('controller' => 'indexController', 'action' => 'index');
    $routes['/gtic/public/solicitar_acesso'] = array('controller' => 'indexController', 'action' => 'solicitar_acesso');
    $routes['/gtic/public/change_password'] = array('controller' => 'tecnicoController', 'action' => 'change_password');
    $routes['/gtic/public/logar'] = array('controller' => 'indexController', 'action' => 'logar');
    $routes['/gtic/public/logout'] = array('controller' => 'indexController', 'action' => 'logout');
    $routes['/gtic/public/get_services_suggestions'] = array('controller' => 'indexController', 'action' => 'get_services_suggestions');
    $routes['/gtic/public/get_locales_suggestions'] = array('controller' => 'indexController', 'action' => 'get_locales_suggestions');
    $routes['/gtic/public/get_users_suggestions'] = array('controller' => 'indexController', 'action' => 'get_users_suggestions');
    $routes['/gtic/public/get_support_users_suggestions'] = array('controller' => 'indexController', 'action' => 'get_support_users_suggestions');
    //--------------------------------------------------------------------------------------------------------------------------

    //ADMIN ROUTES--------------------------------------------------------------------------------------------------------------
    $routes['/gtic/public/admin'] = array('controller' => 'gerenteController', 'action' => 'index');
    $routes['/gtic/public/admin/cadastro_usuarios'] = array('controller' => 'gerenteController', 'action' => 'cadastro_usuario_index');
    $routes['/gtic/public/admin/cadastrar_usuarios'] = array('controller' => 'gerenteController', 'action' => 'cadastrar_usuarios');
    $routes['/gtic/public/admin/archive_account_request'] = array('controller' => 'gerenteController', 'action' => 'archive_account_request');
    $routes['/gtic/public/admin/open_call_request'] = array('controller' => 'gerenteController', 'action' => 'open_call_request');
    $routes['/gtic/public/admin/finalize_request'] = array('controller' => 'gerenteController', 'action' => 'finalize_request');
    $routes['/gtic/public/admin/configuracoes'] = array('controller' => 'gerenteController', 'action' => 'admin_account_settings');
    $routes['/gtic/public/admin/solicitar_atendimento'] = array('controller' => 'gerenteController', 'action' => 'solicitar_atendimento');
    //---------------------------------------------------------------------------------------------------------------------------

    //CLIENT ROUTES--------------------------------------------------------------------------------------------------------------
    $routes['/gtic/public/cliente'] = array('controller' => 'clienteController', 'action' => 'index');
    $routes['/gtic/public/cliente/solicitar_atendimento'] = array('controller' => 'clienteController', 'action' => 'solicitar_atendimento');
    $routes['/gtic/public/cliente/client_register_call_request'] = array('controller' => 'clienteController', 'action' => 'client_register_call_request');
    $routes['/gtic/public/cliente/client_cancel_call_request'] = array('controller' => 'clienteController', 'action' => 'client_cancel_call_request');
    $routes['/gtic/public/cliente/historico_chamados'] = array('controller' => 'clienteController', 'action' => 'client_request_history');
    $routes['/gtic/public/cliente/configuracoes'] = array('controller' => 'clienteController', 'action' => 'cliente_account_settings');
    //----------------------------------------------------------------------------------------------------------------------------

    //TECHNICIAN ROUTES-----------------------------------------------------------------------------------------------------------
    $routes['/gtic/public/tecnico'] = array('controller' => 'tecnicoController', 'action' => 'index');
    $routes['/gtic/public/technician_select_request'] = array('controller' => 'tecnicoController', 'action' => 'technician_select_request');
    $routes['/gtic/public/tecnico/historico_chamados'] = array('controller' => 'tecnicoController', 'action' => 'technician_history');
    $routes['/gtic/public/tecnico/solicitar_atendimento'] = array('controller' => 'tecnicoController', 'action' => 'solicitar_atendimento');
    $routes['/gtic/public/tecnico/register_call_request'] = array('controller' => 'tecnicoController', 'action' => 'register_call_request');
    $routes['/gtic/public/tecnico/configuracoes'] = array('controller' => 'tecnicoController', 'action' => 'technician_account_settings');
    $routes['/gtic/public/tecnico/refuse_support_request'] = array('controller' => 'tecnicoController', 'action' => 'refuse_support_request');
    //----------------------------------------------------------------------------------------------------------------------------

    $this->setRoutes($routes);
  }
}
?>
