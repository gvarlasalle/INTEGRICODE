// funcion para mostrar registrar y ocultar login
function mostrarRegistro() {
    $("#frmLogin").hide();
    $("#frmRecuperar").hide();
    $("#frmRegistro").show();
    $("#cambiarClave").hide();
    //poner el estado en false
    $(".glyphicon").data("activo", false);
}
// funcion para mostrar login y ocultar registrar
function mostrarLogin() {
    $("#frmLogin").show();
    $("#frmRegistro").hide();
    $("#frmRecuperar").hide();
    $("#cambiarClave").hide();
    //poner el estado en false
    $(".glyphicon").data("activo", false);
}
// funcion para mostrar recuperar y ocultar login
function mostrarRecuperar() {
    $("#frmLogin").hide();
    $("#frmRegistro").hide();
    $("#cambiarClave").hide();
    $("#frmRecuperar").show();
}

function mostrarCambiarClave() {
    $("#frmLogin").hide();
    $("#frmRegistro").hide();
    $("#frmRecuperar").hide();
    $("#cambiarClave").show();
}

//mostrar contraseña
$(document).ready(function () {
    $(".glyphicon").click(function () {
        var input = $(this).prev();
        if (input.attr("type") == "password") {
            input.attr("type", "text");
            $(this).data("activo", true);
            //cambar icono
            $(this).html('<i class="bi bi-eye-slash-fill"></i>');
        } else {
            input.attr("type", "password");
            $(this).data("activo", false);
            //cambar icono
            $(this).html('<i class="bi bi-eye-fill"></i>');
        }
    });
});

//loguear

$(document).ready(function () {
    $("#btnLogin").click(function () {
        var datos = $("#frmLogin").serialize();
        $.ajax({
            type: "POST",
            url: "backend/usuario.php?f=loguear",
            data: datos,
            beforeSend: function () {
                $("#btnLogin").html("Iniciando sesión...");
                //desabilitar boton
                $("#btnLogin").attr("disabled", true);
            },
            success: function (e) {
                $("#btnLogin").html("Iniciar Sesión");
                //habilitar boton
                $("#btnLogin").attr("disabled", false);

                if (e == 0) {
                    Swal.fire({
                        icon: "error",
                        title: "Ups!",
                        text: 'Contraseña incorrecta',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 1) {
                    window.location.href = "home/profesor";
                } else if (e == 2) {
                    window.location.href = "home/alumno";
                } else if (e == 3) {
                    Swal.fire({
                        icon: "error",
                        title: "Ups!",
                        text: 'Correo incorrecto o cuenta no existe',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 4) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'Todos los campos son obligatorios',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 5) {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: 'Esra cuenta esta desactivada',
                        confirmButtonText: `Aceptar`,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: e,
                        confirmButtonText: `Aceptar`,
                    });
                }
            },
        });
        return false;
    });
});


//no

//registrar
$(document).ready(function () {
    $("#btnRegistrarse").click(function () {
        var datos = $("#frmRegistro").serialize();
        $.ajax({
            type: "POST",
            url: "backend/usuario.php?f=registrar",
            data: datos,
            beforeSend: function () {
                $("#btnRegistrarse").html("Registrando...");
                //desabilitar boton
                $("#btnRegistrarse").attr("disabled", true);
            },
            success: function (e) {
                $("#btnRegistrarse").html("Registrarse");
                //habilitar boton
                $("#btnRegistrarse").attr("disabled", false);

                if (e == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Listo!",
                        text: 'Se ah registrado correctamente',
                        confirmButtonText: `Aceptar`,
                    });
                    $("#frmRegistro")[0].reset();
                    mostrarLogin();
                } else if (e == 2) {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: 'El correo ya esta registrado',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'Todos los campos son obligatorios',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 3) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'El formato del correo es invalido',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 4) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'La contraseña debe tener minimo 8 caracteres',
                        confirmButtonText: `Aceptar`,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: e,
                        confirmButtonText: `Aceptar`,
                    });
                }
            },
        });
        return false;
    });
});

//*recuperar contraseña
$(document).ready(function () {
    $("#btnBuscar").click(function () {
        var datos = $("#frmRecuperar").serialize();
        $.ajax({
            type: "POST",
            url: "backend/recuperar.php?f=generarCodigo",
            data: datos,
            beforeSend: function () {
                $("#btnBuscar").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                //desabilitar boton
                $("#btnBuscar").attr("disabled", true);
            },
            success: function (e) {
                $("#btnBuscar").html("Buscar");
                //habilitar boton
                $("#btnBuscar").attr("disabled", false);
                if (e == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "¡Listo!",
                        text: 'Se ah enviado un código de recuperacion a su correo',
                        confirmButtonText: `Aceptar`,
                    });
                    $("#frmRecuperar")[0].reset();
                    mostrarCambiarClave();
                } else if (e == 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'No tiene un token, recargar la pagina',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 2) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'Todos los campos son obligatorios',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 3) {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: 'El sistema no pudo enviar el correo, intentelo de nuevo',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 4) {
                    Swal.fire({
                        icon: "error",
                        title: "Ups!",
                        text: 'El correo no esta registrado',
                        confirmButtonText: `Aceptar`,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: e,
                        confirmButtonText: `Aceptar`,
                    });
                }
            },
        });
        return false;
    });
});

//*cambiar contraseña
$(document).ready(function () {
    $("#btnRecuperar").click(function () {
        var codigo = $("#rcodigo").val();
        var clave = $("#rnclave").val();
        var tk = $("#tk4").val();
        $.ajax({
            type: "POST",
            url: "backend/recuperar.php?f=recuperarClave",
            data: {
                codigo: codigo,
                clave: clave,
                tk: tk
            },
            beforeSend: function () {
                $("#btnRecuperar").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                //desabilitar boton
                $("#btnRecuperar").attr("disabled", true);
            },
            success: function (e) {
                $("#btnRecuperar").html("Cambiar");
                //habilitar boton
                $("#btnRecuperar").attr("disabled", false);
                if (e == 1) {
                    Swal.fire({
                        icon: "success",
                        title: "Listo!",
                        text: 'Su contraseña ah sido actualizada',
                        confirmButtonText: `Aceptar`,
                    });
                    document.getElementById("rcodigo").value = "";
                    document.getElementById("rnclave").value = "";
                    mostrarLogin();
                } else if (e == 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'No hay un token valido, recargar la pagina',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 2) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'Todos los campos son obligatorios',
                        confirmButtonText: `Aceptar`,
                    });
                } else if (e == 3) {
                    Swal.fire({
                        icon: "warning",
                        title: "Ups!",
                        text: 'Código incorrecto',
                        confirmButtonText: `Aceptar`,
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Lo sentimos!",
                        text: e,
                        confirmButtonText: `Aceptar`,
                    });
                }
            },
        });
        return false;
    });
});