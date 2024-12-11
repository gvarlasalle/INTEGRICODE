<?php

require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

function loguear()
{

    try {
        if ($_POST['tk2'] != "" && $_POST['tk2'] == $_SESSION['token']) {
            if ($_POST['tk2'] != "" && $_POST['tk2'] == $_SESSION['token'] && !empty(trim($_POST['txtUsuario'])) && !empty(trim($_POST['txtPass']))) {

                $user = $_POST['txtUsuario'];
                $pass = $_POST['txtPass'];

                $con = conectar();

                // Consulta preparada para prevenir inyección SQL
                $sql = "SELECT * from usuarios where correo = ? AND estado = 1";

                // Preparar la consulta
                $stmt = mysqli_prepare($con, $sql);

                // Vincular parámetros
                mysqli_stmt_bind_param($stmt, "s", $user);

                // Ejecutar la consulta
                mysqli_stmt_execute($stmt);

                // Obtener el resultado
                $resultado = mysqli_stmt_get_result($stmt);

                if ($row = $resultado->fetch_assoc()) {

                    // Verificar la contraseña utilizando password_verify()
                    if (password_verify($pass, $row['clave'])) {

                        //si el estado es 0 no puede ingresar
                        if ($row['estado'] == 0) {
                            echo 5;
                            exit();
                        }

                        //crear token jwt
                        $token = JWT::encode(
                            array(
                                'iat' => time(),
                                'nbf' => time(),
                                'exp' => time() + 60 * 60 * 24 * 30,
                                "data" => array(
                                    "id" => $row['id'],
                                    "nombre" => $row['nombre'],
                                    "correo" => $row['correo'],
                                    "apellidos" => $row['apellidos'],
                                    "rol" => $row['rol']
                                )
                            ),
                            $_ENV['KEY'],
                            'HS256'
                        );

                        if ($row['rol'] == 1) {
                            echo 1;
                        } else if ($row['rol'] == 2) {
                            echo 2;
                        }
                        //guardar en una cookie que dure 30 dias
                        setcookie("sesion", $token, time() + 60 * 60 * 24 * 30, "/", "", false, true);

                        //cerramos la consulta preparada
                        mysqli_stmt_close($stmt);
                        // Cerrar la conexión a la base de datos
                        mysqli_close($con);
                    } else {
                        //cerramos la consulta preparada
                        mysqli_stmt_close($stmt);
                        // Cerrar la conexión a la base de datos
                        mysqli_close($con);

                        echo 0;
                    }
                } else {
                    //cerramos la consulta preparada
                    mysqli_stmt_close($stmt);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($con);
                    echo 3;
                }
            } else {
                echo 4;
            }
        } else {
            echo 'no tienes permiso';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function registrar()
{

    try {
        if ($_POST['tk'] != "" && $_POST['tk'] == $_SESSION['token']) {
            if ($_POST['tk'] != "" && $_POST['tk'] == $_SESSION['token'] && !empty(trim($_POST['rnombre'])) && !empty(trim($_POST['rapellido'])) && !empty(trim($_POST['remail'])) && !empty(trim($_POST['rpassword'])) && !empty(trim($_POST['rrol']))) {

                $nom = $_POST['rnombre'];
                $ape = $_POST['rapellido'];
                $email = $_POST['remail'];
                $pass = $_POST['rpassword'];
                $rol = $_POST['rrol'];

                //validamos el correo
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    echo 3;
                    exit();
                }

                //validamos la contraseña
                if (strlen($pass) < 8) {
                    echo 4;
                    exit();
                }

                //conexion
                $conexion = conectar();

                //ecriptamos la contraseña
                $hash = password_hash($pass, PASSWORD_BCRYPT);


                //antes de ejecutar verificamos si el correo ya existe
                $sql2 = "select * from usuarios where correo = ? and estado = 1";
                $stmt2 = mysqli_prepare($conexion, $sql2);
                mysqli_stmt_bind_param($stmt2, "s", $email);
                mysqli_stmt_execute($stmt2);
                $resultado = mysqli_stmt_get_result($stmt2);
                if ($resultado->fetch_assoc()) {
                    //cerramos la consulta preparada
                    mysqli_stmt_close($stmt2);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($conexion);
                    echo 2;
                } else {
                    //cerramos la consulta preparada
                    mysqli_stmt_close($stmt2);

                    // Consulta preparada para prevenir inyección SQL
                    $sql = "insert into usuarios (correo, clave, nombre, apellidos, rol, estado) values (?,?,?,?,?,1)";

                    // Preparar la consulta
                    $stmt = mysqli_prepare($conexion, $sql);

                    // Vincular parámetros
                    mysqli_stmt_bind_param($stmt, "sssss", $email, $hash, $nom, $ape, $rol);

                    // Ejecutar la consulta
                    mysqli_stmt_execute($stmt);

                    // Cerrar la consulta preparada
                    mysqli_stmt_close($stmt);

                    // Cerrar la conexión a la base de datos
                    mysqli_close($conexion);
                    echo 1;
                }
            } else {
                echo 0;
            }
        } else {
            echo 'no tienes permiso';
        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

function cerrarSesion()
{
    setcookie('sesion', '', time() - 3600, '/');
    session_destroy();
    header("Location: ../");
    exit();
}

function eliminarCuentaProfesor()
{
    try {

        if ($_POST['tk'] == $_SESSION['token'] && $_SESSION['idProfesor'] != "") {
            $con = conectar();
            $id = $_SESSION['idProfesor'];
            $sql = "UPDATE usuarios SET estado = 0 WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // Cerrar la conexión a la base de datos
            mysqli_close($con);

            //eliminar cookie
            setcookie('sesion', '', time() - 3600, '/');
            session_destroy();
            $respuesta['status'] = 'success';
            $respuesta['message'] = 'Cuenta eliminada correctamente';
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'no tienes un token válido';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    //devolver respuesta en json
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function eliminarCuentaAlumno()
{
    try {

        if ($_POST['tk'] == $_SESSION['token'] && $_SESSION['idAlumno'] != "") {
            $con = conectar();
            $id = $_SESSION['idAlumno'];
            $sql = "UPDATE usuarios SET estado = 0 WHERE id = ?";
            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            // Cerrar la conexión a la base de datos
            mysqli_close($con);

            //eliminar cookie
            setcookie('sesion', '', time() - 3600, '/');
            session_destroy();
            $respuesta['status'] = 'success';
            $respuesta['message'] = 'Cuenta eliminada correctamente';
        } else {
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'no tienes un token válido';
        }
    } catch (Exception $e) {
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    //devolver respuesta en json
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function cambiarClaveAlumno(){
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if(!empty(trim($_POST['clave']))){
                $clave = $_POST['clave'];
                if (strlen($clave) < 8) {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'La contraseña debe tener al menos 8 caracteres';
                }else{
                    $id = $_SESSION['idAlumno'];
                    $con = conectar();
                    $hash = password_hash($clave, PASSWORD_BCRYPT);
                    $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $hash, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($con);
                    //borrar cookie
                    setcookie('sesion', '', time() - 3600, '/');
                    session_destroy();
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Contraseña actualizada correctamente';
                }
            }else{
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se ha completado el campo de contraseña';
            }
        }else{
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'no tienes un token válido';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    //devolver respuesta en json
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

function cambiarClaveProfesor(){
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            if(!empty(trim($_POST['clave']))){
                $clave = $_POST['clave'];
                if (strlen($clave) < 8) {
                    $respuesta['status'] = 'error';
                    $respuesta['message'] = 'La contraseña debe tener al menos 8 caracteres';
                }else{
                    $id = $_SESSION['idProfesor'];
                    $con = conectar();
                    $hash = password_hash($clave, PASSWORD_BCRYPT);
                    $sql = "UPDATE usuarios SET clave = ? WHERE id = ?";
                    $stmt = mysqli_prepare($con, $sql);
                    mysqli_stmt_bind_param($stmt, "si", $hash, $id);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                    // Cerrar la conexión a la base de datos
                    mysqli_close($con);
                    //borrar cookie
                    setcookie('sesion', '', time() - 3600, '/');
                    session_destroy();
                    $respuesta['status'] = 'success';
                    $respuesta['message'] = 'Contraseña actualizada correctamente';
                }
            }else{
                $respuesta['status'] = 'error';
                $respuesta['message'] = 'No se ha completado el campo de contraseña';
            }
        }else{
            $respuesta['status'] = 'error';
            $respuesta['message'] = 'no tienes un token válido';
        }
    }catch(Exception $e){
        $respuesta['status'] = 'catch';
        $respuesta['message'] = $e->getMessage();
    }
    //devolver respuesta en json
    header('Content-Type: application/json');
    echo json_encode($respuesta);
}

if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
