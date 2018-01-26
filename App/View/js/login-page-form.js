var abas = document.getElementById("botoes-abas").children;

var alternateTab = function (event) {
    if (event.which == 1 || event.which == 32 || event.which == 13) {
        if ( this.id == "login_tab" && this.className.indexOf("selected") == -1 ) {

            document.getElementById("login_tab").className += "selected";
            document.getElementById("login-form").className += "selected";

            document.getElementById("register_tab").className = document.getElementById("register_tab").className.replace(/selected/g, "");
            document.getElementById("register-form").className = document.getElementById("register-form").className.replace(/selected/g, "");

        } else if ( this.id == "register_tab" && this.className.indexOf("selected") == -1 ) {

            document.getElementById("register_tab").className += "selected";
            document.getElementById("register-form").className += "selected";

            document.getElementById("login_tab").className = document.getElementById("login_tab").className.replace(/selected/g, "");
            document.getElementById("login-form").className = document.getElementById("login-form").className.replace(/selected/g, "");
        }
    }
};

for ( li of abas ) {
    li.addEventListener("click", alternateTab, false);
    li.addEventListener("keydown", alternateTab, false);
}
