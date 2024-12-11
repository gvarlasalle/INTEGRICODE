document.addEventListener("DOMContentLoaded", function (event) {

    const showNavbar = (toggleId, navId, bodyId, headerId) => {
        const toggle = document.getElementById(toggleId),
            nav = document.getElementById(navId),
            bodypd = document.getElementById(bodyId),
            headerpd = document.getElementById(headerId)

        // Validate that all variables exist
        if (toggle && nav && bodypd && headerpd) {
            toggle.addEventListener('click', () => {
                // show navbar
                nav.classList.toggle('mostrar')
                // change icon
                toggle.classList.toggle('bx-x')
                // add padding to body
                bodypd.classList.toggle('body-pd')
                // add padding to header
                headerpd.classList.toggle('body-pd')
            })
        }
    }

    showNavbar('header-toggle', 'nav-bar', 'body-pd', 'header')

    /*===== LINK ACTIVE =====*/
    const linkColor = document.querySelectorAll('.nav_link')

    // Obtener los parámetros de la URL (incluyendo 'f')
    const currentQuery = new URLSearchParams(window.location.search).get('f');

    // Recuperar el último seleccionado de localStorage
    let lastSelectedHref = localStorage.getItem('lastSelectedHref');

    // Variable para identificar si hubo coincidencia
    let foundMatch = false;

    // Recorremos todos los enlaces con el selector 'linkColor'
    linkColor.forEach(link => {
        // Quitar la clase 'active' de todos los enlaces
        link.classList.remove('active');

        // Verificar si el 'href' del enlace contiene el valor de la query 'f'
        const linkQuery = new URLSearchParams(link.getAttribute('href').split('?')[1]).get('f');

        if (linkQuery === currentQuery) {
            // Agregar la clase 'active' si la query coincide
            link.classList.add('active');
            foundMatch = true;
            // Guardar el enlace seleccionado en localStorage
            localStorage.setItem('lastSelectedHref', link.getAttribute('href'));
        }
    });

    // Si no hubo coincidencia, seleccionar el último seleccionado almacenado
    if (!foundMatch && lastSelectedHref) {
        linkColor.forEach(link => {
            if (link.getAttribute('href') === lastSelectedHref) {
                link.classList.add('active');
            }
        });
    }


    // Your code to run since DOM is loaded and ready
});