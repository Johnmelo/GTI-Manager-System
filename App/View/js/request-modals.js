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
    $('.autocomplete-suggestions').remove();
    $('.tech-items-list').empty();
    $('.responsaveis-wrapper').removeClass('editable editing');
    checkIfMaxTechnician();

    // Set config
    $('.request-modal').find('.modal-header > h4')[0].innerHTML = modalConfig.title;

    // Define the form inputs to be visible and its readonly setting
    var visibleFields = modalConfig.visible_fields;
    for (var key in visibleFields) {
        if (key === "responsaveis_field") {
            $('.responsaveis-wrapper').addClass(visibleFields[key]);
        }
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
        } else if (field.id === "responsaveis_field") {
            // When responsible technicians area
            if (requestData["responsaveis"] !== undefined) {
              fillTicketTechniciansList(requestData.responsaveis);
            } else {
              if (window.myself) {
                insertTechnicianCard(myself.name, '', 'acquiring own-card');
              }
            }
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
          } else if (response.type === "db_conn_failed") {
              alert("Falha na conexão com o banco de dados");
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
          } else if (response.type === "db_conn_failed") {
            alert("Falha na conexão com o banco de dados");
            return false;
          } else if (response.type === "db_op_failed") {
            alert("Não foi possível alterar os dados no banco de dados");
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
    // Get the technicians responsible and their responsibility
    let techniciansList = getTechniciansList();

    if (!techniciansList) {
      return false;
    }

    $("html").css("cursor", "wait");
    $("body").css("pointer-events", "none");
    $.post("/gtic/public/tecnico/technician_select_request",
    {
      "ticket_id": ticketID,
      "deadline_value": datetime,
      "technicians_list": techniciansList
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
            } else if (response.type === "db_conn_failed") {
              alert("Falha na conexão com o banco de dados");
              return false;
            } else if (response.type === "db_op_failed") {
              alert("Não foi possível alterar os dados no banco de dados");
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
          } else if (response.type === "db_conn_failed") {
            alert("Falha na conexão com o banco de dados");
            return false;
          } else if (response.type === "db_op_failed") {
            alert("Não foi possível alterar os dados no banco de dados");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
  });
}

function acceptInvitation(e) {
  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/respond_ticket_sharing_invitation", {
    "ticketID": $('#id_chamado_field').val(),
    "response": 'accepted',
    "responsibility": $(e).closest('.tech-item-wrapper').find('textarea').val()
  })
  .done(response => {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (response && response.event === "success") {
      if (response.ticket) {
        if (response.type && response.type === "ticket_sharing_invitation_accepted") {
          // ticketSharingAccepted(response.ticket);
          $(e).closest('.tech-item-wrapper').removeClass().addClass('tech-item-wrapper viewing in-process own-card');
          $(e).closest('.tech-item-wrapper').find('textarea').prop('readOnly', true);
          return true;
        }
      }
    }
    window.location.reload(true);
  })
  .fail(data => {
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
          } else if (response.type === "db_conn_failed") {
            alert("Falha na conexão com o banco de dados");
            return false;
          } else if (response.type === "db_op_failed") {
            alert("Não foi possível alterar os dados no banco de dados");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
  });
}

function refusedInvitation(e) {
  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/respond_ticket_sharing_invitation", {
    "ticketID": $('#id_chamado_field').val(),
    "response": 'declined',
    "responsibility": $(e).closest('.tech-item-wrapper').find('textarea').val()
  })
  .done(response => {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");

    if (response && response.event === "success") {
      if (response.ticket) {
        if (response.type && response.type === "ticket_sharing_invitation_declined") {
          // ticketSharingAccepted(response.ticket);
          $(e).closest('.tech-item-wrapper').removeClass().addClass('tech-item-wrapper viewing refused own-card');
          $(e).closest('.tech-item-wrapper').find('textarea').attr('readonly', 'readonly');
          return true;
        }
      }
    }
    window.location.reload(true);
  })
  .fail(data => {
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
          } else if (response.type === "db_conn_failed") {
            alert("Falha na conexão com o banco de dados");
            return false;
          } else if (response.type === "db_op_failed") {
            alert("Não foi possível alterar os dados no banco de dados");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
  });
}

