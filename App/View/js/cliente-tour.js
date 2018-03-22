var tour = new Tour({
  orphan: true,
  template: "<div class='popover tour'>\
                <div class='arrow'></div>\
                <h3 class='popover-title'></h3>\
                <div class='popover-content'></div>\
                <div class='popover-navigation'>\
                  <button class='btn btn-default' data-role='prev'>« Anterior</button>\
                  <button class='btn btn-default' data-role='next'>Próximo »</button>\
                  <button class='btn btn-default' data-role='end'>Encerrar</button>\
                </div>\
              </div>",
  steps: [
    {
      title: 'Bem-vindo ao GTI Chamados!',
      content: 'Clicando no botão "Próximo" abaixo, você poderá seguir um breve<br>tutorial de apresentação de como utilizar o sistema.<br><br>Clique no botão "Encerrar" se deseja dispensar o tutorial.<br><br><small>Mas recomendamos que veja. É rapidinho!</small>',
      placement: 'auto',
      backdrop: true,
      onShow: function(e) { if (e._options.orphan) $('body').data("bs.popover", null); }
    },
    {
      title: 'Menu do usuário',
      content: 'Nesse menu você pode abrir a página de configurações ou sair da conta.',
      element: 'a.user-profile',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Menu lateral',
      content: 'Nessa barra lateral você pode navegar para<br>outras páginas do sistema, como a página onde<br>você solicita chamados, em "Solicitar atendimento".',
      element: '.main_container > .left_col',
      placement: 'right',
      backdrop: true
    },
    {
      title: 'Página inicial',
      content: 'Estamos nessa página agora.<br>Essa é a página inicial que sempre<br>abre logo depois de você entrar no sistema.',
      element: '#sidebar-menu ul li:nth-child(1)',
      placement: 'right',
      backdrop: true
    },
    {
      title: 'Página de solicitação de chamados',
      content: 'Aqui nesse link você acessa a<br>página para solicitar chamados.<br><br>Vamos dar uma olhada nela no próximo passo.',
      element: '#sidebar-menu ul li:nth-child(2)',
      placement: 'right',
      backdrop: true
    },
    {
      path: '/gticchla/public/cliente/solicitar_atendimento'
    },
    {
      title: 'Fazendo um chamado',
      content: 'É nesse formulário que você solicita<br>um chamado.<br>Você seleciona uma das opções de<br>motivo e descreve melhor a situação<br>no campo de texto seguinte.<br>Depois, é só clicar em "Solicitar".',
      element: '#solicitar-chamado-panel',
      backdrop: true
    },
    {
      title: 'Chamados pendentes',
      content: '<small>Quando você solicita um chamado no formulário ao<br>lado, ele não é imediatamente aberto.<br>As suas solicitações de chamado feitas no formulário<br>ao lado ficam nessa tabela de "Solicitações pendentes"<br>na espera de serem aceitas pelo suporte.<br><br>Após a solicitação ser aceita, o chamado é aberto e<br>você passa a acompanha-lo na tabela de "Chamados<br>abertos" na página inicial.<br><br>Vamos voltar para a página inicial para ver as tabelas<br>dos chamados depois de serem abertos...</small>',
      element: '#chamados-pendentes-panel',
      placement: 'left',
      backdrop: true
    },
    {
      path: '/gticchla/public/cliente'
    },
    {
      title: 'Seus chamados abertos',
      content: 'De volta à pagina inicial. Quando a sua solicitação<br>de chamado sai da tabela "Solicitações pendentes"<br>lá da página "Solicitar atendimento", ele vem para<br>essa tabela.<br><br>Ou seja, os chamados nessa tabela são solicitações<br>que foram aceitas pelo suporte e estão em espera<br>para serem atendidas.',
      element: '#chamados-abertos-panel',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Seus chamados em atendimento',
      content: 'Quando chegar a vez do seu chamado ser atendido,<br>ele sairá da tabela de "Chamados abertos" e ficará<br>nessa tabela de "Chamados em atendimeto".',
      element: '#chamados-em-atendimento-panel',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Detalhes dos chamados',
      content: 'Em qualquer tabela do sistema você pode clicar<br>numa linha para ver as informações completas<br>de um chamado!',
      element: 'form[name=chamados_atendimento_clientes] tbody tr:nth-child(1)',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'É isso!',
      content: 'Seja bem-vindo à versão de testes do nosso sistema de chamados.<br>O site não será como está agora para sempre, vamos continuar fazendo mudanças<br>para melhorar e acrescentar funcionalidades ao sistema.<br><br><img style="width:32px;" src="https://emojipedia-us.s3.amazonaws.com/thumbs/120/whatsapp/116/winking-face_1f609.png" />',
      backdrop: true,
      onShow: function(e) { if (e._options.orphan) $('body').data("bs.popover", null); }
    }
  ]
});
