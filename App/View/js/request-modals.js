$('button[name=btnJoin]').on('click', function(e) {
    if (e.target.nodeName == "BUTTON") {
        var request_id = $(e.target).parents('tr')[0].children[0].innerHTML;
        var modal_form_config = {
            id_chamado_field: true,
            cliente_field: true,
            servico_field: true,
            descricao_field: true,
            data_solicitacao_field: true,
            data_abertura_field: true,
            prazo_field: false
        }
        var modal_footer_config = [
            {
                btnContent: "Assumir chamado",
                class: "btn btn-primary",
                callback: acquireRequest.bind(null, $(e.target).parents('tr'))
            }
        ];
        $('.request-modal').find('.modal-header > h4')[0].innerHTML = "Assumir chamado";
        fillUpRequestModal({"request_id": request_id});
        showRequestModal(modal_form_config, modal_footer_config);
    }
});

$('button[name=btnFinalizarChamado]').on('click', function(e) {
    if (e.target.nodeName == "BUTTON") {
        var request_id = $(e.target).parents('tr')[0].children[0].innerHTML;
        var modal_form_config = {
            id_chamado_field: true,
            servico_field: true,
            data_abertura_field: true,
            prazo_field: true,
            descricao_field: true,
            parecer_tecnico_field: false
        };
        var modal_footer_config = [
            {
                btnContent:"Finalizar",
                class: "btn btn-primary",
                callback: finalizeRequest.bind(null, $(e.target).parents('tr'))
            }
        ];
        $('.request-modal').find('.modal-header > h4')[0].innerHTML = "Finalizar chamado";
        fillUpRequestModal({"request_id": request_id});
        showRequestModal(modal_form_config, modal_footer_config);
    }
});

function defineAndShowModal(typeRequest, element, formConfig, footerConfig) {
    if (element.target.nodeName == "TR" || element.target.nodeName == "TD") {
        var request_id = element.currentTarget.children[0].innerHTML;
        var table = $(element.currentTarget).parents('.table');
        $('.request-modal').find('.modal-header > h4')[0].innerHTML = "Detalhes do chamado";

        if (typeRequest == "call-request-type") {
            fillUpRequestModal({"call_request_id": request_id});
        } else if (typeRequest == "open-request-type") {
            fillUpRequestModal({"request_id": request_id});
        }
        showRequestModal(modal_form_config);
    }
}

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

function fillUpRequestModal (id) {

    $.post("/gticchla/public/get_request_info", id)
    .done(function(data) {
        var request = JSON.parse(data);
        for (field of fieldList) {
            $('.request-modal-form')[0].elements[field].value = request[field];
        }
    })
    .fail(function() {
        alert("Não foi possível realizar a ação");
    });
}

function finalizeRequest(tableRow) {
    var request_id = $('.request-modal-form')[0].elements["id_chamado_field"].value;
    var parecer_tecnico = $('.request-modal-form')[0].elements["parecer_tecnico_field"].value;

    $.post("/gticchla/public/admin/finalize_request",
    {
        "request_id": request_id,
        "technical_opinion": parecer_tecnico
    })
    .done(function() {
        tableRow.remove();
        $('.request-modal').modal('toggle');
    })
    .fail(function() {
        alert("Não foi possível realizar a ação");
    });
}

function acquireRequest(tableRow) {
    var request_id = tableRow.find('button[name="btnJoin"]').val();
    var data_prazo = $('.request-modal-form')[0].elements["prazo_field"].value;
    var data_abertura = $('.request-modal-form')[0].elements["data_abertura_field"].value;
    var prazo_dias = moment(data_prazo, 'DD/MM/YYYY').diff(moment(data_abertura, 'DD/MM/YYYY'), 'days');

    $.post("/gticchla/public/technician_select_request",
    {
        "btnJoin": request_id,
        "prazo": prazo_dias
    })
    .done(function() {
        tableRow.remove();
        $('.request-modal').modal('toggle');
        setTimeout(function() {
            document.location.reload(true);
        }, 500);
    })
    .fail(function() {
        alert("Não foi possível realizar a ação");
    });
}

// Inserting HTML structure into modal tag
$(document).ready(function() {
    $('.modal.request-modal').get(0).innerHTML = '\
    <div class="modal-dialog" role="document">\
      <div class="modal-content">\
        <div class="modal-header">\
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>\
          <h4 class="modal-title"></h4>\
        </div>\
        <div class="modal-body">\
          <form class="form-horizontal request-modal-form">\
            <div class="form-group" style="display: none;">\
              <label for="id_solicitacao_field" class="col-sm-4 control-label">Solicitação</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="id_solicitacao_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="id_chamado_field" class="col-sm-4 control-label">Chamado</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="id_chamado_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="cliente_field" class="col-sm-4 control-label">Cliente</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="cliente_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="solicitacao_chamado_status_field" class="col-sm-4 control-label">Status</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="solicitacao_chamado_status_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="chamado_status_field" class="col-sm-4 control-label">Status</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="chamado_status_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="servico_field" class="col-sm-4 control-label">Serviço</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="servico_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="data_solicitacao_field" class="col-sm-4 control-label">Data da solicitação</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="data_solicitacao_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="data_abertura_field" class="col-sm-4 control-label">Data de abertura</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="data_abertura_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="data_finalizado_field" class="col-sm-4 control-label">Data finalizado</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="data_finalizado_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="prazo_field" class="col-sm-4 control-label">Prazo</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="prazo_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="tecnico_responsavel_field" class="col-sm-4 control-label">Técnico responsável</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="tecnico_responsavel_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="tecnico_abertura_field" class="col-sm-4 control-label">Técnico da abertura</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="tecnico_abertura_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="descricao_field" class="col-sm-4 control-label">Descrição</label>\
              <div class="col-sm-8">\
                <textarea class="form-control" rows="3" id="descricao_field" readonly></textarea>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="parecer_tecnico_field" class="col-sm-4 control-label">Parecer técnico</label>\
              <div class="col-sm-8">\
                <textarea class="form-control" rows="3" id="parecer_tecnico_field" readonly></textarea>\
              </div>\
            </div>\
          </form>\
        </div>\
        <div class="modal-footer" style="display: none;"></div>\
      </div>\
    </div>';
});