function editTechListBtn() {
  if (window.myself) {
    let backup = $('.tech-items-list').clone();
    window.technicianResDataBackup = backup;
    $('.responsaveis-wrapper').addClass("editing");
    if (myself.role === "GERENTE") {
      $('.tech-item-wrapper').attr('class', 'tech-item-wrapper new-invitation creating other-technician');
      $('.tech-item-wrapper .tech-name-input').prop("readonly", false);
      $('.tech-item-wrapper textarea').prop("readonly", false);
    }
    buildAutocompleteInputs();
  }
}

function saveTechListBtn() {
  let ticketID = $('.request-modal input#id_chamado_field').val();
  let techniciansRespList = getTechniciansList();
  if (!techniciansRespList) {
    return false;
  } else if (techniciansRespList.length === 0) {
    techniciansRespList = null;
  }

  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/update_ticket_responsible_technicians",
  {
    "ticket_id": ticketID,
    "technicians_list": techniciansRespList
  })
  .done(response => {
    // Unblock page
    $("html").css("cursor", "auto");
    $("body").css("pointer-events", "auto");
    if (response && response.event === "success") {
      if (response.type && response.type === "ticket_responsible_technicians_updated") {
        if (response.ticket){
          ticketTechniciansUpdated(response.ticket);
          $('.responsaveis-wrapper').removeClass("editing");
          if (response.ticket['responsaveis'] === undefined) {
            // If all technicians were removed
            $('.request-modal').modal('hide');
            return true;
          } else {
            fillTicketTechniciansList(response.ticket.responsaveis);
            return true;
          }
        }
      }
    }
    window.location.reload(true);
  })
  .fail(data => {
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
          } else if (response.type === "db_conn_failed") {
            alert("Falha na conexão com o banco de dados");
            return false;
          } else if (response.type === "db_op_failed") {
            alert("Não foi possível alterar os dados no banco de dados");
            return false;
          } else if (response.type === "not_unique_tech_ids") {
            alert("Erro: se certifique que cada técnico aparece na lista de responsáveis apenas uma vez");
            return false;
          }
        }
      }
    }
    alert("Houve uma falha não identificada");
  });
}

function responsibilityDone(e) {
  let ticketID = $('#id_chamado_field').val()
  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/responsibility_done", { 'ticketID': ticketID })
  .done(response => {
     // Unblock page
     $("html").css("cursor", "auto");
     $("body").css("pointer-events", "auto");

     if (response && response.event === "success") {
       if (response.ticket) {
         if (response.type && response.type === "responsibility_done") {
           // ticketResponsibilityDone(response.ticket);
           fillTicketTechniciansList(response.ticket.responsaveis);
           return true;
         }
       }
     }
     window.location.reload(true);
   })
  .fail(data => {
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
           } else if (response.type === "db_conn_failed") {
             alert("Falha na conexão com o banco de dados");
             return false;
           } else if (response.type === "db_op_failed") {
             alert("Não foi possível alterar os dados no banco de dados");
             return false;
           }
         }
       }
     }
     alert("Houve uma falha não identificada");
   });
}

function cancelActionBtn(e) {
  let card = $(e).closest('.tech-item-wrapper');
  let cardClasses = card.attr('class');
  if (cardClasses.match(/new-invitation open-ticket other-technician/g) !== null) {
    // If giving up of creating an invitation to an already in process ticket
    removeTechnicianItemBtn(e);
  } else {
    // If giving up of editing a card data
    card.removeClass('editing').addClass('viewing');
    card.find('.tech-name-input').prop('readonly', true);
    card.find('textarea').prop('readonly', true);
    let backedUpActivity = window.cardsBackup.get(`${card.index()}`);
    card.find('.tech-name-input').val(backedUpActivity.technician);
    card.find('textarea').val(backedUpActivity.responsibility);
    autosize.update(card.find('textarea'));
  }
}

