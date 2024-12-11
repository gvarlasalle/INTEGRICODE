<?php
require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

function createActividad()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['titulo'])) && !empty(trim($_POST['descripcion'])) && !empty(trim($_POST['fechaf'])) && !empty(trim($_POST['curso'])) && isset($_SESSION['idProfesor'])) {
    
                $titulo = $_POST['titulo'];
                $desc = $_POST['descripcion'];
                $fechaf = $_POST['fechaf'];
                $curso = $_POST['curso'];
                $idProfesor = $_SESSION['idProfesor'];
    
                //obtener fecha establecer lima
                date_default_timezone_set('America/Lima');
                $fechai = date('Y-m-d'); // Cambiado a formato de fecha de MySQL
    
                $conexion = conectar();
    
                $sql = "INSERT INTO actividades(titulo, descripcion, fechaf, fechai, curso, estado) VALUES (?,?,?,?,?,1)";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ssssi", $titulo, $desc, $fechaf, $fechai, $curso);
                $resultado = mysqli_stmt_execute($stmt);
    
                if ($resultado) {
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Actividad creada correctamente';
                } else {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'Error al ejecutar la consulta';
                }
                //cerrar la declaración preparada
                mysqli_stmt_close($stmt);
    
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
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function entregarActividad()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['link']))) {
                $id = $_POST['id'];
                $link = $_POST['link'];
                $alumno = $_SESSION['idAlumno'];
    
                $rutatxt = $_POST['ruta'];
    
                $conexion = conectar();

                //validar la fecha traer la fecha y comparar la cantidad de tiempo con la fecha actual
                date_default_timezone_set('America/Lima');
                $fechaactual = new DateTime();

                $sql2 = "SELECT fechaf FROM actividades WHERE id='$id'";
                $resultado2 = $conexion->query($sql2);
                $resultado2 = $resultado2->fetch_assoc();
                $fechaf = new DateTime($resultado2['fechaf']);
                if ($fechaactual > $fechaf) {
                    // Calcular la diferencia entre la fecha actual y la fecha de entrega
                    $diferencia = $fechaactual->diff($fechaf);
                    $dias = $diferencia->d;
                    $horas = $diferencia->h;
                    $minutos = $diferencia->i;
                
                    if ($dias > 0) {
                        $retraso = $dias . ' d ' . $horas . ' h ' . $minutos . ' m';
                    } else if ($horas > 0) {
                        $retraso = $horas . ' h ' . $minutos . ' m';
                    } else {
                        $retraso = $minutos . ' m';
                    }
                    $estado = 2;
                }else{
                    $retraso = null;
                    $estado = 1;
                }

                $sql = "INSERT INTO detalleact(alumno, url, actividad, rutatxt, retraso, estado) VALUES ('$alumno', '$link', '$id', '$rutatxt', '$retraso', '$estado')";
    
                $conexion->query($sql);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Actividad entregada correctamente';
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function actualizarCodigo()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['link'])) && !empty(trim($_POST['ruta']))) {
                $id = $_POST['id'];
                $link = $_POST['link'];
                $ruta = $_POST['ruta'];
                $alumno = $_SESSION['idAlumno'];
    
                $conexion = conectar();

                //validar la fecha traer la fecha y comparar la cantidad de tiempo con la fecha actual
                date_default_timezone_set('America/Lima');
                $fechaactual = new DateTime();

                $sql2 = "SELECT a.fechaf from actividades a JOIN detalleact da ON da.actividad=a.id WHERE da.id='$id'";
                $resultado2 = $conexion->query($sql2);
                $resultado2 = $resultado2->fetch_assoc();
                $fechaf = new DateTime($resultado2['fechaf']);
                if ($fechaactual > $fechaf) {
                    // Calcular la diferencia entre la fecha actual y la fecha de entrega
                    $diferencia = $fechaactual->diff($fechaf);
                    $dias = $diferencia->d;
                    $horas = $diferencia->h;
                    $minutos = $diferencia->i;
                
                    if ($dias > 0) {
                        $retraso = $dias . ' d ' . $horas . ' h ' . $minutos . ' m';
                    } else if ($horas > 0) {
                        $retraso = $horas . ' h ' . $minutos . ' m';
                    } else {
                        $retraso = $minutos . ' m';
                    }
                    $estado = 2;
                }else{
                    $retraso = null;
                    $estado = 1;
                }

                $sql = "UPDATE detalleact SET rutatxt = ?, url = ?, estado = ?, retraso = ?  WHERE id = ? and alumno = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ssisii", $ruta, $link, $estado, $retraso, $id, $alumno);
                $resultado = mysqli_stmt_execute($stmt);
    
                if ($resultado) {
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Código actualizado correctamente';
                } else {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'Error al ejecutar la consulta';
                }
                //cerrar la declaración preparada
                mysqli_stmt_close($stmt);
    
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
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function rutaDB($conexion, $id)
{
    $sql = "SELECT rutatxt FROM detalleact WHERE id=?";
    $stmt = mysqli_prepare($conexion, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $ruta);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    return $ruta;
}

function cancelarEntrega()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id']))) {
                $id = $_POST['id'];
                $conexion = conectar();
                //actualizar estado a 0
                $sql = "UPDATE detalleact SET estado = 0, similitud=null, nota=null, retraso=null WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                //eliminar archivo txt
                $ruta = rutaDB($conexion, $id);
                unlink($ruta);
                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Actividad cancelada correctamente';
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se envio el id';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function eliminarActividad()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id']))) {
                $id = $_POST['id'];
                $conexion = conectar();
                //actualizar estado a 0
                $sql = "UPDATE actividades SET estado = 0 WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "i", $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Actividad eliminada correctamente';
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se envio el id';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function editarActividad()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['titulo'])) && !empty(trim($_POST['descripcion'])) && !empty(trim($_POST['fechaf']))) {
                $id = $_POST['id'];
                $titulo = $_POST['titulo'];
                $descripcion = $_POST['descripcion'];
                $fechaf = $_POST['fechaf'];
                $conexion = conectar();
                //actualizar estado a 0
                $sql = "UPDATE actividades SET titulo = ?, descripcion = ?, fechaf = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $titulo, $descripcion, $fechaf, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Actividad actualizada correctamente';
            } else {
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se envio el id';
            }
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function calificar(){
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if (!empty(trim($_POST['id'])) && !empty(trim($_POST['nota']))) {
                $id = $_POST['id'];
                $nota = $_POST['nota'];
                $conexion = conectar();
                $sql = "UPDATE detalleact SET nota = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ii", $nota, $id);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                // Cerrar la conexión a la base de datos
                mysqli_close($conexion);
                $respuesta['status'] = 'success';
                $respuesta['message'] = 'Actividad calificada correctamente';
            }else{
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'Todos los campos son obligatorios';
            }
        }else{
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'Token no autorizado';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    // Devolver la respuesta en formato JSON
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
