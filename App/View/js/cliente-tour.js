var tour = new Tour({
  orphan: true,
  onNext: detectStep,
  onEnd: detectTourEnding,
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
      path: '/gtic/public/cliente/solicitar_atendimento'
    },
    {
      title: 'Fazendo um chamado',
      content: 'É nesse formulário que você solicita<br>um chamado.<br>Você seleciona uma das opções de<br>motivo, o local para o qual o serviço se<br>destina e descreve a situação/problema<br>no campo de texto "Descrição".<br>Depois, é só clicar em "Solicitar".<br><br>Atenção: no campo de "Motivo" e "Local",<br>é necessário selecionar uma opção da<br>lista que aparece quando se clica nos<br>campos. Você pode digitar algo, mas<br>apenas para pesquisar uma opção. O<br>formulário não irá aceitar qualquer texto<br>digitado, apenas os items selecionados.',
      element: '#nova-solicitacao-panel',
      backdrop: true
    },
    {
      title: 'Chamados pendentes',
      content: '<small>Quando você solicita um chamado no formulário<br>anterior, o chamado não é imediatamente aberto.<br>As suas solicitações de chamado feitas no formulário<br>anterior ficam nessa tabela aguardando aprovação, <br>na espera de serem aceitas pelo suporte.<br><br>Após a solicitação ser aceita, o chamado é aberto e<br>você passa a acompanha-lo na tabela da "Fila de<br>Atendimento" na página inicial.<br><br>Vamos voltar para a página inicial para ver as tabelas<br>dos chamados depois de serem abertos...</small>',
      element: '#solicitacoes-nao-abertas-panel',
      placement: 'left',
      backdrop: true
    },
    {
      path: '/gtic/public/cliente'
    },
    {
      title: 'Seus chamados abertos',
      content: 'De volta à pagina inicial. Quando a sua solicitação<br>de chamado sai da tabela de "Aguardando aprovação"<br>lá da página "Solicitar atendimento", ele vem para essa tabela.<br><br>Ou seja, os chamados nessa tabela são solicitações<br>que foram aceitas pelo suporte e estão em espera<br>para serem atendidas.',
      element: '#chamados-abertos-panel',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Seus chamados em atendimento',
      content: 'Quando chegar a vez do seu chamado ser atendido,<br>ele sairá da tabela de "Fila de atendimento" ao lado e ficará<br>nessa tabela de "Chamados em atendimento".',
      element: '#chamados-em-atendimento-panel',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Detalhes dos chamados',
      content: 'Em qualquer tabela do sistema você pode<br>clicar numa linha para ver as informações<br>completas de um chamado!',
      element: 'form[name=chamados_abertos_clientes] tbody tr:nth-child(1)',
      placement: 'bottom',
      backdrop: true
    },
    {
      title: 'Histórico dos seus chamados',
      content: 'Por fim, aqui nesse link você acessa a<br>página com o histórico de todos os seus<br>chamados que já foram concluídos.',
      element: '#sidebar-menu ul li:nth-child(3)',
      placement: 'right',
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


function detectStep (tour) {
  if (tour._current == 0) {
    $('.dropdown-menu.dropdown-usermenu a').css("pointer-events", "none");
  }
  if (tour._current == 1 || tour._current == 5) {
    $('.left_col').css("pointer-events", "none");
  }
  if (tour._current == 5) {
    $('form[action=client_register_call_request] button').css("pointer-events", "none");
  }
  if (tour._current == 6) {
    $('form[name=solicitacoes_chamado] button').css("pointer-events", "none");
  }
  if (tour._current == 8) {
    $('form[name=chamados_abertos_clientes] button').css("pointer-events", "none");
  }
  if (tour._current == 10) {
    // Insert temp request row for illustration
    addExampleRow();
  }
  if (tour._current == 11) {
    // Remove the temp row afterwards
    removeExampleRow();
  }
}

function detectTourEnding (tour) {
  // Undo measures to prevent exiting the tour if tour is skipped prematurely
  $('.dropdown-menu.dropdown-usermenu a').css("pointer-events", "auto");
  $('.left_col').css("pointer-events", "auto");
  $('form[action=client_register_call_request] button').css("pointer-events", "auto");
  $('form[name=solicitacoes_chamado] button').css("pointer-events", "auto");
  $('form[name=chamados_abertos_clientes] button').css("pointer-events", "block");
  if (tour._current == 11)
    removeExampleRow();
}

function addExampleRow () {
  var table = document.getElementById("not-processing-requests").getElementsByTagName('tbody')[0];
  var temp_row = table.insertRow(0);

  id_cell = temp_row.insertCell(0);
  servico_cell = temp_row.insertCell(1);
  status_cell = temp_row.insertCell(2);
  tec_abert_cell = temp_row.insertCell(3);
  acao_cell = temp_row.insertCell(4);

  id_cell.innerText = "1";
  servico_cell.innerText = "Exemplo de chamado";
  status_cell.innerText = "AGUARDANDO";
  tec_abert_cell.innerText = "João";
  acao_cell.innerHTML = '<div class="icheckbox_square-blue" style="position: relative;"><input type="checkbox" value="8" name="selections[]" style="position: absolute; opacity: 0;"><ins class="iCheck-helper" style="position: absolute; top: 0%; left: 0%; display: block; width: 100%; height: 100%; margin: 0px; padding: 0px; background: rgb(255, 255, 255); border: 0px; opacity: 0;"></ins></div>';
}

function removeExampleRow () {
  var table = document.getElementById("not-processing-requests").getElementsByTagName('tbody')[0];
  table.deleteRow(0);
}