function editCardBtn(e) {
  if (window.cardsBackup === undefined) {
    window.cardsBackup = new Map();
  }
  let card = $(e).closest('.tech-item-wrapper');
  let cardClasses = card.attr('class');
  let technician = card.find('.tech-name-input').val();
  let cardResponsibility = card.find('textarea').val();
  // change the layout
  card.removeClass('viewing').addClass('editing');
  if (card.attr('class').match(/own-card/) === null) {
    card.find('.tech-name-input').prop('readonly', false);
  }
  card.find('textarea').prop('readonly', false);
  buildAutocompleteInputs();
  // store the current info for in case the edition is canceled
  window.cardsBackup.set(`${card.index()}`, { 'technician': technician, 'responsibility': cardResponsibility});
  if (cardClasses.match(/tech-item-wrapper viewing in-process own-card/g) !== null) {
  } else if (cardClasses.match(/tech-item-wrapper pending-acceptance viewing other-technician"]/g) !== null) {
  }
}

function reacquireTicketBtn(e) {
  let ticketID = $('#id_chamado_field').val()
  let card = $(e).closest('.tech-item-wrapper');
  let cardResponsibility = card.find('textarea').val();
  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/reaquire_ticket", {
    'ticketID': ticketID,
    'responsibility': cardResponsibility
  })
  .done(response => {
     // Unblock page
     $("html").css("cursor", "auto");
     $("body").css("pointer-events", "auto");

     if (response && response.event === "success") {
       if (response.ticket) {
         if (response.type && response.type === "ticket_reaquired") {
           // ticketResponsibilityDone(response.ticket);
           fillTicketTechniciansList(response.ticket.responsaveis);
           return true;
         }
       }
     }
     window.location.reload(true);
   })
  .fail(data => {
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
           } else if (response.type === "db_conn_failed") {
             alert("Falha na conexão com o banco de dados");
             return false;
           } else if (response.type === "db_op_failed") {
             alert("Não foi possível alterar os dados no banco de dados");
             return false;
           }
         }
       }
     }
     alert("Houve uma falha não identificada");
   });
}

function saveResponsibilityChange(e) {
  let ticketID = $('#id_chamado_field').val()
  let card = $(e).closest('.tech-item-wrapper');
  let technician = card.find('.tech-name-input').val();
  let cardResponsibility = card.find('textarea').val();

  // check if a technician was selected
  let isTechnicianListed = ((window.technicians.filter(x => x.value === technician)).length > 0);
  if (!isTechnicianListed) {
      alert("Selecione um dos técnicos sugeridos");
      return false;
  }
  let technicianData = window.technicians.find(t => t.value === technician);
  let technicianID = technicianData.data.userID;

  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/save_responsibility_change", {
    'ticketID': ticketID,
    'technicianID': technicianID,
    'responsibility': cardResponsibility
  })
  .done(response => {
     // Unblock page
     $("html").css("cursor", "auto");
     $("body").css("pointer-events", "auto");

     if (response && response.event === "success") {
       if (response.ticket) {
         if (response.type && response.type === "responsibility_change_saved") {
           // ticketResponsibilityDone(response.ticket);
           fillTicketTechniciansList(response.ticket.responsaveis);
           return true;
         }
       }
     }
     window.location.reload(true);
   })
  .fail(data => {
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
           } else if (response.type === "db_conn_failed") {
             alert("Falha na conexão com o banco de dados");
             return false;
           } else if (response.type === "db_op_failed") {
             alert("Não foi possível alterar os dados no banco de dados");
             return false;
           }
         }
       }
     }
     alert("Houve uma falha não identificada");
   });
}

function cancelTechListEditionBtn() {
  $('.tech-items-list').remove();
  $('.autocomplete-suggestions').remove();
  $('.responsaveis-wrapper').removeClass("editing");
  $('.responsaveis-wrapper').prepend(window.technicianResDataBackup);
  checkIfMaxTechnician();
}

function insertTechnicianItemBtn() {
  if (window.myself && window.myself.role === "GERENTE") {
    insertTechnicianCard('', '', 'new-invitation open-ticket other-technician');
  } else {
    let ticketID = $('#id_chamado_field').val();
    isTicketInProcess = techniciansInProcessTickets.findIndex(t => t.id_chamado === ticketID) !== -1;
    if (isTicketInProcess) {
      insertTechnicianCard('', '', 'new-invitation open-ticket other-technician');
    } else {
      insertTechnicianCard('', '', 'new-invitation creating other-technician');
    }
  }
}

