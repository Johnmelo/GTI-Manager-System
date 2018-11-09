function showAlert(title, message, type = "alert-info") {
    $('div.page-title > div.title_left')[0].insertAdjacentHTML('afterbegin', '<div class="alert ' + type + ' alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>' + title + '</strong> ' + message + '</div>');
}

$('#new-password-1, #new-password-2').on('keyup', function() {

    var pass1 = $('#new-password-1').val();
    var pass2 = $('#new-password-2').val();

    if (pass1 != pass2) {
        $('#new-password-1, #new-password-2').parents('.form-group').addClass('has-error');
        $('#helpText').parents('.form-group').css("display", "block");
        $('#btn-submit-password-form').removeClass('btn-primary');
        $('#btn-submit-password-form').prop('disabled', true);
    } else {
        $('#new-password-1, #new-password-2').parents('.form-group').removeClass('has-error');
        $('#helpText').parents('.form-group').css("display", "none");
        $('#btn-submit-password-form').addClass('btn-primary');
        $('#btn-submit-password-form').prop('disabled', false);
    }
});

$('#btn-submit-password-form').on('click', function() {
    $("html").css("cursor", "wait");
    $("body").css("pointer-events", "none");

    var current_password = $('#current-password').val();
    var new_password = $('#new-password-2').val();
    $('#current-password, #new-password-1, #new-password-2').val("");
    $('#btn-submit-password-form').removeClass('btn-primary');
    $('#btn-submit-password-form').prop('disabled', true);
    $('div.alert').remove();

    $.post("/gtic/public/change_password", {"current_password":current_password, "new_password":new_password})
    .done(function(data) {
        var response = data;
        if (response) {
            showAlert("Sucesso!", "Sua senha foi alterada", "alert-success");
        }
        $("html").css("cursor", "auto");
        $("body").css("pointer-events", "auto");
    })
    .fail(function(data) {
        var response = data.responseJSON;
        if (response && response.event === "error") {
            if (response.type === "wrong_current_password") {
                showAlert("Ops!", "Você errou a senha atual.", "alert-error");
            } else if (response.type === "db_op_failed") {
                showAlert("Ops!", "Ocorreu um erro e a senha não foi alterada.", "alert-error");
            }
        } else {
            showAlert("Ops!", "Houve uma falha não identificada.", "alert-error");
        }
        $("html").css("cursor", "auto");
        $("body").css("pointer-events", "auto");
    });
});
