<?php
require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;
use \Firebase\JWT\key;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

function cursosAlumnosDB($conexion, $codigo)
{
    $sql = "SELECT c.nombre from detallecurso dc JOIN cursos c on dc.curso=c.id WHERE dc.alumno='$codigo'";
    $resultado = $conexion->query($sql);
    $cursos = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $cursos[] = $row;  // Guardamos cada noticia en un array
    }

    return $cursos;
}

function cursosProfesorDB($conexion, $codigo)
{
    $sql = "SELECT nombre FROM cursos WHERE usuario='$codigo'";
    $resultado = $conexion->query($sql);
    $cursos = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $cursos[] = $row;  // Guardamos cada noticia en un array
    }

    return $cursos;
}

function usuarioDB($conexion, $codigo){
    $sql = "SELECT * FROM usuarios WHERE id='$codigo'";
    $resultado = $conexion->query($sql);
    $usuario = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $usuario[] = $row;  // Guardamos cada noticia en un array
    }

    return $usuario;
}

function listarAjustes()
{
    try{
        if ($_POST['tk'] == $_SESSION['token']) {
            $key = $_ENV['KEY'];
            if (isset($_COOKIE['sesion'])) {
                $decode = JWT::decode($_COOKIE['sesion'], new key($key, 'HS256'));
            } else {
                //redireccionamos al login
                header("Location: ../");
            }
    
            $conexion = conectar();
            $codigo = $decode->data->id;
            $nombreEx = explode(" ", $decode->data->nombre);
            $nombreEx = $nombreEx[0];
            $nombre_completo = $decode->data->nombre . " " . $decode->data->apellidos;
            $correo = $decode->data->correo;
            
            $rol = $decode->data->rol;
            if ($rol == 1) {
                $rol = "Profesor";
            } else {
                $rol = "Alumno";
            }
            $imagen = "server/usuarios/" . $decode->data->id . $nombreEx . ".png";
            if (!file_exists("../" . $imagen)) {
                $imagen = "assets/images/sf.jpg";
            }
        ?>
                <div class="my-4">
                    <div class="height-100 scroll bg-white pt-2">
                        <div class="container py-3">
                            <div class="row align-items-center gap-4">
                                <div class="col-lg-auto col-md-auto col-sm-auto col-xs-auto">
                                    <div class="archivo" id="archivo">
                                        <img src="../../<?php echo $imagen; ?>" id="perfil-edit" alt="">
                                        <div class="file-select" id="src-file1">
                                            <input id="upload" type="file" id="file" name="src-file1" accept="image/*" aria-label="Archivo">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-auto col-md-auto col-sm-auto col-xs-auto">
                                    <div class="w-3/4 text-muted">Se recomienda subir una imagen en formato PNG o JPG, para un optimo funcionamiento</div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="card redondear">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap">
                                        <h5 class="fw-bold">Información Personal</h5>
                                        <button onclick="cambiarClave()" class="btn btn-outline-dark redondear"><i class='bx bx-lock-alt'></i> <span class="">Editar Contraseña</span></button>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12 mb-2">
                                            <span class="text-muted">Nombre Completo</span>
                                            <div class="fw-bold"><?php echo $nombre_completo; ?></div>
                                        </div>
                                        <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 mb-2">
                                            <span class="text-muted">Correo</span>
                                            <div class="fw-bold"><?php echo $correo; ?></div>
                                        </div>
                                        <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <span class="text-muted">Código</span>
                                                    <div class="fw-bold"><?php echo $codigo; ?></div>
                                                </div>
                                                <div>
                                                    <span class="text-muted">Rol</span>
                                                    <div class="fw-bold"><?php echo $rol; ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card text-dark bg-light redondear mt-4">
                                <div class="card-body">
                                    <h5 class="fw-bold"><?php echo $rol == 'Profesor' ? 'Cursos Creados' : 'Cursos Asignados'; ?></h5>
                                    <div class="d-flex align-items-center justify-content-start gap-3 flex-wrap">
                                        <?php
                                        if($rol == 'Profesor'){
                                            $dataCursos = cursosProfesorDB($conexion, $codigo);
                                        }else{
                                            $dataCursos = cursosAlumnosDB($conexion, $codigo);
                                        }
    
                                        foreach ($dataCursos as $curso) {
                                            ?>
                                            <div class="badge bg-azul text-white"><?php echo $curso['nombre']; ?></div>
                                            <?php } ?>
                                    </div>
                                </div>
                            </div>
    
                            <div class="card mt-4">
                                <div class="card-body">
                                    <button onclick="eliminarCuenta()" class="btn btn-outline-danger redondear"><i class='bx bx-trash' ></i> Eliminar Cuenta</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        <?php
        } else {
            echo "no autorizado";
        }
    }catch(Exception $e){
        echo $e->getMessage();
    }
}

function actualizarFoto()
{
    if ($_POST['tk'] == $_SESSION['token']) {

        $key = $_ENV['KEY'];
        if (isset($_COOKIE['sesion'])) {
            $decode = JWT::decode($_COOKIE['sesion'], new key($key, 'HS256'));
        } else {
            //redireccionamos al login
            header("Location: ../");
        }

        $id = $decode->data->id;
        $nombreEx = explode(" ", $decode->data->nombre);
        $nombreEx = $nombreEx[0];
        extract($_POST);
        $dir = "../server/usuarios/";
        if (!is_dir($dir))
            mkdir($dir);
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);
        $save = file_put_contents($dir . $id . $nombreEx . ".png", $img);
        if ($save) {
            $resp['status'] = 'success';
        } else {
            $resp['status'] = 'failed';
        }
        echo json_encode($resp);
    } else {
        echo "no autorizado";
    }
}

function actualizarFotoCurso()
{
    if ($_POST['tk'] == $_SESSION['token']) {

        $key = $_ENV['KEY'];
        if (isset($_COOKIE['sesion'])) {
            $decode = JWT::decode($_COOKIE['sesion'], new key($key, 'HS256'));
        } else {
            //redireccionamos al login
            header("Location: ../");
        }

        $id = $decode->data->id;
        $codigo = $_POST['cod'];
        extract($_POST);
        $dir = "../server/cursos/";
        if (!is_dir($dir))
            mkdir($dir);
        $img = str_replace('data:image/png;base64,', '', $img);
        $img = str_replace(' ', '+', $img);
        $img = base64_decode($img);
        $save = file_put_contents($dir . $id . $codigo . ".png", $img);
        if ($save) {
            $resp['status'] = 'success';
        } else {
            $resp['status'] = 'failed';
        }
        echo json_encode($resp);
    } else {
        echo "no autorizado";
    }
}


if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