function removeTechnicianItemBtn(e) {
  let card = $(e).closest('.tech-item-wrapper');
  let cardInputName = card.find('.tech-name-input');
  let inputNameList = $('.tech-name-input:not([readonly])');
  // Get the technician item index
  let indexCardToRemove = inputNameList.index(cardInputName);
  // Remove the autocomplete object related to the item being removed
  $('.autocomplete-suggestions').eq(indexCardToRemove).remove();
  // Then remove the item
  card.remove();
  updateTechnicianSuggestionsAvailableList();
  // If someone is being removed, then it's possible to add at least one technician.
  // Activate the "add" button.
  checkIfMaxTechnician();

  // If directly removing the invitation from the DB
  if (card.attr('class').match(/new-invitation open-ticket other-technician/g) === null) {
    // Sharing invitation data
    let ticketID = $('#id_chamado_field').val()
    let technician = card.find('.tech-name-input').val();
    let technicianData = window.technicians.find(t => t.value === technician);
    let technicianID = technicianData.data.userID;

    if ($('.responsaveis-wrapper').attr('class').match(/editable editing/g) === null) {
      // Unblock page
      $("html").css("cursor", "auto");
      $("body").css("pointer-events", "auto");
      $.post('/gtic/public/tecnico/discard_invite', {
        'ticketID': ticketID,
        'technicianID': technicianID
      })
      .done(response => {
        // Unblock page
        $("html").css("cursor", "auto");
        $("body").css("pointer-events", "auto");

        if (response && response.event === "success") {
          if (response.ticket) {
            if (response.type && response.type === "ticket_sharing_invitation_discarded") {
              // ticketResponsibilityDone(response.ticket);
              fillTicketTechniciansList(response.ticket.responsaveis);
              return true;
            }
          }
        }
        window.location.reload(true);
      })
      .fail(data => {
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
              } else if (response.type === "db_conn_failed") {
                alert("Falha na conexão com o banco de dados");
                return false;
              } else if (response.type === "db_op_failed") {
                alert("Não foi possível alterar os dados no banco de dados");
                return false;
              }
            }
          }
        }
        alert("Houve uma falha não identificada");
      });
    }
  }
}

function inviteTechnicianBtn(e) {
  let ticketID = $('#id_chamado_field').val()
  let card = $(e).closest('.tech-item-wrapper');
  let technician = card.find('.tech-name-input').val();
  let cardResponsibility = card.find('textarea').val();

  // check if a technician was selected
  let isTechnicianListed = ((window.technicians.filter(x => x.value === technician)).length > 0);
  if (!isTechnicianListed) {
      alert("Selecione um dos técnicos sugeridos");
      return false;
  }
  let technicianData = window.technicians.find(t => t.value === technician);
  let technicianID = technicianData.data.userID;

  $("html").css("cursor", "wait");
  $("body").css("pointer-events", "none");
  $.post("/gtic/public/tecnico/invite_technician", {
    'ticketID': ticketID,
    'technicianID': technicianID,
    'responsibility': cardResponsibility
  })
  .done(response => {
     // Unblock page
     $("html").css("cursor", "auto");
     $("body").css("pointer-events", "auto");

     if (response && response.event === "success") {
       if (response.ticket) {
         if (response.type && response.type === "technician_invited") {
           // ticketResponsibilityDone(response.ticket);
           fillTicketTechniciansList(response.ticket.responsaveis);
           return true;
         }
       }
     }
     window.location.reload(true);
   })
  .fail(data => {
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
           } else if (response.type === "db_conn_failed") {
             alert("Falha na conexão com o banco de dados");
             return false;
           } else if (response.type === "db_op_failed") {
             alert("Não foi possível alterar os dados no banco de dados");
             return false;
           }
         }
       }
     }
     alert("Houve uma falha não identificada");
   });
}

