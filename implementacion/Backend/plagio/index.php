<?php
session_start();
require_once('vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, 'inc/.env');
$dotenv->safeLoad();

//comprobar si ya inicio sesion
if (isset($_COOKIE['sesion'])) {
    try {
        $decode = JWT::decode($_COOKIE['sesion'], new key($_ENV['KEY'], 'HS256'));
        if ($decode->data->rol == 2) {
            header('Location: home/alumno');
        } else if ($decode->data->rol == 1) {
            header('Location: home/profesor');
        }
    } catch (Exception $e) {
        //borrar cookie 
        setcookie('sesion', '', time() - 3600, '/');
    }
}
if (!isset($_SESSION['token'])) {
    $token = bin2hex(random_bytes(32));
    $_SESSION['token'] = $token;
} else {
    $token = $_SESSION['token'];
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Integricode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/login.css">
</head>

<body>

    <div class="container">
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-6 col-xl-6 altura">
                <div class="container w-90 lo">
                    <form method="post" id="frmLogin">
                        <div class="container text-center img-logo">
                            <img src="assets/images/plagio.webp" class="w-43" alt="">
                        </div>
                        <div class="text-center mt-4">
                            <h3>Iniciar Sesión</h3>
                            <p class="text-start">Ingrese su correo y contraseña para iniciar sesión.</p>
                        </div>

                        <div class="mt-2">
                            <label for="usuario" class="form-label fw-bold">Correo</label>
                            <input type="email" class="form-control" name="txtUsuario" id="txtUsuario" required placeholder="correo@gmail.com">
                        </div>
                        <div class="mt-2">
                            <label for="pass" class="form-label fw-bold">Contraseña</label>
                            <div class="position-relative">
                                <input type="password" class="form-control contrasena" name="txtPass" id="txtPass" placeholder="**********">
                                <span data-activo=false class="glyphicon"><i class="bi bi-eye-fill"></i></span>
                            </div>
                        </div>
                        <input type="hidden" name="tk2" id="tk2" value="<?php echo $token; ?>">
                        <div class="mt-4">
                            <button type="button" id="btnLogin" class="btn bg-negro btn-primary text-light fw-bold w-100">Iniciar Sesión</button>
                        </div>
                        <div class="mt-4 text-center fs-small">
                            <a onclick="mostrarRegistro()" class="nav-link text-dark fw-bold pointer">
                                ¿No tienes una cuenta? Registrate
                            </a>
                            <a onclick="mostrarRecuperar()" class="nav-link text-dark fw-bold pointer">
                                ¿Olvidaste tu contraseña? Recuperar
                            </a>
                        </div>
                    </form>

                    <form method="post" id="frmRegistro" style="display: none;">
                        <div class="mt-4">
                            <h3>Registrarse</h3>
                        </div>

                        <input type="hidden" name="tk" id="tk" value="<?php echo $token; ?>">
                        <div class="mt-2">
                            <label class="form-label">Nombre</label>
                            <input class="form-control" name="rnombre" id="rnombre" placeholder="Ingresa tus nombres" required type="text" />
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Apellidos</label>
                            <input class="form-control" name="rapellido" id="rapellido" placeholder="Ingresa tus apellidos" required type="text" />
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Correo</label>
                            <input class="form-control" name="remail" id="remail" placeholder="correo@gmail.com" required type="email" />
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Contraseña</label>
                            <div class="position-relative">
                                <input class="form-control contrasena" name="rpassword" id="rpassword" placeholder="**********" required type="password" />
                                <span data-activo=false class="glyphicon"><i class="bi bi-eye-fill"></i></span>
                            </div>
                        </div>

                        <div class="mt-2">
                            <label class="form-label">Rol</label>
                            <select name="rrol" id="rrol" class="form-select">
                                <option value="2" selected>Alumno</option>
                                <option value="1">Profesor</option>
                            </select>
                        </div>

                        <div class="mt-4">
                            <button type="button" class="btn bg-negro btn-primary text-light fw-bold w-100" id="btnRegistrarse">Registrarse</button>
                        </div>

                        <div class="mt-4 text-center fs-small">
                            <a onclick="mostrarLogin()" class="nav-link text-dark fw-bold pointer">
                                ¿Tienes una cuenta? Iniciar Sesión
                            </a>
                        </div>
                    </form>

                    <form method="post" id="frmRecuperar" style="display: none;">
                        <div class="container text-center img-logo">
                            <img src="assets/images/plagio.webp" class="w-43" alt="">
                        </div>
                        <div class="text-center mt-4">
                            <h3>Recupera tu cuenta</h3>
                            <p class="text-start">Introduce tu correo electrónico con el cual te registraste para buscar tu cuenta.</p>
                        </div>
                        <input type="hidden" name="tk3" id="tk3" value="<?php echo $token; ?>">
                        <div class="mt-3">
                            <input type="email" id="crecuperar" name="crecuperar" placeholder="correo@gmail.com" required class="form-control">
                        </div>
                        <div class="mt-4">
                            <button class="btn bg-negro btn-primary text-light fw-bold w-100" id="btnBuscar">Buscar</button>
                        </div>
                        <div class="mt-4 text-center fs-small">
                            <a onclick="mostrarLogin()" class="nav-link text-dark fw-bold pointer">
                                ¿Tienes una cuenta? Iniciar Sesión
                            </a>
                        </div>
                    </form>

                    <div id="cambiarClave" style="display: none;">
                        <div class="container text-center img-logo">
                            <img src="assets/images/plagio.webp" class="w-43" alt="">
                        </div>
                        <div class="text-center mt-4">
                            <h3>Recupera tu cuenta</h3>
                            <p class="text-start">Completa los datos de forma correcta, revisa tu correo se te envio un codigo.</p>
                        </div>
                        <input type="hidden" name="tk4" id="tk4" value="<?php echo $token; ?>">
                        <div class="mt-3">
                            <label for="rcodigo" class="form-label">Codigo</label>
                            <input type="text" id="rcodigo" name="rcodigo" placeholder="XXXXX" required class="form-control">
                        </div>
                        <div class="mt-3">
                            <label for="rnclave" class="form-label">Nueva clave</label>
                            <input type="text" id="rnclave" name="rnclave" placeholder="Nueva contraseña..." required class="form-control">
                        </div>
                        <div class="mt-4">
                            <button class="btn bg-negro btn-primary text-light fw-bold w-100" id="btnRecuperar">Cambiar</button>
                        </div>
                    </div>

                </div>
            </div>
            <div class="col-sm-12 col-md-6 col-lg-6 col-xl-6 con-img">
                <div class="container lo">
                    <div id="contiene-cosas" class="">
                        <img src="assets/images/plagio.webp" class="flotar w-90" alt="Sistema antiplagio">
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="assets/js/login.js"></script>
</body>

</html>