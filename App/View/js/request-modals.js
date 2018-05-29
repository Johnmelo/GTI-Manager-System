$('button[name=btnJoin]').on('click', function(e) {
    if (e.target.nodeName == "BUTTON") {
        var request_id = getRowId(e);
        var modal_config = {
            title: "Assumir chamado",
            visible_fields: {
                id_chamado_field: true,
                cliente_field: true,
                servico_field: true,
                descricao_field: true,
                data_solicitacao_field: true,
                data_abertura_field: true,
                prazo_field: false
            },
            footer_config: [
                {
                    btnContent: "Assumir chamado",
                    class: "btn btn-primary",
                    callback: acquireRequest.bind(null, $(e.target).parents('tr'))
                }
            ]
        };
        defineSimpleModal(modal_config, "open-request-type", request_id);
    }
});

$('button[name=btnFinalizarChamado]').on('click', function(e) {
    if (e.target.nodeName == "BUTTON") {
        var request_id = getRowId(e);
        var modal_config = {
            title: "Finalizar chamado",
            visible_fields: {
                id_chamado_field: true,
                servico_field: true,
                data_abertura_field: true,
                prazo_field: true,
                descricao_field: true,
                parecer_tecnico_field: false
            },
            footer_config: [
                {
                    btnContent:"Finalizar",
                    class: "btn btn-primary",
                    callback: finalizeRequest.bind(null, $(e.target).parents('tr'))
                }
            ]
        };
        defineSimpleModal(modal_config, "open-request-type", request_id);
    }
});

function getRowId(e) {
    if (e.target.nodeName == "TR") {
        return $(e.target).get(0).dataset.requestId;
    } else {
        return $(e.target).parents('tr').get(0).dataset.requestId;
    }
}

function defineModal(modalConfig) {
    // modal config structure: {
    //   title: <string>,
    //   visible_fields: {
    //     "fieldToBeVisible": "readOnyBool",
    //     ...
    //   },
    //   footer_config: [
    //     {
    //       btnContent:"text",
    //       class:"classes",
    //       callback: callback
    //     }
    //   ]
    // }

    // Reset modal config
    $('.request-modal-form .form-group').css("display", "none");
    $('.request-modal-form input, .request-modal-form textarea').val("");
    $('.request-modal').find('.modal-footer').css("display", "none");
    $('.request-modal').find('.modal-footer button').remove();

    // Set config
    $('.request-modal').find('.modal-header > h4')[0].innerHTML = modalConfig.title;

    // Define the form inputs to be visible and its readonly setting
    var visibleFields = modalConfig.visible_fields;
    for (var key in visibleFields) {
        $('.request-modal-form')[0].elements[key].readOnly= visibleFields[key];
        $($('.request-modal-form')[0].elements[key]).parents('.form-group').css("display", "block");
    }

    // If footer is defined
    if (modalConfig.footer_config && modalConfig.footer_config.length > 0) {
        var footerBtns = modalConfig.footer_config;
        // create each button
        for (btn in footerBtns) {
            var button = document.createElement("button");
            button.setAttribute("type", "button");
            // button text
            button.innerHTML = footerBtns[btn].btnContent;
            // if class is defined
            button.setAttribute("class", (footerBtns[btn].class) ? footerBtns[btn].class : "btn btn-default");
            // if a callback was passed to be executed when pressed the button
            if (footerBtns[btn].callback) { $(button).on('click', footerBtns[btn].callback); }
            // insert button in the modal footer
            $('.request-modal').find('.modal-footer').append(button);
        }
        $('.request-modal').find('.modal-footer').css("display", "block");
    }
}

function defineSimpleModal(modalConfig, typeRequest, requestId) {
    var visibleFields = [];
    for (key in modalConfig.visible_fields) {
        visibleFields.push(key)
    }
    defineModal(modalConfig);
    fillUpRequestModal(typeRequest, requestId, visibleFields);
    showRequestModal();
}

function showRequestModal() {
    // Show modal
    $('.request-modal').modal('toggle');
}

function fillUpRequestModal(typeRequest, requestId, fieldList) {

    var id;
    if (typeRequest == "call-request-type") {
        id = {"call_request_id": requestId};
    } else if (typeRequest == "open-request-type") {
        id = {"request_id": requestId};
    }

    $.post("/gticchla/public/get_request_info", id)
    .done(function(data) {
        var request = JSON.parse(data);
        for (field of fieldList) {
            if (field === "data_abertura_field" || field === "data_solicitacao_field" || field === "data_finalizado_field" || field === "prazo_field") {
                var date = moment(request[field], 'DD/MM/YYYY HH:mm:ss').format("DD/MM/YYYY");
                var time = moment(request[field], 'DD/MM/YYYY HH:mm:ss').format("HH:mm");
                $('.request-modal-form')[0].elements[field].value = date + " às " + time;
            } else {
                $('.request-modal-form')[0].elements[field].value = request[field];
            }
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
    var prazo_dias = moment(data_prazo, 'DD/MM/YYYY HH:mm:ss').diff(moment(data_abertura, 'DD/MM/YYYY HH:mm:ss'), 'days');

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

function refuseRequest(tableRow) {
    var request_id = tableRow.get(0).dataset.requestId;
    var refusal_reason = $('.request-modal-form')[0].elements["motivo_recusa_field"].value;

    $.post("/gticchla/public/tecnico/refuse_support_request",
    {
        "request_id": request_id,
        "refusal_reason": refusal_reason
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
            <div class="form-group" style="display: none;">\
              <label for="motivo_recusa_field" class="col-sm-4 control-label">Motivo da recusa</label>\
              <div class="col-sm-8">\
                <textarea class="form-control" rows="3" id="motivo_recusa_field" readonly></textarea>\
              </div>\
            </div>\
          </form>\
        </div>\
        <div class="modal-footer" style="display: none;"></div>\
      </div>\
    </div>';
});
