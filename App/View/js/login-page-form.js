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

$(document).ready(function() {
    $('input#username').focus();
});