function getTechniciansList() {
  let techniciansList = [];
  let okToContinue = true;
  // Check the necessary variables availability
  if (!myself && !technicians) {
    return false;
  }
  // Get the inserted technicians
  $('.tech-items-list').children().each((index, element) => {
    let techNameInput = $(element).find('input.tech-name-input').get(0);
    let techActivityInput = $(element).find('textarea').get(0);

    if ($(element).hasClass('own-card')) {
      techniciansList.push(
        {
          "technicianID": myself.id,
          "technicianActivity": techActivityInput.value.replace(/^\s*/, '').replace(/\s*$/, '')
        }
      );
    } else {
      let technicianName = techNameInput.value;
      // Check if it's a valid option (an autocomplete suggestion)
      let isTechnicianListed = ((window.technicians.filter(x => x.value === technicianName)).length > 0);
      if (isTechnicianListed) {
        let technician = window.technicians.find(t => t.value === technicianName)
        let technicianID = technician.data.userID;
        let technicianActivity = techActivityInput.value.replace(/^\s*/, '').replace(/\s*$/, '');
        techniciansList.push(
          {
            "technicianID": technicianID,
            "technicianActivity": technicianActivity
          }
        );
      } else {
        alert("Você adicionou um campo para adicionar outro técnico mas não definiu o técnico");
        return okToContinue = false;
      }
    }
  });

  if (okToContinue) {
    return techniciansList;
  }
  return false;
}

function fillTicketTechniciansList(ticketTechniciansData) {
  // Reset the list
  $('.autocomplete-suggestions').remove();
  $('.tech-items-list').empty();

  if (window.myself) {
    // If logged in user is one of the responsible technicians
    // display his/her card first
    let ownResponsibilityData = ticketTechniciansData.find(rd => rd.id_tecnico === myself.id);
    let technicianHasTicket = false;
    if (ownResponsibilityData) {
      let techName = technicians.find(t => t.data.userID === ownResponsibilityData.id_tecnico).value;
      let invitationStatus;
      if (ownResponsibilityData.status === "1") {
        technicianHasTicket = true;
        invitationStatus = 'viewing in-process own-card';
      } else if (ownResponsibilityData.status === "0") {
        invitationStatus = 'new-invitation responding own-card';
      } else if (ownResponsibilityData.status === "-1") {
        invitationStatus = 'viewing refused own-card';
      } else if (ownResponsibilityData.status === "2") {
        technicianHasTicket = true;
        invitationStatus = 'viewing done own-card';
      }
      insertTechnicianCard(techName, ownResponsibilityData.atividade, invitationStatus);
      updateTechnicianSuggestionsAvailableList();
      ticketTechniciansData = ticketTechniciansData.filter(r => r !== ownResponsibilityData);
    }
    for (responsibilityData of ticketTechniciansData) {
      let techName = technicians.find(t => t.data.userID === responsibilityData.id_tecnico).value;
      let invitationStatus;
      if (responsibilityData.status === "1") {
        invitationStatus = "viewing in-process other-technician";
      } else if (responsibilityData.status === "0") {
        invitationStatus = "pending-acceptance viewing other-technician";
      } else if (responsibilityData.status === "-1") {
        invitationStatus = "refused viewing other-technician";
      } else if (responsibilityData.status === "2") {
        invitationStatus = "done viewing other-technician";
      }
      invitationStatus += (technicianHasTicket) ? '' : ' not-editable';
      insertTechnicianCard(techName, responsibilityData.atividade, invitationStatus);
      updateTechnicianSuggestionsAvailableList();
    }
  } else {
    for (responsibilityData of ticketTechniciansData) {
      let invitationStatus = '';
      if (responsibilityData.status === "1") {
        invitationStatus = "viewing in-process other-technician";
      } else if (responsibilityData.status === "2") {
        invitationStatus = "done viewing other-technician";
      }
      insertTechnicianCard(responsibilityData.tecnico, responsibilityData.atividade, invitationStatus);
  }
}
}

