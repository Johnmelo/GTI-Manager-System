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
            if (requestData.hasOwnProperty("prazo")) {
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
            field.id === "data_solicitacao_field" ||
            field.id === "data_abertura_field" ||
            field.id === "data_assumido_field" ||
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

function acceptRequest(requestId) {
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

    $("html").css("cursor", "wait");
    $("body").css("pointer-events", "none");
    $.post("/gtic/public/admin/open_call_request",
    {
      "call_request_id": requestId,
      "deadline_value": datetime
    })
    .done(function(response) {
      // Unblock page
      $("html").css("cursor", "auto");
      $("body").css("pointer-events", "auto");

      if (response && response.event === "success") {
        if (response.type && response.type === "new_ticket") {
          if (response.ticket) {
            ticketRequestAccepted(response.ticket);
            $('.request-modal').modal('hide');
            return true;
          }
        }
      }
      window.location.reload(true);
    })
    .fail(function(data) {
      // Unblock page
      $("html").css("cursor", "auto");
      $("body").css("pointer-events", "auto");

      if (data && data.responseJSON) {
        response = data.responseJSON;
        if (response.event && response.event === "error") {
          if (response.type) {
            if(response.type === "deadline_wrong_format") {
              alert("O prazo foi informado em um formato não reconhecido");
              return false;
            } else if (response.type === "missing_data") {
              alert("Há dados fazendo falta");
              return false;
            } else if (response.type === "db_op_failed") {
              alert("O chamado não pôde ser armazenado no banco");
              return false;
            }
          }
        }
      }
      alert("Houve uma falha não identificada");
    });
  } else {
    alert(
      "O prazo foi informado em um formato não reconhecido.\
      \nO formato esperado é o seguinte:\
      \nDD/MM/YYYY às HH:mm"
    );
  }
}

function finalizeRequest(requestId) {
  let parecerTecnico = $('.request-modal-form')[0].elements["parecer_tecnico_field"].value;
  parecerTecnico = parecerTecnico.replace(/^\s+/g, '').replace(/\s+$/g, '');

  if (parecerTecnico.match(/^\s*$/)) {
    alert("Insira o parecer técnico");
    return false;
  }

  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/admin/finalize_request",
  {
    "request_id": requestId,
    "technical_opinion": parecerTecnico
  })
  .done(function(response) {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (response && response.event === "success") {
      if (response.type && response.type === "finalized_ticket") {
        if (response.ticket){
          ticketClosed(response.ticket);
          $('.request-modal').modal('hide');
          return true;
        }
      }
    }
    window.location.reload(true);
  })
  .fail(function(data) {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (data && data.responseJSON) {
      response = data.responseJSON;
      if (response.event && response.type) {
        if (response.event === "error") {
          if (response.type === "missing_data") {
            alert("Há dados fazendo falta");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
  });
}

function acquireRequest(ticketID) {
  let data_prazo = $('.request-modal-form')[0].elements["prazo_field"].value;

  // Check if inserted deadline is in valid format
  if (data_prazo.match(/\d{2}\/\d{2}\/\d{4} [aà]s \d{2}:\d{2}/)) {
    // Convert the deadline info into the format accepted by the DB
    data_prazo = data_prazo.replace(/[aà]s /g, "");
    data_prazo += ":00";
    let datetime = data_prazo.split(" ");
    let date = datetime[0].split("/");
    let time = datetime[1];
    datetime = date[2] + "-" + date[1] + "-" + date[0] + " " + time;

    $("html").css("cursor", "wait");
    $("body").css("pointer-events", "none");
    $.post("/gtic/public/technician_select_request",
    {
      "ticket_id": ticketID,
      "deadline_value": datetime
    })
    .done(function(response) {
      // Unblock page
      $("html").css("cursor", "auto");
      $("body").css("pointer-events", "auto");

      if (response && response.event === "success") {
        if (response.type && response.type === "acquired_ticket") {
          if (response.ticket){
            ticketAcquired(response.ticket);
            $('.request-modal').modal('hide');
            return true;
          }
        }
      }
      window.location.reload(true);
    })
    .fail(function(data) {
      // Unblock page
      $("html").css("cursor", "auto");
      $("body").css("pointer-events", "auto");

      if (data && data.responseJSON) {
        response = data.responseJSON;
        if (response.event && response.type) {
          if (response.event === "error") {
            if (response.type === "missing_data") {
              alert("Há dados fazendo falta");
              return false;
            } else if (response.type === "deadline_wrong_format") {
              alert("O prazo foi informado em um formato não reconhecido");
              return false;
            }
          }
        }
      }
      alert("Houve uma falha não identificada");
    });
  } else {
    alert(
      "O prazo foi informado em um formato não reconhecido.\
      \nO formato esperado é o seguinte:\
      \nDD/MM/YYYY às HH:mm"
    );
  }
}

function refuseRequest(requestId) {
  let refusalReason = $('.request-modal-form')[0].elements["motivo_recusa_field"].value;
  refusalReason = refusalReason.replace(/^\s+/g, '').replace(/\s+$/g, '');

  if (refusalReason.match(/^\s*$/)) {
    alert("Insira o motivo da recusa");
    return false;
  }

  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");

  $.post("/gtic/public/tecnico/refuse_support_request",
  {
    "request_id": requestId,
    "refusal_reason": refusalReason
  })
  .done(function(response) {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (response && response.event === "success") {
      if (response.type && response.type === "ticket_request_refused") {
        if (response.request) {
          ticketRequestRefused(response.request);
          $('.request-modal').modal('hide');
          return true;
        }
      }
    }
    window.location.reload(true);
  })
  .fail(function(data) {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (data && data.responseJSON) {
      response = data.responseJSON;
      if (response.event && response.event === "error") {
        if (response.type) {
          if (response.type === "missing_data") {
            alert("Há dados fazendo falta");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
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
              <label for="data_assumido_field" class="col-sm-4 control-label">Data de assunção</label>\
              <div class="col-sm-8">\
                <input type="text" class="form-control" id="data_assumido_field" readonly>\
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
