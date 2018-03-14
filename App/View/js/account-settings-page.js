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

    var current_password = $('#current-password').val();
    var new_password = $('#new-password-2').val();
    $('#current-password, #new-password-1, #new-password-2').val("");
    $('#btn-submit-password-form').removeClass('btn-primary');
    $('#btn-submit-password-form').prop('disabled', true);
    $('div.alert').remove();

    $.post("/gticchla/public/change_password", {"current_password":current_password, "new_password":new_password})
    .done(function(data) {
        $('div.page-title > div.title_left')[0].insertAdjacentHTML('afterbegin', '<div class="alert alert-success alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Sucesso!</strong> Sua senha foi modificada</div>');
    })
    .fail(function(data) {
        $('div.page-title > div.title_left')[0].insertAdjacentHTML('afterbegin', '<div class="alert alert-error alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button><strong>Ops!</strong> Houve um problema e a senha não foi modificada</div>');
    });
});
