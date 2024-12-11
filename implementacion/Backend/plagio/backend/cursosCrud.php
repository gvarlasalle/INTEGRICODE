<?php
require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

require_once('validaciones.php');

function createCurso()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['nombre'])) && !empty(trim($_POST['aula'])) && isset($_SESSION['idProfesor'])) {

                $nombre = $_POST['nombre'];
                $aula = $_POST['aula'];
                $idProfesor = $_SESSION['idProfesor'];
                $conexion = conectar();

                $codigo = generarCodigoUnico($conexion);

                if ($codigo) {
                    $sql = "INSERT INTO cursos(nombre, aula, usuario, cod, estado) VALUES (?,?,?,?,1)";
                    $stmt = mysqli_prepare($conexion, $sql);
                    mysqli_stmt_bind_param($stmt, "ssis", $nombre, $aula, $idProfesor, $codigo);
                    $resultado = mysqli_stmt_execute($stmt);

                    if ($resultado) {
                        $respuesta['status'] = 'success';
                        $respuesta['message'] = 'Curso creado correctamente';
                    } else {
                        $respuesta['status'] = 'error';
                        $respuesta['message'] = 'Error al ejecutar la consulta';
                    }
                    //cerrar la declaración preparada
                    mysqli_stmt_close($stmt);
                } else {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'No se pudo generar un código único';
                }

                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function unirseCurso()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['cod']))) {
                $cod = $_POST['cod'];
                $conexion = conectar();

                // Consultar si el código es igual al del curso usando consulta preparada
                $sql = "SELECT id FROM cursos WHERE cod = ?";
                $stmt = $conexion->prepare($sql);
                $stmt->bind_param("s", $cod);
                $stmt->execute();
                $resultado = $stmt->get_result();
                $row = $resultado->fetch_assoc();

                if ($row && $row['id'] != "") {
                    $idCurso = $row['id'];
                    $idAlumno = $_SESSION['idAlumno'];

                    // Verificar si el alumno ya se ha unido al curso
                    $sql = "SELECT curso FROM detallecurso WHERE alumno = ? AND curso = ?";
                    $stmt = $conexion->prepare($sql);
                    $stmt->bind_param("ii", $idAlumno, $idCurso);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    $fila = $resultado->fetch_assoc();

                    if ($fila) {
                        $respuesta['status'] = 'error';
                        $respuesta['message'] = 'Ya se ha unido al curso';
                    } else {
                        // Insertar en detallecurso si no está ya unido
                        $sql = "INSERT INTO detallecurso(alumno, curso) VALUES (?, ?)";
                        $stmt = $conexion->prepare($sql);
                        $stmt->bind_param("ii", $idAlumno, $idCurso);
                        $stmt->execute();

                        if ($stmt->affected_rows > 0) {
                            $respuesta['status'] = 'success';
                            $respuesta['message'] = 'Se ha unido al curso correctamente';
                        } else {
                            $respuesta['status'] = 'error';
                            $respuesta['message'] = 'Error al unirse al curso';
                        }
                    }
                } else {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'El código no es válido';
                }
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }

    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}


function generarCodigoUnico($conexion)
{
    $codigoValido = false;
    $codigo = "";

    // Intentar generar un código único
    for ($i = 0; $i < 10; $i++) { // Intentar hasta 10 veces generar un código único
        $codigo = strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

        // Verificar si el código ya existe en la base de datos
        $sqlVerificar = "SELECT cod FROM cursos WHERE cod = ?";
        $stmtVerificar = mysqli_prepare($conexion, $sqlVerificar);
        mysqli_stmt_bind_param($stmtVerificar, "s", $codigo);
        mysqli_stmt_execute($stmtVerificar);
        mysqli_stmt_store_result($stmtVerificar);

        if (mysqli_stmt_num_rows($stmtVerificar) == 0) {
            // El código no existe, es válido
            $codigoValido = true;
            mysqli_stmt_close($stmtVerificar);
            break;
        }
        mysqli_stmt_close($stmtVerificar);
    }

    // Retornar el código si es válido, o false si no se pudo generar uno
    return $codigoValido ? $codigo : false;
}

