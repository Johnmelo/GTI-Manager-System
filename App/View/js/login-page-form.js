var abas = document.getElementById("botoes-abas").children;

for ( li of abas ) {

    li.addEventListener("click", function(event) {

        if ( this.id == "login_tab" && this.className.indexOf("selected") == -1 ) {

            document.getElementById("login_tab").className += "selected";
            document.getElementById("login-form-wrapper").className += "selected";

            document.getElementById("register_tab").className = document.getElementById("register_tab").className.replace(/selected/g, "");
            document.getElementById("register-form-wrapper").className = document.getElementById("register-form-wrapper").className.replace(/selected/g, "");

        } else if ( this.id == "register_tab" && this.className.indexOf("selected") == -1 ) {

            document.getElementById("register_tab").className += "selected";
            document.getElementById("register-form-wrapper").className += "selected";

            document.getElementById("login_tab").className = document.getElementById("login_tab").className.replace(/selected/g, "");
            document.getElementById("login-form-wrapper").className = document.getElementById("login-form-wrapper").className.replace(/selected/g, "");
        }
    });
}