function insertTechnicianCard(technicianName, technicianActivity, classes) {
  let techniciansList = $('.tech-items-list');
  let noActivity = (technicianActivity === null || technicianActivity.match(/^\s*$/) !== null);

  // Name input config
  let techNameReadonly = '';
  if (
    classes !== 'new-invitation creating other-technician' &&
    classes !== 'pending-acceptance editing other-technician' &&
    classes !== 'new-invitation open-ticket other-technician'
  ) {
    techNameReadonly = 'readonly';
  }

  // Activity textarea config
  technicianActivity = (noActivity) ? '' : technicianActivity;
  let textareaReadonly = '';
  if (
    classes.match(/viewing/g) !== null ||
    classes.match(/in-process/g) !== null ||
    classes.match(/refused/g) !== null ||
    classes.match(/done/g) !== null
  ) {
    textareaReadonly = 'readonly';
  }
  if (noActivity && !classes.match(/acquiring|new-invitation|editing/)) {
    classes += " no-activity";
  }
  let textareaPlaceholder = (classes.match(/own-card/gi)) ? 'Descreva sua responsabilidade' : 'Descreva a parte que ele ficou encarregado';

  let technicianItem = `\
  <div class="tech-item-wrapper ${classes}">\
    <div class="content-wrapper">\
      <div class="item-header">\
        <div class="item-titles">\
          <div class="titles-upper-row">\
            <input type="text" class="form-control tech-name-input" placeholder="Digite o nome ou usuário do técnico" value="${technicianName}" ${techNameReadonly}>\
            <button type="button" class="btn btn-danger remove-card-btn" onclick="removeTechnicianItemBtn(this)"><i class="fas fa-minus"></i></button>\
          </div>\
        </div>\
      </div>\
      <textarea rows="1" cols="5" placeholder="${textareaPlaceholder}" ${textareaReadonly}>${technicianActivity}</textarea>\
        <div class="status-row">\
        <button type="button" class="btn status pending-status"><i class="fas fa-clock"></i> Pendendo aceite</button>\
        <button type="button" class="btn status refused-status"><i class="fas fa-ban"></i> Convite recusado</button>\
        <button type="button" class="btn status in-process-status"><i class="fas fa-spinner"></i> Em andamento</button>\
        <button type="button" class="btn status done-status"><i class="fas fa-check"></i> Realizado</button>\
      </div>\
      <div class="card-bottom-btn-row">\
        <button type="button" class="btn btn-danger cancel-btn" onclick="cancelActionBtn(this)"><i class="fas fa-times"></i> Cancelar</button>\
        <button type="button" class="btn btn-primary invite-btn" onclick="inviteTechnicianBtn(this)"><i class="fas fa-share"></i> Convidar</button>\
        <button type="button" class="btn btn-danger refuse-btn" onclick="refusedInvitation(this)"><i class="fas fa-times"></i> Recusar</button>\
        <button type="button" class="btn btn-success acquire-btn" onclick="acceptInvitation(this)"><i class="fas fa-check"></i> Assumir</button>\
        <button type="button" class="btn btn-primary save-btn" onclick="saveResponsibilityChange(this)"><i class="fas fa-check"></i> Salvar</button>\
        <button type="button" class="btn btn-primary edit-btn" onclick="editCardBtn(this)"><i class="fas fa-edit"></i> Editar</button>\
        <button type="button" class="btn btn-success done-btn" onclick="responsibilityDone(this)"><i class="fas fa-check"></i> Finalizar</button>\
        <button type="button" class="btn btn-primary reacquire-btn" onclick="reacquireTicketBtn(this)"><i class="fas fa-redo"></i> Reassumir</button>\
      </div>\
    </div>\
  </div>\
  `;

  techniciansList.append(technicianItem);
  updateTechnicianSuggestionsAvailableList();
  let techNameInputSelector = (window.myself && window.myself.role === "GERENTE") ? '.tech-item-wrapper .tech-name-input' : '.tech-item-wrapper:not(.own-card) .tech-name-input';
  $(techNameInputSelector).off('input');
  $(techNameInputSelector).on('input', (e) => {
    updateTechnicianSuggestionsAvailableList();
    $(e.currentTarget).autocomplete().options.lookup = availableTechnicianSuggestions;
  });
  buildAutocompleteInputs();
  checkIfMaxTechnician();
}

