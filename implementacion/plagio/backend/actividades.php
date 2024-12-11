<?php
require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

require_once('validaciones.php');

function listarActividadesDB($conexion, $actividad)
{
    $sql = "CALL ObtenerDetallesActividad('$actividad')";
    $resultado = $conexion->query($sql);
    return $resultado;
}

function instruccionesDB($conexion, $actividad)
{
    $alumno = $_SESSION['idAlumno'];
    $sql = "SELECT da.id, a.titulo, a.fechai, a.fechaf, a.descripcion, da.estado FROM detalleact da join actividades a on da.actividad=a.id WHERE da.alumno = '$alumno' and a.id = '$actividad' AND a.estado=1 ORDER BY da.id ASC";
    $resultado = $conexion->query($sql);
    return $resultado;
}

function listarDetalleActDB($conexion, $actividad)
{
    $sql = "SELECT * from actividades WHERE id='$actividad' AND estado=1";
    $resultado = $conexion->query($sql);
    return $resultado;
}

function detalleActDB($conexion, $actividad)
{
    $sql = "SELECT * from detalleact WHERE actividad='$actividad' and alumno='" . $_SESSION['idAlumno'] . "'";
    $resultado = $conexion->query($sql);
    return $resultado;
}

function listarActividades()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
?>
            <div class="py-4">
                <div class="height-100 pb-3 scroll accordion accordion-flush bg-white" id="accordionFlushExample">
                    <div class="container pb-3">
                        <?php
                        for ($i = 0; $i < 20; $i++) {
                        ?>
                            <div class="accordion-item">
                                <div class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="<?php echo "#aact" . $i; ?>" aria-expanded="false" aria-controls="flush-collapseOne">
                                        Accordion Item #<?php echo $i; ?>
                                    </button>
                                </div>
                                <div id="<?php echo "aact" . $i; ?>" class="accordion-collapse collapse w-100" data-bs-parent="#accordionFlushExample">
                                    <div class="accordion-body">

                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="text-muted">Publicado: 20/10/2024</span>
                                            <span class="text-success fw-bold">Asignado</span>
                                        </div>
                                        <div class="mt-4">
                                            <p>Lorem ipsum dolor sit, amet consectetur adipisicing elit. Fuga at facilis minima nihil unde fugit laudantium quasi? Recusandae neque atque delectus vitae. Possimus alias aliquid aliquam tempore voluptas rerum facilis.</p>
                                        </div>
                                    </div>
                                    <div class="card-footer">
                                        <a href="?f=instrucciones" type="button" class="btn btn-primary">Subir Actividad</a>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            </div>
        <?php
        } else {
            echo "no autorizado";
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

function listarInstrucciones()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $actividad = $_POST['id'];
            $alumno = $_SESSION['idAlumno'];

            if (!validarActividadAlumno($conexion, $actividad, $alumno)) {
                die("Actividad no encontrada");
            }

            $dataActividades = instruccionesDB($conexion, $actividad);
            $dataActividades = $dataActividades->fetch_assoc();
            if (!$dataActividades) {
                $dataActividades = listarDetalleActDB($conexion, $actividad);
                $dataActividades = $dataActividades->fetch_assoc();
                if (!$dataActividades) {
                    die("actividad no encontrada");
                }
                $template = '
                <div class="mt-2">
                    <input type="text" class="form-control" id="link" placeholder="Ingrese el link del código">
                </div>
                <div class="mt-3">
                    <button class="btn bg-azul btn-primary w-100 fw-bold" id="btn_entregar">Entregar actividad</button>
                </div>
            ';
                $similitud = '';
                $estado = 'Asignado';
            } else {
                $revision = detalleActDB($conexion, $actividad);
                $revision = $revision->fetch_assoc();

                if ($revision['estado'] == 0) {
                    $estado = 'Asignado';
                    $template = '
                    <div class="mt-2">
                    <input type="text" class="form-control" value="' . $revision["url"] . '" id="link_act" placeholder="Ingrese el link del código">
                    <input type="hidden" id="id_act_u" value="' . $dataActividades["id"] . '">
                    </div>
                    <div class="mt-3">
                        <button class="btn bg-azul btn-primary w-100 fw-bold" id="btn_actualizar_act">Entregar actividad</button>
                    </div>
                    ';
                    $plagio = $revision['similitud']?? 'NI';
                    $nota = $revision['nota'] ?? '--';
                    $similitud = '
                    <div class="d-flex justify-content-between">
                        <p>Similitud: ' . $plagio . '%</p>
                        <p>Calificación: ' . $nota . '/20 </p>
                    </div>
                    ';
                } elseif ($revision['estado'] == 1 || $revision['estado'] == 2) {
                    if($revision['estado'] == 2){
                        $retraso = 'Con retraso: '.$revision['retraso'];
                    }else if($revision['estado'] == 1){
                        $retraso = 'Actividad entregada';
                    }
                    $template = '
                        <div class="mt-2">
                            <span>' . $retraso . '</span>
                        </div>
                        <div class="mt-3">
                            <button class="btn bg-azul btn-primary w-100 fw-bold" onclick="cancelarEntrega(' . $dataActividades["id"] . ')">Cancelar Entrega</button>
                        </div>
                    ';
                    if ($revision['similitud'] != null) {
                        $plagio = $revision['similitud'] ?? 'NI';
                        $nota = $revision['nota'] ?? '--';
                        $similitud = '
                        <div class="d-flex justify-content-between">
                            <p>Simitud: ' . $plagio . '%</p>
                            <p>Calificación: ' . $nota . '/20 </p>
                        </div>
                        ';
                        $estado = 'Analizado';
                    } else {
                        $similitud = '';
                        $estado = 'Entregado';
                    }
                }
            }
        ?>
            <div class="py-4">
                <div class="height-100 scroll px-2 bg-blanco pt-2">
                    <div class="container">
                        <div class="row">
                            <div class="col-md-7 col-sm-12 col-lg-8">
                                <div class="d-flex align-items-center fw-bold fs-3">
                                    <i class='bx bx-book-alt bg-azul p-2 circulo text-light me-2'></i>
                                    <h3><?php echo $dataActividades['titulo']; ?></h3>
                                </div>
                                <div class="mt-2">
                                    <span>Tiempo: <?php echo $dataActividades['fechai'] . " hasta " . $dataActividades['fechaf']; ?></span>
                                    <hr>
                                    <p><?php echo $dataActividades['descripcion']; ?></p>
                                    <hr>
                                    <?php echo $similitud; ?>
                                </div>
                            </div>

                            <div class="col-md-5 col-sm-12 col-lg-4">
                                <div class="tarjeta px-3 py-3 bg-blanco redondear">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <h3>Tu trabajo</h3>
                                        <span class="text-success fw-bold"><?php echo $estado; ?></span>
                                    </div>
                                    <?php echo $template; ?>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        <?php
        } else {
            echo "no autorizado";
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

function revisarActividades()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $actividad = $_POST['id'];
            $profesor = $_SESSION['idProfesor'];

            if (!validarActividadProfesor($conexion, $actividad, $profesor)) {
                die("Actividad no encontrada");
            }

            $nombreact = listarDetalleActDB($conexion, $actividad);
            $nombreact = $nombreact->fetch_assoc();
        ?>
            <div class="py-3">
                <div class="height-100 pb-3 bg-blanco pt-2">
                    <div class="container h-100">
                        <div class="h-85 scroll">
                            <div class="d-flex justify-content-between align-items-center gap-3">
                                <div class="">
                                    <h3 class="fw-bold"><?php echo $nombreact['titulo']; ?></h3>
                                </div>
                                <div class="d-flex justify-content-end gap-3">
                                    <a href="?f=reporte&id=<?php echo $actividad; ?>" class="text-dark"><i class='bx bxs-file-pdf fs-1'></i></a>
                                    <span role="button" id="btn_gemini"><i class='bx bxs-bot fs-1'></i></span>
                                </div>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span>Fecha de inicio: <?php echo $nombreact['fechai']; ?></span>
                                <span>Fecha de entrega: <?php echo $nombreact['fechaf']; ?></span>
                            </div>
                            <hr class="mb-4">
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead>
                                        <tr>
                                            <th scope="col">Alumnos</th>
                                            <th scope="col">Estado</th>
                                            <th scope="col">Similitud</th>
                                            <th scope="col">Link</th>
                                            <th scope="col">Calificar</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $dataActividades = listarActividadesDB($conexion, $actividad);
                                        while ($actividades = $dataActividades->fetch_assoc()) {
                                            //extraer del nombre solo el primer nombre
                                            $nombre = explode(" ", $actividades['nombre']);
                                            $nombre = $nombre[0];
                                            //extraer del apellido solo el primer apellido
                                            $apellido = explode(" ", $actividades['apellidos']);
                                            $apellido = $apellido[0];
                                            $nombreCompleto = $nombre . " " . $apellido;
                                            //poner mayuscula el primer caracter del nombre
                                            $nombreCompleto = ucfirst($nombreCompleto);
                                            if ($actividades['estado'] == 1) {
                                                $estado = "Entregado";
                                                if ($actividades['similitud'] == null) {
                                                    $similitud = "NI";
                                                } else {
                                                    $similitud = $actividades['similitud'];
                                                }
                                            } else if($actividades['estado'] == 2){
                                                $estado = 'Retraso: '.$actividades['retraso'];
                                                if ($actividades['similitud'] == null) {
                                                    $similitud = "NI";
                                                } else {
                                                    $similitud = $actividades['similitud'];
                                                }
                                            }else {
                                                $estado = "No entregado";
                                                $similitud = "NI";
                                            }

                                        ?>
                                            <tr>
                                                <th scope="row"><?php echo $nombreCompleto; ?></th>
                                                <td><?php echo $estado; ?></td>
                                                <td><?php echo $similitud; ?>%</td>
                                                <td>
                                                    <?php if ($actividades['estado'] != 0) { ?>
                                                        <a href="<?php echo $actividades['url']; ?>" type="button" target="_blank" class="text-azul fw-bold">Ver codigo</a>
                                                    <?php } ?>
                                                </td>
                                                <td>
                                                    <?php if ($actividades['estado'] != 0) { 
                                                        $calificar = $actividades['codigo'] . "||" .
                                                            $actividades['nota'];
                                                        ?>
                                                        <span type="button" onclick="colocarNota('<?php echo $calificar; ?>')"><?php echo $actividades['nota'] ?? '--'; ?>/20</span>
                                                    <?php }else{
                                                        echo '00/20';
                                                    } ?>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>

                        </div>

                        <div class="mt-3 d-flex gap-3">
                            <div class="">
                                <button id="btn_verificar" class="btn btn-success fw-bold">Analizar</button>
                            </div>
                            <div class="">
                                <select class="form-select" name="comboAlgoritmos" id="comboAlgoritmos">
                                    <option value="0">Seleccionar Algoritmo</option>
                                    <option value="1">Variables</option>
                                    <option value="2">Levenshtein</option>
                                    <option value="3">Coseno</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
<?php
        } else {
            echo "no autorizado";
        }
    } catch (Exception $e) {
        die("Error: " . $e->getMessage());
    }
}

if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
?>