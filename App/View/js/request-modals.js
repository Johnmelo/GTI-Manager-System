$('.table.table-hover > tbody > tr').on('click', function(e) {
    if (e.target.nodeName == "TR" || e.target.nodeName == "TD") {
        var request_id = e.currentTarget.children[0].innerHTML;
        var table = $(e.currentTarget).parents('.table');
        $('.request-modal').find('.modal-header > h4')[0].innerHTML = "Detalhes do chamado";

        if (table.hasClass('open-request-list')) {
            var modal_form_config = {
                id_solicitacao_field: true,
                chamado_status_field: true,
                servico_field: true,
                data_abertura_field: true,
                prazo_field: true,
                tecnico_responsavel_field: true,
                tecnico_abertura_field: true,
                descricao_field: true,
            };
            fillUpRequestModal(request_id);
            showRequestModal(modal_form_config);
        } else if (table.hasClass('call-request-list')) {
            var modal_form_config = {
                id_solicitacao_field: true,
                solicitacao_chamado_status_field: true,
                servico_field: true,
                data_solicitacao_field: true,
                descricao_field: true
            };
            fillUpRequestModal(request_id);
            showRequestModal(modal_form_config);
        }

    }
});

function showRequestModal(formConfig, footerConfig) {
    // form config structure: { "fieldToBeVisible": "readOnyBool", ... }
    // footer config structure: [ { "btnContent": "content", "callback": "functionName", "class": "classes" }, ... ]

    // Reset modal config
    $('.request-modal-form .form-group').css("display", "none");
    $('.request-modal-form input, .request-modal-form textarea').val("");
    $('.request-modal').find('.modal-footer').css("display", "none");
    $('.request-modal').find('.modal-footer button').remove();

    // Set new config

    // Define the form inputs to be visible and its readonly setting
    for (var key in formConfig) {
        $('.request-modal-form')[0].elements[key].readOnly= formConfig[key];
        $($('.request-modal-form')[0].elements[key]).parents('.form-group').css("display", "block");
    }
    // If footer is defined
    if (footerConfig) {
        // create each button
        for (btn in footerConfig) {
            var button = document.createElement("button");
            button.setAttribute("type", "button");
            // button text
            button.innerHTML = footerConfig[btn].btnContent;
            // if class is defined
            button.setAttribute("class", (footerConfig[btn].class) ? footerConfig[btn].class : "btn btn-default");
            // if a callback was passed to be executed when pressed the button
            if (footerConfig[btn].callback) { $(button).on('click', footerConfig[btn].callback); }
            // insert button in the modal footer
            $('.request-modal').find('.modal-footer').append(button);
        }
        $('.request-modal').find('.modal-footer').css("display", "block");
    }

    // Show modal
    $('.request-modal').modal('toggle');
}

function fillUpRequestModal (request_id) {

    $.post("/gticchla/public/get_request_info", {"request_id": request_id})
    .done(function(data) {
        var request = JSON.parse(data);
        for (key in request) {
            $('.request-modal-form')[0].elements[key].value = request[key];
        }
    })
    .fail(function() {
        alert("Não foi possível realizar a ação");
    });
}
