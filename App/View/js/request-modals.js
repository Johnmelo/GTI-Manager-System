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

function defineSimpleModal(modalConfig, requestData) {
    let visibleFields = [];
    for (key in modalConfig.visible_fields) {
        visibleFields.push(key)
    }
    defineModal(modalConfig);
    fillUpRequestModal(requestData, visibleFields);
    showRequestModal();
}

function showRequestModal() {
    // Show modal
    $('.request-modal').modal('toggle');
}

function fillUpRequestModal(requestData, visibleFields) {
    // Get the desired modal input fields
    let modalFields = Array.from(
        $('.request-modal-form')[0].elements
    ).filter(i => visibleFields.indexOf(i.id) != -1);

    // Fill up the modal fields
    for (field of modalFields) {
        let fieldTitle = field.id.replace("_field", "");

        // When datetime field, put it in the proper format
        if (field.id === "prazo_field") {
            // If deadline info is included, just format the value
            if (request.hasOwnProperty("prazo_field")) {
                let datetime = moment(
                    requestData[fieldTitle],
                    'YYYY-MM-DD HH:mm:ss'
                ).format("DD/MM/YYYY [às] HH:mm");
                field.value = datetime;
            } else {
                // Otherwise, create the deadline with the 2 days default
                let datetime = moment()
                .add(2, 'days')
                .format("DD/MM/YYYY [às] HH:mm");
                field.value = datetime;
            }
        } else if (
            field.id === "data_abertura_field" ||
            field.id === "data_solicitacao_field" ||
            field.id === "data_finalizado_field") {
                // If datetime, just format it
                let datetime = moment(
                    requestData[fieldTitle],
                    'YYYY-MM-DD HH:mm:ss'
                ).format("DD/MM/YYYY [às] HH:mm");
                field.value = datetime;
        } else {
            // Other values except dates, just put it in the input field
            field.value = requestData[fieldTitle];
        }
    }
}

function acceptRequest(tableRow) {
    var request_id = tableRow.find('button[name="request-acceptance"]').val();
    var data_prazo = $('.request-modal-form')[0].elements["prazo_field"].value;

    // Check if inserted deadline is in valid format
    if (data_prazo.match(/\d{2}\/\d{2}\/\d{4} [aà]s \d{2}:\d{2}/)) {
        // Convert the deadline info into the format accepted by the DB
        data_prazo = data_prazo.replace(/[aà]s /g, "");
        data_prazo += ":00";
        var datetime = data_prazo.split(" ");
        var date = datetime[0].split("/");
        var time = datetime[1];
        datetime = date[2] + "-" + date[1] + "-" + date[0] + " " + time;
        $.post("/gtic/public/admin/open_call_request",
        {
            "call_request_id": request_id,
            "deadline_value": datetime
        })
        .done(function() {
            tableRow.remove();
            $('.request-modal').modal('toggle');
            setTimeout(function() {
                document.location.reload(true);
            }, 500);
        })
        .fail(function(data) {
            if (data.hasOwnProperty("responseJSON")) {
                response = data.responseJSON;
                if (response.hasOwnProperty("event") && response.event === "error") {
                    if (response.hasOwnProperty("type") && response.type === "deadline_wrong_format") {
                        alert("O prazo foi informado em um formato não reconhecido");
                    } else {
                        alert("Houve um problema não previsto");
                    }
                } else {
                    alert("Houve um problema não previsto");
                }
            } else {
                alert("Houve um problema não previsto");
            }
        });
    } else {
        alert("O prazo foi informado em um formato não reconhecido");
    }
}

function finalizeRequest(tableRow) {
    var request_id = $('.request-modal-form')[0].elements["id_chamado_field"].value;
    var parecer_tecnico = $('.request-modal-form')[0].elements["parecer_tecnico_field"].value;

    $.post("/gtic/public/admin/finalize_request",
    {
        "request_id": request_id,
        "technical_opinion": parecer_tecnico
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

function acquireRequest(tableRow) {
    var request_id = tableRow.find('button[name="btnJoin"]').val();
    var data_prazo = $('.request-modal-form')[0].elements["prazo_field"].value;

    // Check if inserted deadline is in valid format
    if (data_prazo.match(/\d{2}\/\d{2}\/\d{4} [aà]s \d{2}:\d{2}/)) {
        // Convert the deadline info into the format accepted by the DB
        data_prazo = data_prazo.replace(/[aà]s /g, "");
        data_prazo += ":00";
        var datetime = data_prazo.split(" ");
        var date = datetime[0].split("/");
        var time = datetime[1];
        datetime = date[2] + "-" + date[1] + "-" + date[0] + " " + time;
        $.post("/gtic/public/technician_select_request",
        {
            "call_request_id": request_id,
            "deadline_value": datetime
        })
        .done(function() {
            tableRow.remove();
            $('.request-modal').modal('toggle');
            setTimeout(function() {
                document.location.reload(true);
            }, 500);
        })
        .fail(function(data) {
            if (data.hasOwnProperty("responseJSON")) {
                response = data.responseJSON;
                if (response.hasOwnProperty("event") && response.event === "error") {
                    if (response.hasOwnProperty("type") && response.type === "deadline_wrong_format") {
                        alert("O prazo foi informado em um formato não reconhecido");
                    } else {
                        alert("Houve um problema não previsto");
                    }
                } else {
                    alert("Houve um problema não previsto");
                }
            } else {
                alert("Houve um problema não previsto");
            }
        });
    } else {
        alert("O prazo foi informado em um formato não reconhecido");
    }
}

function refuseRequest(tableRow) {
    var request_id = tableRow.get(0).dataset.requestId;
    var refusal_reason = $('.request-modal-form')[0].elements["motivo_recusa_field"].value;

    $.post("/gtic/public/tecnico/refuse_support_request",
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
              <label for="local_field" class="col-sm-4 control-label">Local</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="local_field" readonly>\
              </div>\
            </div>\
            <div class="form-group" style="display: none;">\
              <label for="status_field" class="col-sm-4 control-label">Status</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="status_field" readonly>\
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
