# Funcionalidades do cliente

As funcionalidades a seguir estão disponíveis para usuários que tem como função cliente.

## Solicitar chamado

Na página "Solicitar atendimento" o cliente pode solicitar um chamado a partir do formulário **"Nova Solicitação"**.

Para abrir um chamado, é obrigatório informar o serviço desejado, o local para o qual o serviço se destina e algum detalhe como motivo do chamado ou qual o problema a ser resolvido.

![Tela de abrir chamado](./_imgs/cliente-solicitar-chamado.png)

Para inserir o **serviço**, é necessário selecionar uma das opções exibidas quando a campo recebe foco.

![Campo de serviço](./_imgs/seleção-serviço.png)

Nesse tipo de campo, o usuário pode digitar para pesquisar pelas opções.

![Pesquisando por serviço](./_imgs/pesquisa-serviço.png)

!> Esse tipo de campo **não aceita qualquer texto inserido**. É necessário selecionar uma das opções disponíveis para que a informação seja válida e o formulário apto a ser submetido.

O campo para selecionar o **local** segue a mesma abordagem.

![Pesquisando por local](./_imgs/pesquisa-local.png)

Por fim, o usuário precisa inserir algumas **informações sobre a solicitação ou seu motivo**. Essa informação pode ser a descrição de um problema, um pedido de reserva de equipamento, etc.

Apenas após inserir todas as informações, o botão para submeter a solicitação fica disponível.

![Formulário de solicitação de chamado preenchido](./_imgs/cliente-solicitar-chamado-preenchido.png)

## Visualizar solicitações de chamado

Após submeter uma solicitação de chamado, a solicitação fica na espera para ser **aprovado ou recusado pelo suporte**. A lista de solicitações de chamado fica disponível na mesma página em que se faz solicitações, na tabela **"Aguardando aprovação"**.

![Tabela de solicitações de chamado](./_imgs/cliente-solicitações-chamado.png)

Clicando na linha de uma solicitação na tabela, é exibida uma janela com todas as informações da solicitação.

![Detalhes de uma solicitação de chamado](./_imgs/detalhes-solicitação.png)

## Cancelar uma solicitação de chamado

Caso o usuário assim desejar, é possível cancelar a solicitação clicando no botão **"Cancelar"**. É exibida uma janela pedindo para confirmar o cancelamento. Se confirmado, a solicitação é removida da tabela e não estará mais visível pelo suporte.

![Confirmação do cancelamento de uma solicitação](./_imgs/cliente-confirmar-cancelar-solicitação.png)

## Visualizar chamados pendentes

Quando uma solicitação de chamado é aceita pelo suporte, deixa de ser solicitação e passar a ser um chamado pendente. Logo, a solicitação sai da tabela "Aguardando aprovação" da página "Solicitar atendimento" e o chamado resultande dessa solicitação aparece na tabela **"Na fila para atendimento"** na página inicial do cliente.

![Tabela de chamados na fila para atendimento](./_imgs/cliente-tabela-chamados-pendentes.png)

Clicando na linha do chamado na tabela, é possível visualizar as informações completas do chamado.

![Janela com todas as informações de um chamado](./_imgs/detalhes-chamado.png)

## Cancelar um chamado

Enquanto um chamado ainda está na fila para ser atendido, ainda é possível de ser cancelado pelo cliente. Da mesma forma que acontece com uma solicitação, para cancelar um chamado basta clicar no botão "Cancelar" na linha correspondente e confirmar o cancelamento na janela de confirmação exibida.

## Visualizar um chamado em atendimento

Quando um chamado na fila para atendimento é assumido por técnicos do suporte, o chamado é retirado da tabela "Na fila para atendimento" e passa para a tabela "Chamados em atendimento".

![Tabela de chamados em atendimento](./_imgs/cliente-tabela-chamado-em-andamento.png)

Ao clicar na linha de um chamado da tabela, é possível verificar todas as informações do chamado, incluindo o(s) técnico(s) responsável(is) e quais as atribuições de cada técnico (caso tenha sido informado).

![Janela com todas as informações de um chamado em atendimento](./_imgs/detalhes-chamado-em-andamento.png)

## Verificar histórico de chamados

Depois que um chamado é fechado ou cancelado, ele fica disponível na página de histórico

![Página de histórico de chamados](./_imgs/cliente-histórico.png)

É possível visualizar as informações completas dos chamados no histórico ao clicar na linha correspondente a ele na tabela.

![Janela com todas as informações de um chamado fechado](./_imgs/detalhes-chamado-fechado.png)
