function toggleMenu () {

    var menu = document.getElementById("top-menu");

    if ( menu.className.indexOf("open") != -1 ) {

        menu.className = menu.className.replace(/open/g, "");

    } else {

        menu.className += "open";

    }

}

document.addEventListener("click", function(event) {

    var menu = document.getElementById("top-menu");
    var target = event.target;

    if ( ! menu.contains(target) ) {

        if ( menu.className.indexOf("open") != -1 ) {

            toggleMenu();

        }

    }
});