function eliminarCurso()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id']))) {
                $id = $_POST['id'];
                $profesor = $_SESSION['idProfesor'];
                $conexion = conectar();

                //*verificar si el curso me pertenece o no
                if (!validarCursoProfesor($conexion, $id, $profesor)) {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'Curso no encontrado';
                } else {
                    //actualizar estado a 0
                    $sql = "UPDATE cursos SET estado = 0 WHERE id = ?";
                    $stmt = mysqli_prepare($conexion, $sql);
                    mysqli_stmt_bind_param($stmt, "i", $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($conexion);
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Curso eliminado correctamente';
                }
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se envio el id';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function editarCurso()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['nombre'])) && !empty(trim($_POST['aula']))) {
                $id = $_POST['id'];
                $nombre = $_POST['nombre'];
                $aula = $_POST['aula'];
                $profesor = $_SESSION['idProfesor'];
                $conexion = conectar();

                //*VERIFICAR ANTES QUE EL CURSO ME PERTENEZCA
                if (!validarCursoProfesor($conexion, $id, $profesor)) {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'Curso no encontrado';
                } else {

                    //actualizar estado a 0
                    $sql = "UPDATE cursos SET nombre = ?, aula = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conexion, $sql);
                    mysqli_stmt_bind_param($stmt, "ssi", $nombre, $aula, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($conexion);
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Curso actualizado correctamente';
                }
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los datos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function insertNoticia()
{
    try {

        if ($_POST['tk'] == $_SESSION['token']) {

            if (!empty(trim($_POST['noticia'])) && !empty(trim($_POST['curso']))) {
                $conexion = conectar();
                $curso = $_POST['curso'];
                $noticia = $_POST['noticia'];

                date_default_timezone_set('America/Lima');
                $fecha = date('Y-m-d');

                $sql = "INSERT INTO noticias(fecha, noticia, curso, estado) VALUES (?, ?, ?, 1)";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ssi", $fecha, $noticia, $curso);

                $resultado = mysqli_stmt_execute($stmt);

                if ($resultado) {
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Se ha publicado la noticia correctamente';
                } else {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'Error al ejecutar la consulta';
                }
                //cerrar la declaración preparada
                mysqli_stmt_close($stmt);
                //cerrar la conexión a la base de datos
                mysqli_close($conexion);
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function eliminarNoticia()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id']))) {
                $id = $_POST['id'];
                $conexion = conectar();
                //actualizar estado a 0
                $sql = "UPDATE noticias SET estado = 0 WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Noticia eliminada correctamente';
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se envio el id';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function editarNoticia()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['noticia']))) {
                $id = $_POST['id'];
                $noticia = $_POST['noticia'];
                $profesor = $_SESSION['idProfesor'];
                $conexion = conectar();

                if (!validarNoticiaProfesor($conexion, $id, $profesor)) {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'No tienes permisos para editar esta noticia';
                } else {
                    $consulta = "UPDATE noticias SET noticia = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conexion, $consulta);
                    mysqli_stmt_bind_param($stmt, "si", $noticia, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'La noticia se ha actualizado correctamente';
                }
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'No tienes autorización para acceder a esta función';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function eliminarAlumno()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['curso']))) {
                $id = $_POST['id'];
                $curso = $_POST['curso'];
                $conexion = conectar();
                $sql = "DELETE FROM detallecurso WHERE alumno = ? and curso = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $id, $curso);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $sql2 = "UPDATE detalleact AS da
                JOIN actividades AS a ON da.actividad = a.id
                SET da.estado = 0, da.similitud = NULL
                WHERE da.alumno = ? AND a.curso = ?;";
                $stmt2 = mysqli_prepare($conexion, $sql2);
                mysqli_stmt_bind_param($stmt2, "ii", $id, $curso);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                $conexion->close();
                $respuesta['status'] = "success";
                $respuesta['message'] = "Se eliminó el alumno con éxito";
            } else {
                $respuesta['status'] = "error";
                $respuesta['message'] = "Falta el ID del alumno";
            }
        } else {
            $respuesta['status'] = "error";
            $respuesta['message'] = "No tienes autorización para acceder a esta función";
        }
    } catch (Exception $e) {
        $respuesta['status'] = "catch";
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}


//!funcion para salirse del curso
function salirCurso()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            if ($_SESSION['idAlumno'] && !empty(trim($_POST['curso']))) {
                $id = $_SESSION['idAlumno'];
                $curso = $_POST['curso'];
                $conexion = conectar();
                $sql = "DELETE FROM detallecurso WHERE alumno = ? and curso = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $id, $curso);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);

                $sql2 = "UPDATE detalleact AS da
                JOIN actividades AS a ON da.actividad = a.id
                SET da.estado = 0, da.similitud = NULL
                WHERE da.alumno = ? AND a.curso = ?;";
                $stmt2 = mysqli_prepare($conexion, $sql2);
                mysqli_stmt_bind_param($stmt2, "ii", $id, $curso);
                mysqli_stmt_execute($stmt2);
                mysqli_stmt_close($stmt2);

                $conexion->close();
                $respuesta['status'] = "success";
                $respuesta['message'] = "Has salido del curso";
            } else {
                $respuesta['status'] = "error";
                $respuesta['message'] = "No se ha especificado el curso";
            }
        } else {
            $respuesta['status'] = "error";
            $respuesta['message'] = "No tienes autorización para acceder a esta función";
        }
    } catch (Exception $e) {
        $respuesta['status'] = "catch";
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
