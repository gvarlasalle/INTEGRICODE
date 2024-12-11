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
        if ($decode->data->rol == 2) {
            header('Location: ../alumno');
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

$idProfesor = $decode->data->id;
$_SESSION['idProfesor'] = $idProfesor;

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
    <link rel="stylesheet" href="../../assets/css/dropdown.css">
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


    <!-- Modal agregar curso -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Crear un curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Crear Curso</span>
                                </div>
                                <div>
                                    <span class="text-muted">Completa todos los campos para crear un curso</span>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="30" class="form-control text-capitalize" id="cursoName" placeholder="Nombre del curso">
                                    <label for="cursoName">Nombre del curso</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="15" class="form-control text-uppercase" id="aulaName" placeholder="Aula">
                                    <label for="aulaName">Aula</label>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_add_curso" class="btn bg-azul btn-primary">Crear</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal agregar actividad -->
    <div class="modal fade" id="addActividad" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Crear una actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Crear Actividad</span>
                                </div>
                                <div>
                                    <span class="text-muted">Completa todos los campos para crear una actividad</span>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="80" class="form-control" id="titleAct" placeholder="Título">
                                    <label for="titleAct">Título</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <textarea maxlength="250" type="text" class="form-control" id="descripcionAct" placeholder="Descripción"></textarea>
                                    <label for="descripcionAct">Descripción</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="datetime-local" min="" class="form-control arial" id="fechaAct" placeholder="Fecha de entrega">
                                    <label for="fechaAct">Fecha de entrega</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_add_actividad" class="btn bg-azul btn-primary">Crear</button>
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

    <!-- Modal de editar -->
    <div class="modal fade" id="editarFotoCurso" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Editar Foto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div>
                        <div id="croppie-rectangle-field"></div>
                        <div class="mx-0 text-center">
                            <button class="btn btn-sm btn-primary rounded-0" id="upload-btn-rectangle" type="button">Guardar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar curso -->
    <div class="modal fade" id="editarCurso" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Editar Curso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Editar Curso</span>
                                </div>
                                <div>
                                    <span class="text-muted">Edita los campos que creas comvenientes</span>
                                </div>
                                <input type="hidden" id="eidcurso">
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="30" class="form-control text-capitalize" id="ecursoName" placeholder="Nombre del curso">
                                    <label for="ecursoName">Nombre del curso</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="text" maxlength="15" class="form-control text-uppercase" id="eaulaName" placeholder="Aula">
                                    <label for="eaulaName">Aula</label>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_edit_curso" class="btn bg-azul btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar actividad -->
    <div class="modal fade" id="editarActividad" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Editar Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Editar Actividad</span>
                                </div>
                                <div>
                                    <span class="text-muted">Edita los campos que creas comvenientes</span>
                                </div>
                                <input type="hidden" id="eidactividad">
                                <div class="form-floating mt-3">
                                    <input type="text" class="form-control" id="etitleAct" placeholder="Título">
                                    <label for="etitleAct">Título</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <textarea type="text" class="form-control" id="edescripcionAct" placeholder="Descripción"></textarea>
                                    <label for="edescripcionAct">Descripción</label>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="datetime-local" min="" class="form-control arial" id="efechaAct" placeholder="Fecha de entrega">
                                    <label for="efechaAct">Fecha de entrega</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_edit_actividad" class="btn bg-azul btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar actividad -->
    <div class="modal fade" id="showChart" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">gráfico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Gráfico</span>
                                </div>
                                <div id='chart_div'></div>
                                <input type="text" id="imagen_chart">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_grafico" class="btn bg-azul btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal editar Noticia -->
    <div class="modal fade" id="editarNoticia" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="exampleModalLabel">Editar Noticia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <span class="fw-bold">Editar Noticia</span>
                                </div>
                                <div>
                                    <span class="text-muted">Completa el campo noticia</span>
                                </div>
                                <input type="hidden" id="eidnoticia">
                                <div class="form-floating mt-3">
                                    <textarea type="text" class="form-control" id="eNoticia" placeholder="Noticia"></textarea>
                                    <label for="eNoticia">Noticia</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_actualizar_noticia" class="btn bg-azul btn-primary">Actualizar</button>
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

    <!-- Ingresar calificación -->
    <div class="modal fade" id="ponerNota" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Calificar Actividad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <input type="hidden" id="idnota">
                                <div>
                                    <span class="fw-bold">Ingrese calificación</span>
                                </div>
                                <div>
                                    <span class="text-muted">Ingrese calificación de 0 a 20</span>
                                </div>
                                <div class="form-floating mt-3">
                                    <input type="number" min="0" max="20" class="form-control" oninput="validarCalificacion(this)" id="calificacion" placeholder="Calificación">
                                    <label for="calificacion">Calificación</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" id="btn_calificar" class="btn bg-azul btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://unpkg.com/pdfobject"></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>
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
                url: "../../backend/cursos.php?f=listarCursosProfesor",
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
            var curso = getQueryParam('curso');
            $.ajax({
                type: "POST",
                url: "../../backend/cursos.php?f=listarDetalleCursoProfesor",
                data: {
                    tk: token,
                    curso: curso
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

        function revisarActividades() {
            var token = document.getElementById('tk').value;
            $.ajax({
                type: "POST",
                url: "../../backend/actividades.php?f=revisarActividades",
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
        } else if (getQueryParam('f') === 'revisar') {
            // Ejecuta tu función aquí
            revisarActividades();
        } else if (getQueryParam('f') === 'ajustes') {
            mostrarAjustes();
        } else if (getQueryParam('f') === 'reporte') {
            mostrarReporte();
        } else if (getQueryParam('f') === 'gemini') {
            var id = getQueryParam('id');
            geminiPDF(id);
        } else {
            mostrarCursos();
        }

        //*funciones crud
        $('#btn_add_curso').click(function() {
            var token = document.getElementById('tk').value;
            var nombre = document.getElementById('cursoName').value;
            var aula = document.getElementById('aulaName').value;
            $.ajax({
                type: "POST",
                url: "../../backend/cursosCrud.php?f=createCurso",
                data: {
                    tk: token,
                    nombre: nombre,
                    aula: aula
                },
                beforeSend: function() {
                    $('#btn_add_curso').prop('disabled', true);
                    $('#btn_add_curso').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                },
                success: function(e) {
                    $('#btn_add_curso').prop('disabled', false);
                    $('#btn_add_curso').html('Crear');
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        $('#cursoName').val('');
                        $('#aulaName').val('');
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

        document.addEventListener('DOMContentLoaded', function() {
            var fechaActInput = document.getElementById('fechaAct');
            var fecha2 = document.getElementById('efechaAct');
            var fechaActual = new Date();

            // Formato de fecha y hora en formato ISO, requerido por el input (YYYY-MM-DDTHH:MM)
            var año = fechaActual.getFullYear();
            var mes = String(fechaActual.getMonth() + 1).padStart(2, '0');
            var dia = String(fechaActual.getDate()).padStart(2, '0');
            var horas = String(fechaActual.getHours()).padStart(2, '0');
            var minutos = String(fechaActual.getMinutes()).padStart(2, '0');

            fechaActInput.min = `${año}-${mes}-${dia}T${horas}:${minutos}`;
            fecha2.min = `${año}-${mes}-${dia}T${horas}:${minutos}`;
        });

        $('#btn_add_actividad').click(function() {
            var token = document.getElementById('tk').value;
            var titulo = document.getElementById('titleAct').value;
            var descripcion = document.getElementById('descripcionAct').value;
            var fechaf = document.getElementById('fechaAct').value;

            // Validación de la fecha y hora
            var fechaIngresada = new Date(fechaf);
            var fechaActual = new Date();

            if (fechaIngresada <= fechaActual) {
                Swal.fire({
                    icon: "warning",
                    title: "Fecha no válida",
                    text: "La fecha y hora de la actividad no pueden ser anteriores a la fecha y hora actual.",
                    confirmButtonText: `Aceptar`,
                });
                return; // Detiene el envío del formulario
            }

            $.ajax({
                type: "POST",
                url: "../../backend/actividadesCrud.php?f=createActividad",
                data: {
                    tk: token,
                    titulo: titulo,
                    descripcion: descripcion,
                    fechaf: fechaf,
                    curso: getQueryParam('curso')
                },
                beforeSend: function() {
                    $('#btn_add_actividad').prop('disabled', true);
                    $('#btn_add_actividad').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                },
                success: function(e) {
                    $('#btn_add_actividad').prop('disabled', false);
                    $('#btn_add_actividad').html('Crear');
                    if (e.status == 'success') {
                        Swal.fire({
                            icon: "success",
                            title: "¡Listo!",
                            text: e.message,
                            confirmButtonText: `Aceptar`,
                        });
                        $('#titleAct').val('');
                        $('#descripcionAct').val('');
                        $('#fechaAct').val('');
                        $('#addActividad').modal('hide');
                        mostrarDetalleCursos();
                        $('#tab2').prop('checked', true);
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

        //?funcion insertar noticia
        $(document).ready(function() {
            $(document).on('click', '#btn_add_noticia', function() {
                var token = document.getElementById('tk').value;
                var curso = getQueryParam('curso');
                var noticia = document.getElementById('txtNoticia').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/cursosCrud.php?f=insertNoticia",
                    data: {
                        tk: token,
                        curso: curso,
                        noticia: noticia
                    },
                    beforeSend: function() {
                        $('#btn_add_noticia').prop('disabled', true);
                        $('#btn_add_noticia').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(e) {
                        $('#btn_add_noticia').prop('disabled', false);
                        $('#btn_add_noticia').html('Publicar');
                        if (e.status == 'success') {
                            Swal.fire({
                                icon: "success",
                                title: "¡Listo!",
                                text: 'La noticia se ha publicado correctamente',
                                confirmButtonText: `Aceptar`,
                            });
                            $('#txtNoticia').val('');
                            mostrarDetalleCursos();
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

        function eliminarCurso(id) {
            Swal.fire({
                icon: "warning",
                title: "¿Deseas eliminar este curso?",
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
                        url: "../../backend/cursosCrud.php?f=eliminarCurso",
                        data: {
                            'id': id,
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
                                    text: 'El curso se ha eliminado correctamente',
                                    confirmButtonText: `Aceptar`,
                                });
                                mostrarCursos();
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
                                    text: e.message,
                                    confirmButtonText: `Aceptar`,
                                });
                            }
                        }
                    })
                }
            });
        }

        function eliminarActividad(id) {
            Swal.fire({
                icon: "warning",
                title: "¿Deseas eliminar esta actividad?",
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
                        url: "../../backend/actividadesCrud.php?f=eliminarActividad",
                        data: {
                            'id': id,
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
                                    text: 'La actividad ah sido eliminada correctamente',
                                    confirmButtonText: `Aceptar`,
                                });
                                mostrarDetalleCursos();
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

        function eliminarNoticia(id) {
            Swal.fire({
                icon: "warning",
                title: "¿Deseas eliminar esta noticia?",
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
                        url: "../../backend/cursosCrud.php?f=eliminarNoticia",
                        data: {
                            'id': id,
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
                                });
                                mostrarDetalleCursos();
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

        function editarCurso(editar) {
            var d = editar.split("||");
            var id = d[0];
            var nombre = d[1];
            var aula = d[2];
            document.getElementById('eidcurso').value = id;
            document.getElementById('ecursoName').value = nombre;
            document.getElementById('eaulaName').value = aula;
            $('#editarCurso').modal('show');
        }

        function editarActividad(editar) {
            var d = editar.split("||");
            var id = d[0];
            var titulo = d[1];
            var descripcion = d[2];
            var fechaf = d[3];
            document.getElementById('eidactividad').value = id;
            document.getElementById('etitleAct').value = titulo;
            document.getElementById('edescripcionAct').value = descripcion;
            document.getElementById('efechaAct').value = fechaf;
            $('#editarActividad').modal('show');
        }

        function editarNoticia(editar) {
            var d = editar.split("||");
            var id = d[0];
            var noticia = d[1];
            document.getElementById('eidnoticia').value = id;
            document.getElementById('eNoticia').value = noticia;
            $('#editarNoticia').modal('show');
        }

        $(document).ready(function() {
            $("#btn_edit_actividad").click(function() {
                var id = document.getElementById('eidactividad').value;
                var titulo = document.getElementById('etitleAct').value;
                var descripcion = document.getElementById('edescripcionAct').value;
                var fechaf = document.getElementById('efechaAct').value;

                // Validación de la fecha y hora
                var fechaIngresada = new Date(fechaf);
                var fechaActual = new Date();

                if (fechaIngresada <= fechaActual) {
                    Swal.fire({
                        icon: "warning",
                        title: "Fecha no válida",
                        text: "La fecha y hora de la actividad no pueden ser anteriores a la fecha y hora actual.",
                        confirmButtonText: `Aceptar`,
                    });
                    return; // Detiene el envío del formulario
                }

                $.ajax({
                    type: "POST",
                    url: "../../backend/actividadesCrud.php?f=editarActividad",
                    data: {
                        'id': id,
                        'titulo': titulo,
                        'descripcion': descripcion,
                        'fechaf': fechaf,
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
                                text: 'La actividad se ha actualizado correctamente',
                                confirmButtonText: `Aceptar`,
                            });
                            $('#editarActividad').modal('hide');
                            mostrarDetalleCursos();
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

        $(document).ready(function() {
            $('#btn_edit_curso').click(function() {
                var id = document.getElementById('eidcurso').value;
                var nombre = document.getElementById('ecursoName').value;
                var aula = document.getElementById('eaulaName').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/cursosCrud.php?f=editarCurso",
                    data: {
                        'id': id,
                        'nombre': nombre,
                        'aula': aula,
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
                                text: 'El curso se ha actualizado correctamente',
                                confirmButtonText: `Aceptar`,
                            });
                            $('#editarCurso').modal('hide');
                            mostrarCursos();
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

        $(document).ready(function() {
            $('#btn_actualizar_noticia').click(function() {
                var id = document.getElementById('eidnoticia').value;
                var noticia = document.getElementById('eNoticia').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/cursosCrud.php?f=editarNoticia",
                    data: {
                        'id': id,
                        'noticia': noticia,
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
                                text: 'La noticia se ha actualizado correctamente',
                                confirmButtonText: `Aceptar`,
                            });
                            $('#editarNoticia').modal('hide');
                            mostrarDetalleCursos();
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

        //* funcion llamar reporte por gemini

        $(document).ready(function() {
            $(document).on('click', '#btn_gemini', function() {
                var token = document.getElementById('tk').value;
                var id = getQueryParam('id');
                $.ajax({
                    type: "POST",
                    url: "../../backend/algoritmos/gemini.php",
                    data: {
                        tk: token,
                        id: id
                    },
                    beforeSend: function() {
                        $('#btn_gemini').prop('disabled', true);
                        $('#btn_gemini').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
                    },
                    success: function(e) {
                        $('#btn_gemini').prop('disabled', false);
                        $('#btn_gemini').html("<i class='bx bxs-bot fs-1'></i>");
                        if (e.status == 'success') {

                            console.log(e.data);
                            localStorage.setItem('gemini', e.data);
                            geminiPDF(id);

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

        //*crear un iframe con el reporte
        function geminiPDF(id) {
            //var id = getQueryParam('id');
            var gemini = localStorage.getItem('gemini');
            if(gemini == null){
                Swal.fire({
                    icon: "warning",
                    title: "Lo sentimos!",
                    text: "No hay códigos subidos o gemini no genero una respuesta",
                    confirmButtonText: `Aceptar`,
                }).then(() => {
                    // Retrocede luego de que el usuario presione "Aceptar"
                    window.history.back();
                })
                return
            }
            $.ajax({
                type: "POST",
                url: "../../backend/reports/template/geminipdf.php",
                data: {
                    id: id,
                    gemini: gemini
                },
                dataType: 'json',
                success: function(data2) {
                    var byteCharacters = atob(data2.pdf);
                    var byteNumbers = new Array(byteCharacters.length);
                    for (var i = 0; i < byteCharacters.length; i++) {
                        byteNumbers[i] = byteCharacters.charCodeAt(i);
                    }
                    var byteArray = new Uint8Array(byteNumbers);

                    // Crear un blob a partir del Uint8Array
                    var blob = new Blob([byteArray], {
                        type: 'application/pdf',
                        name: 'reporte'
                    });
                    var pdfUrl = URL.createObjectURL(blob);

                    var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                    if (isMobile) {
                        // Crear un enlace de descarga para dispositivos móviles
                        Swal.fire({
                            title: "Descargar Reporte",
                            text: "¿Desea descargar el reporte?",
                            showDenyButton: true,
                            showCancelButton: false,
                            confirmButtonText: "Descargar",
                            denyButtonText: "Cancelar",
                        }).then((result) => {
                            /* Read more about isConfirmed, isDenied below */
                            if (result.isConfirmed) {
                                var downloadLink = document.createElement('a');
                                downloadLink.href = pdfUrl;
                                downloadLink.download = 'reporte_gemini.pdf'; // Nombre del archivo
                                document.body.appendChild(downloadLink);
                                downloadLink.click();
                                document.body.removeChild(downloadLink);
                                window.history.back();
                                Swal.fire("Descargado!", "", "success");
                            }
                        });
                    } else {
                        // Mostrar en iframe para dispositivos de escritorio
                        var iframe = document.createElement('iframe');
                        iframe.src = pdfUrl;
                        iframe.width = '100%';
                        iframe.classList.add('height-100');
                        iframe.style.border = 'none';

                        // Limpiar el contenedor antes de agregar el nuevo iframe
                        $("#contenedorGeneral").empty().append(iframe);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error:', error);
                    revisarActividades();
                    Swal.fire({
                        title: 'Lo sentimos!',
                        text: 'No se puede generar pdf, porque no hay códigos subidos.',
                        icon: 'warning',
                        confirmButtonText: 'Aceptar',
                    })
                }

            });
        }

        //*funcion para vrificar la similitud de las variables

        $(document).ready(function() {
            $(document).on('click', '#btn_verificar', function() {
                var token = document.getElementById('tk').value;
                var id = getQueryParam('id');
                var algoritmo = document.getElementById('comboAlgoritmos').value;
                if (algoritmo == 0) {
                    Swal.fire({
                        icon: "warning",
                        title: "Lo sentimos!",
                        text: "Por favor seleccione un algoritmo",
                        confirmButtonText: `Aceptar`,
                    });
                    return;
                } else {
                    if (algoritmo == 1) {
                        url = "../../backend/algoritmos/variables.php";
                    } else if (algoritmo == 2) {
                        url = "../../backend/algoritmos/levenshtein.php";
                    } else if (algoritmo == 3) {
                        url = "../../backend/algoritmos/coseno.php";
                    }
                }
                $.ajax({
                    type: "POST",
                    url: url,
                    data: {
                        tk: token,
                        id: id
                    },
                    beforeSend: function() {
                        $('#btn_verificar').prop('disabled', true);
                        $('#btn_verificar').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(e) {
                        $('#btn_verificar').prop('disabled', false);
                        $('#btn_verificar').html('Analizar códigos');
                        if (e.status == 'success') {
                            Swal.fire({
                                icon: "success",
                                title: "¡Listo!",
                                text: e.message,
                                confirmButtonText: `Aceptar`,
                            });
                            revisarActividades();
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
        });

        var $croppieRectangle = new Croppie($('#croppie-rectangle-field')[0], {
            enableExif: true,
            enableResize: false,
            enableZoom: true,
            boundary: {
                width: '100%',
                height: 300 // Puedes ajustar según el diseño
            },
            viewport: {
                width: '100%', // Ancho del rectángulo
                height: 200, // Altura del rectángulo
                type: 'square', // Croppie no tiene "rectángulo" explícito, pero 'square' con dimensiones personalizadas funciona
            },
            enableOrientation: true
        });

        $(document).ready(function() {
            var img_name;
            $(document).on('change', '#fotoCurso', function(e) {
                var reader = new FileReader();
                img_name = e.target.files[0].name;
                reader.onload = function(e) {
                    //abrirmos el modal
                    $('#editarFotoCurso').modal('show');
                    $('#editarFotoCurso').on('shown.bs.modal', function() {
                        $croppieRectangle.bind({
                            url: e.target.result
                        });
                    })

                }
                reader.readAsDataURL(this.files[0]);
            })

            $('#upload-btn-rectangle').click(function() {
                $croppieRectangle.result({
                    type: 'base64',
                    format: 'png'
                }).then((imgBase64) => {
                    $.ajax({
                        url: '../../backend/ajustes.php?f=actualizarFotoCurso',
                        method: 'POST',
                        data: {
                            'img': imgBase64,
                            'fname': img_name,
                            'cod': getQueryParam('cod'),
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
                                    text: 'La imagen del curso se ha actualizado correctamente',
                                    confirmButtonText: `Aceptar`,
                                });
                                $('#editarFotoCurso').modal('hide');
                                $('#cursoImagen').attr('src', imgBase64);

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
        });

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

        //fin

        document.addEventListener('input', function(event) {
            if (event.target.tagName.toLowerCase() === 'textarea') {
                autoExpand(event.target);
            }
        }, false);

        document.addEventListener('focus', function(event) {
            if (event.target.classList.contains('form-control') && event.target.tagName.toLowerCase() === 'textarea') {
                autoExpand(event.target);
            }
        }, true);

        function setContentAndResize(textarea, content) {
            textarea.value = content; // Establece el contenido dinámicamente
            autoExpand(textarea); // Ajusta el tamaño del textarea después de establecer el contenido
        }

        function autoExpand(textarea) {
            textarea.style.height = 'auto'; // Reinicia la altura para recalcular
            textarea.style.height = (textarea.scrollHeight + 5) + 'px'; // Ajusta la altura a la altura de su contenido
        }

        function updateVH() {
            let vh = window.innerHeight * 0.01; // Calcula 1% del viewport real
            document.documentElement.style.setProperty('--vh', `${vh}px`);
        }

        // Ejecutar la función cuando la página se carga o cambia el tamaño de la ventana
        window.addEventListener('load', updateVH);
        window.addEventListener('resize', updateVH);

        //*mostrar reporte
        function mostrarReporte() {
            $.ajax({
                type: "POST",
                url: "../../backend/reports/model/apis.php?f=data",
                data: {
                    id: getQueryParam('id')
                },
                dataType: 'json',
                error: err => {
                    console.error(err);
                },
                success: function(response) {
                    if (response.length === 0) {
                        /*
                        var div = document.createElement('div');
                        div.innerHTML = 'Error al renderizar el gráfico en el reporte o no se envió ninguna tarea en esta actividad';
                        div.style.marginTop = '100px';
                        $('#contenedorGeneral').empty().append(div);
                        */
                        //retroceder navegacion
                        revisarActividades();
                        Swal.fire({
                            title: 'Lo sentimos!',
                            text: 'No se puede generar pdf, porque no hay códigos subidos.',
                            icon: 'warning',
                            confirmButtonText: 'Aceptar',
                        }).then(() => {
                            // Retrocede después de que el usuario presione "Aceptar"
                            window.history.back();
                        });

                        return;
                    }

                    var jsonData = response;

                    google.charts.load('current', {
                        packages: ['corechart', 'bar']
                    });
                    google.charts.setOnLoadCallback(drawAnnotations);

                    function drawAnnotations() {
                        var data = new google.visualization.DataTable();

                        // Definir las columnas del gráfico
                        data.addColumn('string', 'Alumno'); // Columna de Alumno
                        data.addColumn('number', 'Variables'); // Columna Variables
                        data.addColumn({
                            type: 'string',
                            role: 'annotation'
                        }); // Anotación para Variables
                        data.addColumn('number', 'Levenshtein'); // Columna Levenshtein
                        data.addColumn({
                            type: 'string',
                            role: 'annotation'
                        }); // Anotación para Levenshtein
                        data.addColumn('number', 'Coseno'); // Columna Coseno
                        data.addColumn({
                            type: 'string',
                            role: 'annotation'
                        }); // Anotación para Coseno

                        // Llenar los datos con los valores de la respuesta
                        jsonData.forEach(item => {
                            data.addRow([item.nombre,
                                item.variables, item.variables.toFixed(1),
                                item.levenshtein, item.levenshtein.toFixed(1),
                                item.coseno, item.coseno.toFixed(1)
                            ]);
                        });

                        var options = {
                            title: 'Similitud de Código por Alumno',
                            width: 900,
                            height: 400,
                            annotations: {
                                alwaysOutside: true,
                                textStyle: {
                                    fontSize: 14,
                                    color: '#000',
                                    auraColor: 'none'
                                }
                            },
                            hAxis: {
                                title: 'Alumnos',
                            },
                            vAxis: {
                                title: 'Porcentaje de Similitud',
                                viewWindow: {
                                    min: 0,
                                    max: 100
                                }
                            },
                            isStacked: false, // No apilar las barras
                            colors: ['#fe6383', '#fda725', '#4ab151'], // Colores para cada métrica
                            bar: {
                                groupWidth: '75%'
                            },
                        };

                        var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));
                        chart.draw(data, options);
                        document.getElementById('imagen_chart').value = chart.getImageURI();

                        cargarPdf();
                    }
                }
            });
        }



        function cargarPdf() {
            var imagen = $("#imagen_chart").val(); // Obtiene el valor del input (ruta o URL de la imagen)
            let id = getQueryParam('id');
            let url = "../../backend/reports/template/analisisPlagio?id=" + id;

            // Verifica que haya una ruta de imagen
            if (imagen !== '') { // Agrega la ruta de la imagen

                // Realiza la solicitud POST para enviar la ruta de la imagen
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        imagen: imagen
                    }, // Enviar la imagen como parte de un objeto
                    dataType: 'json', // Esperar un JSON de respuesta
                    success: function(data) {
                        // Decodificar el PDF desde base64
                        var byteCharacters = atob(data.pdf);
                        var byteNumbers = new Array(byteCharacters.length);
                        for (var i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        var byteArray = new Uint8Array(byteNumbers);

                        // Crear un blob a partir del Uint8Array
                        var blob = new Blob([byteArray], {
                            type: 'application/pdf',
                            name: 'reporte'
                        });
                        var pdfUrl = URL.createObjectURL(blob);

                        var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);

                        if (isMobile) {
                            // Crear un enlace de descarga para dispositivos móviles
                            Swal.fire({
                                title: "Descargar Reporte",
                                text: "¿Desea descargar el reporte?",
                                showDenyButton: true,
                                showCancelButton: false,
                                confirmButtonText: "Descargar",
                                denyButtonText: "Cancelar",
                            }).then((result) => {
                                /* Read more about isConfirmed, isDenied below */
                                if (result.isConfirmed) {
                                    var downloadLink = document.createElement('a');
                                    downloadLink.href = pdfUrl;
                                    downloadLink.download = 'reporte.pdf'; // Nombre del archivo
                                    document.body.appendChild(downloadLink);
                                    downloadLink.click();
                                    document.body.removeChild(downloadLink);
                                    window.history.back();
                                    Swal.fire("Descargado!", "", "success");
                                } else if (result.isDenied) {
                                    window.history.back();
                                }
                            });
                        } else {
                            // Mostrar en iframe para dispositivos de escritorio
                            var iframe = document.createElement('iframe');
                            iframe.src = pdfUrl;
                            iframe.width = '100%';
                            iframe.classList.add('height-100');
                            iframe.style.border = 'none';

                            // Limpiar el contenedor antes de agregar el nuevo iframe
                            $("#contenedorGeneral").empty().append(iframe);
                        }

                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        revisarActividades();
                        Swal.fire({
                            title: 'Lo sentimos!',
                            text: 'No se puede generar pdf, porque no hay códigos subidos.',
                            icon: 'warning',
                            confirmButtonText: 'Aceptar',
                        }).then(() => {
                            // Retrocede después de que el usuario presione "Aceptar"
                            window.history.back();
                        });
                    }
                });
            } else {
                console.error("No se ha proporcionado una imagen válida.");
                revisarActividades();
                Swal.fire({
                    title: 'Lo sentimos!',
                    text: 'No se puede generar pdf, porque no hay códigos subidos.',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar',
                }).then(() => {
                    // Retrocede después de que el usuario presione "Aceptar"
                    window.history.back();
                });
            }
        }

        function toggleDropdown(event) {
            // Obtener el contenedor del dropdown relacionado con el ícono clickeado
            const dropdown = event.target.closest('.desplegable').querySelector('.desplegable-content');

            // Cerrar todos los dropdowns antes de abrir uno nuevo
            const allDropdowns = document.querySelectorAll('.desplegable-content');
            allDropdowns.forEach(d => {
                if (d !== dropdown) {
                    d.style.display = "none"; // Cerrar otros dropdowns
                }
            });

            // Alternar la visibilidad del dropdown actual
            dropdown.style.display = (dropdown.style.display === "block") ? "none" : "block";
        }

        // Cierra el dropdown al hacer clic fuera de él
        window.onclick = function(event) {
            // Verifica si el clic no fue en el ícono o en el contenido del desplegable
            if (!event.target.matches('.desplegable div i') && !event.target.closest('.desplegable')) {
                const dropdowns = document.querySelectorAll('.desplegable-content');
                dropdowns.forEach(dropdown => {
                    dropdown.style.display = "none"; // Cerrar todos los dropdowns
                });
            }
        }


        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        //?funcion para copiar el codigo
        function copiarCodigo(codigo) {
            var input = document.createElement('input');
            input.setAttribute('value', codigo);
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            Toast.fire({
                icon: "success",
                title: "Código copiado",
            });
        }

        function quitarAlumno(id) {
            Swal.fire({
                title: '¿Está seguro de que desea eliminar este alumno?',
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar alumno"
            }).then((result) => {
                if (result.value) {
                    $.ajax({
                        type: "POST",
                        url: "../../backend/cursosCrud.php?f=eliminarAlumno",
                        data: {
                            'id': id,
                            'curso': getQueryParam('curso'),
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
                                });
                                mostrarDetalleCursos();
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
                    url: "../../backend/usuario.php?f=cambiarClaveProfesor",
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
                        url: "../../backend/usuario.php?f=eliminarCuentaProfesor",
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

        //? valida de 0 - 20
        function validarCalificacion(input) {
            // Obtener el valor del input
            let valor = parseInt(input.value);

            // Si el valor no es un número, se limpia el campo
            if (isNaN(valor)) {
                input.value = '';
                return;
            }

            // Restringir el valor entre 0 y 20
            if (valor < 0) {
                input.value = 0;
            } else if (valor > 20) {
                input.value = 20;
            }
        }

        //?calificar
        function colocarNota(codigo) {
            var d = codigo.split("||");
            var id = d[0];
            var nota = d[1];
            document.getElementById('idnota').value = id;
            document.getElementById('calificacion').value = nota;
            $('#ponerNota').modal('show');
        }

        $(document).ready(function() {
            $('#btn_calificar').click(function() {
                var id = document.getElementById('idnota').value;
                var nota = document.getElementById('calificacion').value;
                $.ajax({
                    type: "POST",
                    url: "../../backend/actividadesCrud.php?f=calificar",
                    data: {
                        'id': id,
                        'nota': nota,
                        'tk': $('#tk').val()
                    },
                    dataType: 'json',
                    error: err => {
                        console.error(err)
                    },
                    beforeSend: function() {
                        $('#btn_calificar').prop('disabled', true);
                        $('#btn_calificar').html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Cargando...');
                    },
                    success: function(response) {
                        $('#btn_calificar').prop('disabled', false);
                        $('#btn_calificar').html('Guardar');
                        if (response.status == 'success') {
                            Swal.fire({
                                icon: "success",
                                title: "¡Listo!",
                                text: response.message,
                                confirmButtonText: `Aceptar`,
                            });
                            revisarActividades();
                            $('#ponerNota').modal('hide');
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
    </script>
</body>

</html>