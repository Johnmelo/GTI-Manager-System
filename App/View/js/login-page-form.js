// Code to alternate tabs, including via keyboard

var alternateTab = function (event) {
    if (event.which == 1 || event.which == 32 || event.which == 13) {
        if ( this.id == "login_tab" && !$(this).hasClass("selected") ) {

            $('#login_tab, #login-form').addClass("selected");
            $("#register_tab, #register-form").removeClass("selected");
            $('#content-wrapper').toggleClass("login-form-view register-form-view");
            if (event.target.nodeName != "INPUT") {
                $('input#username').focus();
                event.preventDefault();
                return false;
            }

        } else if ( this.id == "register_tab" && !$(this).hasClass("selected") ) {

            $("#register_tab, #register-form").addClass("selected");
            $("#login_tab, #login-form").removeClass("selected");
            $('#content-wrapper').toggleClass("login-form-view register-form-view");
            if (event.target.nodeName != "INPUT") {
                $('input#nomeCliente').focus();
                event.preventDefault();
                return false;
            }
        }
    }
};

$('#botoes-abas').children().each(function() {
    this.addEventListener("click", alternateTab, false);
    this.addEventListener("keydown", alternateTab, false);
});




// Validate input values
function inputsAreValid(form) {
    var all_inputs_filled = true;

    // If register form, check if name and last name only contains letters and spaces
    if (form == "register-form") {
        if (!$('#' + form + ' input[id=nomeCliente]').val().match(/^[a-zA-Z]*$/)
        || !$('#' + form + ' input[id=sobrenomeCliente]').val().match(/^[a-zA-Z]*$/)) {
            alert("Nome e sobrenome só podem conter letras e espaços!");
            return false;
        }
    }

    // Check if all inputs are filled in
    $('#' + form + ' input[type=text]').each(function() {
        if ($(this).val().match(/^\s*$/)) {
            all_inputs_filled = false;
            return false;
        }
    });
    if (all_inputs_filled) {
        return true;
    } else {
        alert("Preencha todos os campos");
        return false;
    }
}

// Submit login form
function submitLoginForm() {
    // Disable button
    $("input[id=login-submit]").val("Aguarde...");
    $("input[id=login-submit]").addClass("disabled");
    $("input[id=login-submit]").prop("disabled", true);

    // Validate form input values
    if (inputsAreValid("login-form")) {
        // Get form data
        var form = $('form[id=login-form]').get(0);
        var postData = {
            "username": form['username'].value,
            "password": form['password'].value
        }
        // Submit
        $.post("/gticchla/public/logar", postData)
        .done(function(data) {
            // Success

            // Receive the last login date and store in the sessionStorage
            sessionStorage.removeItem("lastLogin");
            if (data) {
                if (data.hasOwnProperty("lastLogin") && data.lastLogin !== null) {
                    sessionStorage.setItem("lastLogin", data.lastLogin);
                }
            }
            window.location.reload(true);
        }).fail(function(data) {
            // Failure
            if (data && data.responseJSON) {
                var response = data.responseJSON;
                if (response.event == "error") {
                    if (response.type == "invalid_credentials") {
                        alert("O usuário não foi encontrado");
                    } else {
                        alert("Não foi possível no momento");
                    }
                    $("input[id=login-submit]").val("Acessar");
                    $("input[id=login-submit]").removeClass("disabled");
                    $("input[id=login-submit]").prop("disabled", false);
                    return false;
                }
            }
            alert("Houve um problema");
            $("input[id=login-submit]").val("Acessar");
            $("input[id=login-submit]").removeClass("disabled");
            $("input[id=login-submit]").prop("disabled", false);
        });
    } else {
        $("input[id=login-submit]").val("Acessar");
        $("input[id=login-submit]").removeClass("disabled");
        $("input[id=login-submit]").prop("disabled", false);
    }
}

// Login form event listeners
$('input[id=login-submit]').on('click', function(e) {
    submitLoginForm();
});
$("input[id=username], input[id=password]").on('keyup', function(e) {
    if (e.which == 13) {
        submitLoginForm();
    }
});




// Submit request access form
function submitRequestAccessForm() {
    // Disable button
    $("input[id=register-submit]").val("Aguarde...");
    $("input[id=register-submit]").addClass("disabled");
    $("input[id=register-submit]").prop("disabled", true);

    // Validate form input values
    if(inputsAreValid("register-form")) {
        // Get form data
        var form = $('form[id=register-form]').get(0);
        var postData = {
            "nomeCliente": form['nomeCliente'].value,
            "sobrenomeCliente": form['sobrenomeCliente'].value,
            "emailCliente": form['emailCliente'].value,
            "loginCliente": form['loginCliente'].value,
            "setorCliente": form['setorCliente'].value,
            "matriculaCliente": form['matriculaCliente'].value
        }

        // Submit
        $.post("/gticchla/public/solicitar_acesso", postData)
        .done(function(data) {
            // Success
            alert("Solicitação recebida!\nCheque o e-mail fornecido para verificar os dados.");
            window.location.reload(true);
        }).fail(function(data) {
            // Failure
            var response = data.responseJSON;
            if (response.event == "error") {
                if (response.type == "invalid_email") {
                    alert("O endereço de email inserido é inválido");
                } else {
                    alert("Não foi possível no momento");
                }
                $("input[id=register-submit]").val("Solicitar acesso");
                $("input[id=register-submit]").removeClass("disabled");
                $("input[id=register-submit]").prop("disabled", false);
            }
        });
    } else {
        $("input[id=register-submit]").val("Solicitar acesso");
        $("input[id=register-submit]").removeClass("disabled");
        $("input[id=register-submit]").prop("disabled", false);
    }
}

// Request access form event listeners
$('input[id=register-submit]').on('click', function(e) {
    submitRequestAccessForm();
});
$("form[id=register-form] input[type=text]").on('keyup', function(e) {
    if (e.which == 13) {
        submitRequestAccessForm();
    }
});




$(document).ready(function() {
    $('input#username').focus();
});
