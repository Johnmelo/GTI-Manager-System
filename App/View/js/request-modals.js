$('.table.table-hover > tbody > tr').on('click', function(e) {

    var request_id = e.currentTarget.children[0].innerHTML;
    var table = $(e.currentTarget).parents('.table');

    if (table.hasClass('open-request-list')) {
        fillOpenRequestModal(request_id);
    } else if (table.hasClass('call-request-list')) {
        fillRequestCallModal(request_id);
    }
});

function fillOpenRequestModal (request_id) {

    $.post("/gticchla/public/get_request_info",
        {"request_id":request_id},
        function(data, status) {
            var request = JSON.parse(data);
            $('#detalhes-chamado-modal-form')[0].elements["id_solicitacao_field"].value = request.id_solicitacao ? request.id_solicitacao : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["status_field"].value = request.chamado_status ? request.chamado_status : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["servico_field"].value = request.id_servico ? request.id_servico : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["data_abertura_field"].value = request.data_abertura ? request.data_abertura : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["data_finalizado_field"].value = request.data_finalizado ? request.data_finalizado : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["prazo_field"].value = request.prazo ? request.prazo : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["tecnico_responsavel_field"].value = request.id_tecnico_responsavel ? request.id_tecnico_responsavel : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["tecnico_abertura_field"].value = request.id_tecnico_abertura ? request.id_tecnico_abertura : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["descricao_field"].value = request.descricao ? request.descricao : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["parecer_tecnico_field"].value = request.parecer_tecnico ? request.parecer_tecnico : "Indefinido";
            $('#detalhes_chamado').modal('toggle');
        });
}

function fillRequestCallModal (request_id) {

    $.post("/gticchla/public/get_request_info",
        {"request_id":request_id},
        function(data, status) {
            var request = JSON.parse(data);
            $('#detalhes-chamado-modal-form')[0].elements["id_solicitacao_field"].value = request.id_solicitacao ? request.id_solicitacao : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["status_field"].value = request.solicitacao_chamado_status ? request.solicitacao_chamado_status : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["servico_field"].value = request.id_servico ? request.id_servico : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["data_solicitacao_field"].value = request.data_solicitacao ? request.data_solicitacao : "Indefinido";
            $('#detalhes-chamado-modal-form')[0].elements["descricao_field"].value = request.descricao ? request.descricao : "Indefinido";
            $('#detalhes_chamado').modal('toggle');
        });
}