function buildAutocompleteInputs() {
  if (window.myself) {
    const _formatRegexp = function(q) {
      q = q.replace(/[eéèêẽëEÉÈÊẼË]/gi,'[eéèêẽëEÉÈÊẼË]');
      q = q.replace(/[aáàâãäAÁÀÂÃÄÅÆ]/gi,'[aáàâãäAÁÀÂÃÄÅÆ]');
      q = q.replace(/[cçCÇ]/gi,'[cçCÇ]');
      q = q.replace(/[iíìîïIÌÍÎÏ]/gi,'[iíìîïIÌÍÎÏ]');
      q = q.replace(/[oóòôõöOÓÒÔÕÖ]/gi,'[oóòôõöOÓÒÔÕÖ]');
      q = q.replace(/[uúùûüUÚÙÛÜ]/gi,'[uúùûüUÚÙÛÜ]');
      q = q.replace(/[nñNÑ]/gi,'[nñNÑ]');
      q = q.replace(/[yYÿ^yÝ]/gi,'[yYÿ^yÝ]');
      return q;
    }
    const _autocompleteLookup = function (suggestion, originalQuery, queryLowerCase) {
      let pattern = '(\\b|)('+$.Autocomplete.utils.escapeRegExChars(queryLowerCase)+')';
      pattern = _formatRegexp(pattern);
      let matcher = new RegExp(pattern);
      let ret = suggestion.value.toLowerCase().match(matcher);
      return ret;
    };
    const _autocompleteFormatResult = function (suggestion, currentValue) {
      let pattern = '(\\b|)('+$.Autocomplete.utils.escapeRegExChars(currentValue)+')';
      pattern = _formatRegexp(pattern);
      return suggestion.value.replace(new RegExp(pattern, 'gi'), '<strong>$1$2<\/strong>');
    };
    $('.tech-name-input:not([readonly])').autocomplete({
      minChars: 0,
      lookup: window.availableTechnicianSuggestions,
      showNoSuggestionNotice: true,
      noSuggestionNotice: "Técnico não encontrado no sistema",
      lookupFilter: _autocompleteLookup,
      formatResult: _autocompleteFormatResult,
      onSelect: function(option) {
        updateTechnicianSuggestionsAvailableList();
        buildAutocompleteInputs();
      }
    });
    autosize($('textarea'));
  }
}

function afterShownModal() {
  autosize.update($('textarea'))
}

function checkIfMaxTechnician() {
  // If there isn't available technicians anymore, deactivate the "add" button
  let qtyAddedCards = $('.tech-items-list').children().length;
  let qtyTechnicians = technicians.length;
  if (qtyAddedCards >= qtyTechnicians) {
    $('.responsaveis-wrapper').addClass('restrained');
  } else {
    $('.responsaveis-wrapper').removeClass('restrained');
  }
}

function updateTechnicianSuggestionsAvailableList() {
  window.availableTechnicianSuggestions = technicians.slice();
  $('.tech-name-input').each((index, element) => {
    let isOwnCard = $(element.closest('.tech-item-wrapper')).hasClass('own-card');
    if (isOwnCard && window.myself.role !== "GERENTE") {
      availableTechnicianSuggestions = availableTechnicianSuggestions.filter(sug => sug.data.userID !== myself.id);
    } else {
      availableTechnicianSuggestions = availableTechnicianSuggestions.filter(sug => sug.value !== element.value);
    }
  });
}

// Inserting HTML structure into modal tag
$(document).ready(function() {
    $.post("/gtic/public/get_support_users_suggestions")
    .done(function(data) {
      window.technicians = data;
    })
    .fail(function(data) {
      alert("Houve um problema enquanto carregava a lista de técnicos para o \"autocomplete\"");
    });

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
            <div class="form-group" style="display: none;">\
              <label for="responsaveis_field" class="control-label">Técnicos responsáveis:</label>\
              <div class="">\
                <input type="hidden" class="form-control" id="responsaveis_field" readonly>\
              </div>\
              <div class="responsaveis-wrapper">\
                <div class="tech-items-list"></div>\
                <div class="list-buttons-row">\
                  <button type="button" class="btn btn-primary edit-techs-btn" onclick="editTechListBtn()"><i class="fas fa-edit"></i> Editar responsáveis</button>\
                  <button type="button" class="btn btn-primary cancel-edit-btn" onclick="cancelTechListEditionBtn()"><i class="fas fa-times"></i> Cancelar edição</button>\
                  <button type="button" class="btn btn-primary add-tech-btn" onclick="insertTechnicianItemBtn()"><i class="fas fa-plus"></i> Adicionar outro técnico</button>\
                  <button type="button" class="btn btn-primary save-tech-btn" onclick="saveTechListBtn()"><i class="fas fa-save"></i> Salvar</button>\
                </div>\
              </div>\
            </div>\
          </form>\
        </div>\
        <div class="modal-footer" style="display: none;"></div>\
      </div>\
    </div>';

    $('.modal').on('shown.bs.modal', afterShownModal);
});
