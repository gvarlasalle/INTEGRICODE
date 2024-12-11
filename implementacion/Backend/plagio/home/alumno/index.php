<?php
session_start();
require_once('../../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../../inc/.env');
$dotenv->safeLoad();

//comprobar si ya inicio sesion
if (isset($_COOKIE['sesion'])) {
    try {
        $decode = JWT::decode($_COOKIE['sesion'], new key($_ENV['KEY'], 'HS256'));
        if ($decode->data->rol == 1) {
            header('Location: ../profesor');
        }
    } catch (Exception $e) {
        //borrar cookie 
        setcookie('sesion', '', time() - 3600, '/');
        header("Location: ../../");
    }
} else {
    header("Location: ../../");
}

if (!isset($_SESSION['token'])) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
} else {
    $token = $_SESSION['token'];
}

$nombreEx = explode(" ", $decode->data->nombre);
$nombreEx = $nombreEx[0];
$apellidoEx = explode(" ", $decode->data->apellidos);
$apellidoEx = $apellidoEx[0];
$nombreCompleto = $nombreEx . " " . $apellidoEx;

$idAlumno = $decode->data->id;
$_SESSION['idAlumno'] = $idAlumno;

$imagen = "server/usuarios/" . $decode->data->id . $nombreEx . ".png";
if (!file_exists("../../" . $imagen)) {
    $imagen = "assets/images/sf.jpg";
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../../assets/libs/croppie/croppie.css">
    <link rel="stylesheet" href="../../assets/css/sidebar.css">
    <link rel="stylesheet" href="../../assets/css/tab.css">
    <link rel="stylesheet" href="../../assets/css/main.css">
</head>

<body id="body-pd">

    <header class="header" id="header">
        <div class="header_toggle">
            <i class='bx bx-menu' id="header-toggle"></i>
        </div>
        <div class="contenedor-header">
            <div class="pe-2">
                <span><?php echo $nombreCompleto; ?></span>
            </div>
            <div class="header_img">
                <img id="perfilFoto" src="<?php echo '../../' . $imagen; ?>" alt="logo">
            </div>
        </div>

    </header>
    <div class="l-navbar" id="nav-bar">
        <nav class="nav">
            <div>
                <a href="#" class="nav_logo">
                    <i class='bx bx-code-alt nav_logo-icon'></i>
                    <span class="nav_logo-name">
                        INTEGRICODE <br>
                        <span class="subtitle">Sistema Antiplagio</span>
                    </span>
                </a>
                <div class="nav_list">
                    <a href="?f=inicio" type="button" class="nav_link active">
                        <i class='bx bx-home-alt-2 nav_icon'></i>
                        <span class="nav_name">Inicio</span>
                    </a>
                    <a href="?f=cursos" type="button" class="nav_link">
                        <i class='bx bxs-graduation nav_icon'></i>
                        <span class="nav_name">Cursos</span>
                    </a>
                    <!--
                    <a href="?f=actividades" type="button" class="nav_link">
                        <i class='bx bx-book-alt nav_icon'></i>
                        <span class="nav_name">Actividades</span>
                    </a>
-->
                    <a href="?f=ajustes" type="button" class="nav_link">
                        <i class='bx bx-cog nav_icon'></i>
                        <span class="nav_name">Ajustes</span>
                    </a>
                </div>
            </div>
            <a href="../../backend/usuario.php?f=cerrarSesion" class="nav_link">
                <i class='bx bx-log-out nav_icon'></i>
                <span class="nav_name">Cerrar sesión</span>
            </a>
        </nav>
    </div>
    <!--Container Main start-->
    <div class="">
        <input type="hidden" id="tk" value="<?php echo $token; ?>">
        <div id="contenedorGeneral">

        </div>
    </div>
    <!--Boton para unirse al curso-->
    <div type="button" data-bs-toggle="modal" data-bs-target="#exampleModal" class="btn-add">
        <div>+</div>
    </div>


    <!-- Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Unirse a un curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Código de curso</span>
                                </div>
                                <div>
                                    <span class="text-muted">Pidele a tu profesor el código de curso e introducelo aquí</span>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="6" class="form-control text-uppercase" id="floatingInput" placeholder="Código de curso">
                                    <label for="floatingInput">Código de curso</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_unirse" class="btn bg-azul btn-primary">Unirme</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de editar -->
    <div class="modal fade" id="editarFoto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="croppie-editor">
                        <div id="croppie-field"></div>
                        <div class="mx-0 text-center">
                            <button class="btn btn-sm btn-light border border-dark rounded-0" id="rotate-left" type="button"><i class='bx bx-rotate-left'></i></button>
                            <button class="btn btn-sm btn-light border border-dark rounded-0" id="rotate-right" type="button"><i class='bx bx-rotate-right'></i></button>
                            <button class="btn btn-sm btn-primary rounded-0" id="upload-btn" type="button">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal cambiar clave -->
    <div class="modal fade" id="editarClave" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Editar Contraseña</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Contraseña nueva</span>
                                </div>
                                <div>
                                    <span class="text-muted">Ingresa la nueva contraseña</span>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="80" class="form-control" id="clave_nueva" placeholder="Contraseña nueva">
                                    <label for="clave_nueva">Contraseña nueva</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_edit_clave" class="btn bg-azul btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../../assets/js/sidebar.js"></script>
    <script src="../../assets/libs/croppie/croppie.js"></script>

    <script>
        function getQueryParam(param) {
            const urlParams = new URLSearchParams(window.location.search);
            return urlParams.get(param);
        }

        function mostrarCursos() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/cursos.php?f=listarCursos",
                data: {
                    tk: token
                },
                success: function(e) {
                    document.getElementById('contenedorGeneral').innerHTML = e;
                },
            });
        }

        function mostrarDetalleCursos() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/cursos.php?f=listarDetalleCursos",
                data: {
                    tk: token,
                    curso: getQueryParam('curso')
                },
                success: function(e) {
                    document.getElementById('contenedorGeneral').innerHTML = e;
                },
            });
        }

        function mostrarActividades() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividades.php?f=listarActividades",
                data: {
                    tk: token
                },
                success: function(e) {
                    document.getElementById('contenedorGeneral').innerHTML = e;
                },
            });
        }

        function mostrarInstrucciones() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividades.php?f=listarInstrucciones",
                data: {
                    tk: token,
                    id: getQueryParam('id')
                },
                success: function(e) {
                    document.getElementById('contenedorGeneral').innerHTML = e;
                },
            });
        }

        function mostrarAjustes() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/ajustes.php?f=listarAjustes",
                data: {
                    tk: token
                },
                success: function(e) {
                    document.getElementById('contenedorGeneral').innerHTML = e;
                },
            });
        }

        // Verificar si el parámetro 'f' tiene el valor 'detalle'
        if (getQueryParam('f') === 'detalle') {
            // Ejecuta tu función aquí
            mostrarDetalleCursos();
        } else if (getQueryParam('f') === 'actividades') {
            // Ejecuta tu función aquí
            mostrarActividades();
        } else if (getQueryParam('f') === 'instrucciones') {
            // Ejecuta tu función aquí
            mostrarInstrucciones();
        } else if (getQueryParam('f') === 'ajustes') {
            mostrarAjustes();
        } else {
            mostrarCursos();
        }

        //*funciones crud
        $('#btn_unirse').click(function() {
            var token = document.getElementById('tk').value;
            var cod = document.getElementById('floatingInput').value;
            $.ajax({
                type: "POST",
                url: "../../backend/cursosCrud.php?f=unirseCurso",
                data: {
                    tk: token,
                    cod: cod
                },
                beforeSend: function() {
                    $('#btn_unirse').prop('disabled', true);
                    $('#btn_unirse').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                },
                success: function(e) {
                    $('#btn_unirse').prop('disabled', false);
                    $('#btn_unirse').html('Unirme');
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        $('#floatingInput').val('');
                        $('#exampleModal').modal('hide');
                        mostrarCursos();
                    } else if (e.status == 'error') {
                        Swal.fire({
                            icon: "warning",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    }
                },
            });
        });

        //funcion para enviar codigo
        function enviarCodigo(link, id, ruta) {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividadesCrud.php?f=entregarActividad",
                data: {
                    tk: token,
                    link: link,
                    id: id,
                    ruta: ruta
                },
                beforeSend: function() {
                    $('#btn_entregar').prop('disabled', true);
                    $('#btn_entregar').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                },
                success: function(e) {
                    $('#btn_entregar').prop('disabled', false);
                    $('#btn_entregar').html('Entregar actividad');
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        $('#link').val('');
                        mostrarInstrucciones();
                    } else if (e.status == 'error') {
                        Swal.fire({
                            icon: "warning",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    }
                },
            });
        }

        //funcion para guardar el codigo extraido de la api
        $(document).ready(function() {
            //$('#btn_entregar').click(function() {
            $(document).on('click', '#btn_entregar', function() {
                var token = document.getElementById('tk').value;
                var url = document.getElementById('link').value;
                var id = getQueryParam('id');
                $.ajax({
                    type: "POST",
                    url: "../../backend/guardarCodigo.php",
                    data: {
                        tk: token,
                        url: url,
                        id: id
                    },
                    beforeSend: function() {
                        $('#btn_entregar').prop('disabled', true);
                        $('#btn_entregar').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(e) {
                        $('#btn_entregar').prop('disabled', false);
                        $('#btn_entregar').html('Entregar actividad');
                        if (e.status == 'success') {
                            enviarCodigo(url, id, e.ruta);
                        } else if (e.status == 'error') {
                            Swal.fire({
                                icon: "warning",
                                title: "Lo sentimos!",
                                text: e.message,
                                confirmButtonText: `Aceptar`,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Ups!",
                                text: e.message,
                                confirmButtonText: `Aceptar`,
                            });
                        }
                    },
                });
            });
        });

        //?funcion para cancelar entrega
        function cancelarEntrega(id) {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividadesCrud.php?f=cancelarEntrega",
                data: {
                    tk: token,
                    id: id
                },
                success: function(e) {
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        mostrarInstrucciones();
                    } else if (e.status == 'error') {
                        Swal.fire({
                            icon: "warning",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    }
                },
            });
        }

        //funcion para enviar codigo
        function actualizarCodigo(link, id, ruta) {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividadesCrud.php?f=actualizarCodigo",
                data: {
                    tk: token,
                    link: link,
                    id: id,
                    ruta: ruta
                },
                beforeSend: function() {
                    $('#btn_actualizar_act').prop('disabled', true);
                    $('#btn_actualizar_act').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                },
                success: function(e) {
                    $('#btn_actualizar_act').prop('disabled', false);
                    $('#btn_actualizar_act').html('Entregar actividad');
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        $('#link').val('');
                        mostrarInstrucciones();
                    } else if (e.status == 'error') {
                        Swal.fire({
                            icon: "warning",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Lo sentimos!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                    }
                },
            });
        }

        //funcion para guardar el codigo extraido de la api
        $(document).ready(function() {
            //$('#btn_entregar').click(function() {
            $(document).on('click', '#btn_actualizar_act', function() {
                var token = document.getElementById('tk').value;
                var url = document.getElementById('link_act').value;
                var id = document.getElementById('id_act_u').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/guardarCodigo.php",
                    data: {
                        tk: token,
                        url: url,
                        id: id
                    },
                    beforeSend: function() {
                        $('#btn_actualizar_act').prop('disabled', true);
                        $('#btn_actualizar_act').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(e) {
                        $('#btn_actualizar_act').prop('disabled', false);
                        $('#btn_actualizar_act').html('Entregar actividad');
                        if (e.status == 'success') {
                            actualizarCodigo(url, id, e.ruta);
                        } else if (e.status == 'error') {
                            Swal.fire({
                                icon: "warning",
                                title: "Lo sentimos!",
                                text: e.message,
                                confirmButtonText: `Aceptar`,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Lo sentimos!",
                                text: e.message,
                                confirmButtonText: `Aceptar`,
                            });
                        }
                    },
                });
            });
        });
        //*funciones generales
        //funcion para vista previa de perfil

        var $croppie = new Croppie($('#croppie-field')[0], {
            enableExif: true,
            enableResize: false,
            enableZoom: true,
            boundary: {
                width: '100%',
                height: 200
            },
            viewport: {
                height: 110,
                width: 110,
                type: 'circle',
            },
            enableOrientation: true
        })



        $(document).ready(function() {
            var img_name;
            // console.log($croppie)
            $(document).on('change', '#upload', function(e) {
                var reader = new FileReader();
                img_name = e.target.files[0].name;
                reader.onload = function(e) {
                    //abrirmos el modal
                    $('#editarFoto').modal('show');
                    $('#editarFoto').on('shown.bs.modal', function() {
                        $croppie.bind({
                            url: e.target.result
                        });
                    })

                }
                reader.readAsDataURL(this.files[0]);
            })


            $('#rotate-left').click(function() {
                $croppie.rotate(90);
            })
            $('#rotate-right').click(function() {
                $croppie.rotate(-90);

            })
            $('#upload-btn').click(function() {
                $croppie.result({
                    type: 'base64',
                    format: 'png'
                }).then((imgBase64) => {
                    $.ajax({
                        url: '../../backend/ajustes.php?f=actualizarFoto',
                        method: 'POST',
                        data: {
                            'img': imgBase64,
                            'fname': img_name,
                            'tk': $('#tk').val()
                        },
                        dataType: 'json',
                        error: err => {
                            console.error(err)
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    icon: "success",
                                    title: "¡Listo!",
                                    text: 'Su foto de perfil se ha actualizado correctamente',
                                    confirmButtonText: `Aceptar`,
                                });
                                $('#editarFoto').modal('hide');
                                $('#perfil-edit').attr('src', imgBase64);
                                $('#perfilFoto').attr('src', imgBase64);

                            } else {
                                console.error(response);
                                Swal.fire({
                                    icon: "error",
                                    title: "Lo sentimos!",
                                    text: 'Ocurrio un error inesperado, reintentar',
                                    confirmButtonText: `Aceptar`,
                                });
                            }
                        }
                    })
                })
            })
        })

        //? funcion para mostrar modal cambiar clave
        function cambiarClave() {
            $('#editarClave').modal('show');
        }

        //?funcion para cambiar la contraseña
        $(document).ready(function() {
            $('#btn_edit_clave').click(function() {
                var token = document.getElementById('tk').value;
                var clave = document.getElementById('clave_nueva').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/usuario.php?f=cambiarClaveAlumno",
                    data: {
                        'clave': clave,
                        'tk': token
                    },
                    dataType: 'json',
                    error: err => {
                        console.error(err)
                    },
                    beforeSend: function() {
                        $('#btn_edit_clave').prop('disabled', true);
                        $('#btn_edit_clave').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(response) {
                        $('#btn_edit_clave').prop('disabled', false);
                        $('#btn_edit_clave').html('Guardar');
                        if (response.status == 'success') {
                            Swal.fire({
                                icon: "success",
                                title: "¡Listo!",
                                text: response.message,
                                confirmButtonText: `Aceptar`,
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "../../";
                                }
                            });
                        } else if (response.status == 'error') {
                            Swal.fire({
                                icon: "warning",
                                title: "Lo sentimos!",
                                text: response.message,
                                confirmButtonText: `Aceptar`,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Lo sentimos!",
                                text: response.message,
                                confirmButtonText: `Aceptar`,
                            });
                        }
                    }
                })
            });
        });

        //? funcion para eliminar cuenta
        function eliminarCuenta() {
            Swal.fire({
                icon: "warning",
                title: "¿Deseas eliminar tu cuenta?",
                text: "Esta acción no se puede deshacer",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: `Si`,
                cancelButtonText: `No`,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "../../backend/usuario.php?f=eliminarCuentaAlumno",
                        data: {
                            'tk': $('#tk').val()
                        },
                        dataType: 'json',
                        error: err => {
                            console.error(err)
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    icon: "success",
                                    title: "¡Listo!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "../../";
                                    }
                                });
                            } else if (response.status == 'error') {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Lo sentimos!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Lo sentimos!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                });
                            }
                        }
                    })
                }
            });
        }

        //?funcion para salir del curso
        function salirDelCurso() {
            Swal.fire({
                icon: "warning",
                title: "¿Deseas salir del curso?",
                text: "Por favor conrfirmar que deseas salir del curso",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: `Si, salir`,
                cancelButtonText: `No`,
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        type: "POST",
                        url: "../../backend/cursosCrud.php?f=salirCurso",
                        data: {
                            'tk': $('#tk').val(),
                            'curso': getQueryParam('curso')
                        },
                        dataType: 'json',
                        error: err => {
                            console.error(err)
                        },
                        success: function(response) {
                            if (response.status == 'success') {
                                Swal.fire({
                                    icon: "success",
                                    title: "¡Listo!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "?f=inicio";
                                    }
                                });
                            } else if (response.status == 'error') {
                                Swal.fire({
                                    icon: "warning",
                                    title: "Lo sentimos!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                });
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Lo sentimos!",
                                    text: response.message,
                                    confirmButtonText: `Aceptar`,
                                });
                            }
                        }
                    })
                }
            });
        }
        //fin
    </script>
</body>

</html>