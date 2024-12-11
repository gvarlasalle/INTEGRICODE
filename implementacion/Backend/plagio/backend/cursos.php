<?php
require_once('../inc/conexion.php');
require_once('../vendor/autoload.php');

use \Firebase\JWT\JWT;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../inc/.env');
$dotenv->safeLoad();
session_start();

function detalleCurso($conexion, $idcurso)
{
    $sql = "SELECT c.id, c.nombre, c.aula, c.usuario, u.correo, u.estado, c.cod, u.nombre as profesor,CONCAT(u.nombre, ' ', u.apellidos) AS 'docente' FROM cursos c join usuarios u ON c.usuario=u.id WHERE c.id = '$idcurso' AND c.estado = 1 ORDER BY c.id ASC";
    $resultado = $conexion->query($sql);

    $detalleCurso = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $detalleCurso = $row;  // Guardamos cada noticia en un array
    }

    // Liberar el resultado
    $resultado->free();

    return $detalleCurso;
}

function actividadesDB($conexion, $idcurso)
{
    $sql = "SELECT * FROM actividades WHERE curso = '$idcurso' AND estado = 1 ORDER BY id ASC";
    $resultado = $conexion->query($sql);
    $actividades = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $actividades[] = $row;  // Guardamos cada noticia en un array
    }

    // Liberar el resultado
    $resultado->free();

    return $actividades;
}

function listarAlumnosDB($conexion, $idcurso)
{
    $sql = "SELECT CONCAT(u.nombre, ' ', u.apellidos) AS alumno, u.correo, u.id, u.estado
    FROM detallecurso dc
    JOIN usuarios u ON dc.alumno = u.id
    WHERE dc.curso = '$idcurso'";
    $resultado = $conexion->query($sql);
    $alumnos = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $alumnos[] = $row;  // Guardamos cada noticia en un array
    }

    return $alumnos;
}

function nombreProfesorDB($conexion, $idcurso)
{
    $sql = "SELECT CONCAT(u.nombre, ' ', u.apellidos) AS profesor
    FROM cursos c
    JOIN usuarios u ON c.usuario = u.id
    WHERE c.id = '$idcurso'";
    $resultado = $conexion->query($sql);
    return $resultado;
}

function listarNoticiasDB($conexion, $idcurso)
{
    $sql = "SELECT *
    FROM noticias
    WHERE curso = '$idcurso' AND estado = 1";
    $resultado = $conexion->query($sql);
    $noticias = [];

    // Fetch de todos los resultados
    while ($row = $resultado->fetch_assoc()) {
        $noticias[] = $row;  // Guardamos cada noticia en un array
    }

    // Liberar el resultado
    $resultado->free();

    return $noticias;
}

function actividadesEjemploDB($conexion, $idcurso, $alumno)
{
    $sql = "SELECT a.id, a.titulo, a.descripcion, a.fechaf, a.fechai, a.curso, a.estado  
    FROM actividades a 
    JOIN detallecurso dc ON a.curso = dc.curso 
    WHERE a.curso = '$idcurso'
    AND a.estado = 1 
    AND dc.alumno = '$alumno' 
    ORDER BY a.id ASC";
    $resultado = $conexion->query($sql);
    return $resultado;
}

require_once('validaciones.php');

function listarCursos()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $usuario = $_SESSION['idAlumno'];
            $sql = "SELECT c.id, c.usuario, c.cod, u.nombre, c.nombre as 'curso' FROM detallecurso dc JOIN cursos c on dc.curso=c.id join usuarios u on c.usuario=u.id WHERE dc.alumno='$usuario' AND c.estado=1 order by c.id ASC";
            $resultado = $conexion->query($sql);
?>
            <div class="height-100 scroll py-3">
                <div class="cursos">
                    <?php
                    while ($fila = $resultado->fetch_assoc()) {
                        $imagenprofesor = "server/usuarios/" . $fila['usuario'] . $fila['nombre'] . ".png";
                        if (!file_exists("../" . $imagenprofesor)) {
                            $imagenprofesor = "assets/images/sf.jpg";
                        }
                        $imagencurso = "server/cursos/" . $fila['usuario'] . $fila['cod'] . ".png";
                        if (!file_exists("../" . $imagencurso)) {
                            $imagencurso = "assets/images/curso.jpg";
                        }
                        $link = "curso=" . $fila['id'];
                    ?>
                        <div class="w-100 tarjeta redondear">
                            <div class="altura">
                                <img src="../../<?php echo $imagencurso; ?>" alt="logo">
                            </div>
                            <div class="foto-docente">
                                <img src="../../<?php echo $imagenprofesor; ?>" alt="logo">
                            </div>
                            <div class="linea-lateral"></div>
                            <a href="?f=detalle&<?php echo $link; ?>" type="button" class="p-3 mt-4 d-flex align-items-center text-dark flex-row">
                                <div class="me-3">
                                    <img class="carpeta" src="../../assets/images/carpeta2.png" alt="logo">
                                </div>
                                <div class="lh-1">
                                    <span class="fw-bold"><?php echo $fila['curso']; ?></span>
                                </div>
                            </a>
                        </div>
                    <?php
                    }
                    ?>
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

function listarCursosProfesor()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $usuario = $_SESSION['idProfesor'];
            $sql = "SELECT c.id, c.nombre, c.aula, c.usuario, c.cod, u.nombre AS 'profesor' FROM cursos c join usuarios u ON c.usuario=u.id WHERE c.usuario = '$usuario' AND c.estado = 1 ORDER BY c.id ASC";
            $resultado = $conexion->query($sql);
        ?>
            <div class="height-100 scroll py-3">
                <div class="cursos">
                    <?php
                    while ($fila = $resultado->fetch_assoc()) {
                        $imagenprofesor = "server/usuarios/" . $fila['usuario'] . $fila['profesor'] . ".png";
                        if (!file_exists("../" . $imagenprofesor)) {
                            $imagenprofesor = "assets/images/sf.jpg";
                        }
                        $imagencurso = "server/cursos/" . $fila['usuario'] . $fila['cod'] . ".png";
                        if (!file_exists("../" . $imagencurso)) {
                            $imagencurso = "assets/images/curso.jpg";
                        }
                        $link = "curso=" . $fila['id'] . "&cod=" . $fila['cod'];
                        $editar = $fila['id'] . "||" .
                            $fila['nombre'] . "||" .
                            $fila['aula'];
                    ?>
                        <div class="w-100 tarjeta redondear">
                            <div class="altura">
                                <img src="../../<?php echo $imagencurso; ?>" alt="curso">
                            </div>
                            <div class="foto-docente">
                                <img src="../../<?php echo $imagenprofesor; ?>" alt="img_profesor">
                            </div>
                            <div class="linea-lateral"></div>
                            <div class="p-3 mt-4 d-flex align-items-center text-dark flex-row">
                                <a href="?f=detalle&<?php echo $link; ?>" type="button" class="d-flex align-items-center text-dark flex-row">
                                    <div class="me-3">
                                        <img class="carpeta" src="../../assets/images/carpeta2.png" alt="logo">
                                    </div>
                                    <div class="lh-1">
                                        <span class="fw-bold"><?php echo $fila['nombre']; ?></span>
                                    </div>
                                </a>
                                <div class="ms-auto">
                                    <div class="dropdown">
                                        <div role="button" id="dropdownMenuLink" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class='bx bx-dots-vertical-rounded fs-4'></i>
                                        </div>

                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuLink">
                                            <li><span onclick="editarCurso('<?php echo $editar; ?>')" class="dropdown-item" role="button">Editar</span></li>
                                            <li><span onclick="eliminarCurso('<?php echo $fila['id']; ?>')" class="dropdown-item" role="button">Eliminar</span></li>
                                        </ul>
                                    </div>

                                </div>
                            </div>
                        </div>
                    <?php
                    }
                    ?>
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

function listarDetalleCursos()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $curso = $_POST['curso'];
            $alumno = $_SESSION['idAlumno'];

            //* validar que el alumno este en el curso
            if (!validarCursoAlumno($conexion, $curso, $alumno)) {
                die("El curso no existe, o no estas matriculado en el curso");
            }
            $dataActividades = actividadesDB($conexion, $curso);

            $dataCurso = detalleCurso($conexion, $curso);
            $fila = $dataCurso;
            //validar que exista el curso
            if (!$fila) {
                die("curso no encontrado");
            }
            $imagenprofesor = "server/usuarios/" . $fila['usuario'] . $fila['profesor'] . ".png";
            if (!file_exists("../" . $imagenprofesor)) {
                $imagenprofesor = "assets/images/sf.jpg";
            }
            $imagencurso = "server/cursos/" . $fila['usuario'] . $fila['cod'] . ".png";
            if (!file_exists("../" . $imagencurso)) {
                $imagencurso = "assets/images/curso.jpg";
            }
            $nombre_curso = $fila['nombre'];
            $nombre_profesor = $fila['docente'];
            $correo_profesor = $fila['correo'];
        ?>
            <div class="py-4">
                <div class="tabs">

                    <input type="radio" id="tab1" name="tab-control" checked>
                    <input type="radio" id="tab2" name="tab-control">
                    <input type="radio" id="tab3" name="tab-control">
                    <ul>
                        <li title="Curso">
                            <label for="tab1" role="button">
                                <i class='bx bxs-graduation'></i><br>
                                <span>Curso</span>
                            </label>
                        </li>
                        <li title="Actividades">
                            <label for="tab2" role="button">
                                <i class='bx bx-book-alt'></i><br>
                                <span>Actividades</span>
                            </label>
                        </li>
                        <li title="Alumnos">
                            <label for="tab3" role="button">
                                <i class='bx bxs-user'></i><br>
                                <span>Alumnos</span>
                            </label>
                        </li>
                    </ul>

                    <div class="slider">
                        <div class="indicator"></div>
                    </div>
                    <div class="content">
                        <section>
                            <h2>Curso</h2>
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-3 col-sm-12 col-lg-2 mb-3">
                                    </div>

                                    <div class="col-md-9 col-sm-12 col-lg-10">
                                        <div class="card redondear text-white mb-3">
                                            <img src="../../<?php echo $imagencurso; ?>" class="card-img-top banner-curso redondear" alt="curso">
                                            <div class="card-img-overlay bg-fondo redondear">
                                                <div class="text-bottom d-flex align-items-end h-100">
                                                    <div>
                                                        <h4 class="card-title fw-bold"><?php echo $fila['nombre']; ?></h4>
                                                        <p class="card-text"><?php echo $fila['aula']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $dataNoticias = listarNoticiasDB($conexion, $curso);
                                        foreach ($dataNoticias as $noticia) {
                                        ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <img class="header_img" src="../../<?php echo $imagenprofesor; ?>" alt="logo">
                                                        </div>
                                                        <div>
                                                            <span class="fw-bold"><?php echo $nombre_curso; ?></span><br>
                                                            <span class="text-muted"><?php echo $noticia['fecha'] ?></span>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <?php echo $noticia['noticia'] ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                        </section>
                        <section>
                            <h2>Actividades</h2>
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <?php
                                if (count($dataActividades) > 0) {
                                    foreach ($dataActividades as $actividades) { ?>
                                        <div class="accordion-item">
                                            <div class="accordion-header">
                                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="<?php echo "#act" . $actividades['id']; ?>" aria-expanded="false" aria-controls="flush-collapseOne">
                                                    <?php echo $actividades['titulo']; ?>
                                                </button>
                                            </div>
                                            <div id="<?php echo "act" . $actividades['id']; ?>" class="accordion-collapse collapse w-100" data-bs-parent="#accordionFlushExample">
                                                <div class="accordion-body">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <span class="text-muted">Publicado: <?php echo $actividades['fechai']; ?></span>
                                                        <span class="text-success fw-bold">Asignado</span>
                                                    </div>
                                                    <div class="mt-4">
                                                        <p><?php echo $actividades['descripcion']; ?></p>
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="?f=instrucciones&id=<?php echo $actividades['id']; ?>" type="button" class="btn btn-primary">Subir Actividad</a>
                                                </div>
                                            </div>
                                        </div>
                                <?php }
                                } else {
                                    echo "no hay actividades";
                                } ?>
                            </div>
                        </section>
                        <section>
                            <h2>Alumnos</h2>
                            <div>
                                <div class="pb-4">
                                    <h3 class="fw-bold">Profesor</h3>
                                    <hr>
                                    <div class="d-flex align-items-center justify-content-between border-bottom py-1 pe-5">
                                        <span><?php echo $nombre_profesor; ?></span>
                                        <div class="d-flex align-items-center">
                                            <?php if ($fila['estado'] == 0) { ?>
                                                <span role="button" data-bs-toggle="tooltip" data-bs-placement="top" title="El profesor eliminó su cuenta" class="badge bg-danger text-white me-3 rounded-pill">usuario eliminado</span>
                                            <?php } ?>
                                            <a class="text-dark" href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $correo_profesor; ?>" target="_blank" role="button"><i class='bx bxs-envelope fs-4'></i></a>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <div class="d-flex align-items-center justify-content-between pb-0 mb-0 pe-5">
                                        <h3 class="fw-bold">Alumnos</h3>
                                        <button onclick="salirDelCurso()" class="btn btn-outline-danger btn-sm"><i class='bx bx-exit'></i> <span class="text-boton">Salir del curso</span></button>
                                    </div>
                                    <hr>
                                    <?php
                                    $dataAlumnos = listarAlumnosDB($conexion, $curso);

                                    foreach ($dataAlumnos as $alumnos) { ?>

                                        <div class="d-flex align-items-center justify-content-between border-bottom py-2 pe-5">
                                            <span><?php echo $alumnos['alumno'] ?></span>
                                            <div>
                                                <a class="text-dark" href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $alumnos['correo']; ?>" target="_blank" role="button"><i class='bx bxs-envelope fs-4'></i></a>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        </section>
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

function listarDetalleCursoProfesor()
{
    try {
        if ($_POST['tk'] == $_SESSION['token']) {
            $conexion = conectar();
            $curso = $_POST['curso'];
            $profesor = $_SESSION['idProfesor'];

            if (!validarCursoProfesor($conexion, $curso, $profesor)) {
                die("Curso no encontrado");
            }

            $dataActividades = actividadesDB($conexion, $curso);

            $dataCurso = detalleCurso($conexion, $curso);
            $fila = $dataCurso;
            //validar que exista el curso
            if (!$fila) {
                die("curso no encontrado");
            }
            $imagenprofesor = "server/usuarios/" . $fila['usuario'] . $fila['profesor'] . ".png";
            if (!file_exists("../" . $imagenprofesor)) {
                $imagenprofesor = "assets/images/sf.jpg";
            }
            $imagencurso = "server/cursos/" . $fila['usuario'] . $fila['cod'] . ".png";
            if (!file_exists("../" . $imagencurso)) {
                $imagencurso = "assets/images/curso.jpg";
            }
            $nombre_curso = $fila['nombre'];
            $nombre_profesor = $fila['docente'];
        ?>
            <div class="py-4">
                <div class="tabs">

                    <input type="radio" id="tab1" name="tab-control" checked>
                    <input type="radio" id="tab2" name="tab-control">
                    <input type="radio" id="tab3" name="tab-control">
                    <ul>
                        <li title="Curso">
                            <label for="tab1" role="button">
                                <i class='bx bxs-graduation'></i><br>
                                <span>Curso</span>
                            </label>
                        </li>
                        <li title="Actividades">
                            <label for="tab2" role="button">
                                <i class='bx bx-book-alt'></i><br>
                                <span>Actividades</span>
                            </label>
                        </li>
                        <li title="Alumnos">
                            <label for="tab3" role="button">
                                <i class='bx bxs-user'></i><br>
                                <span>Alumnos</span>
                            </label>
                        </li>
                    </ul>

                    <div class="slider">
                        <div class="indicator"></div>
                    </div>
                    <div class="content">
                        <section>
                            <h2>Curso</h2>
                            <div class="container">
                                <div class="row">
                                    <div class="col-lg-2 col-md-3 col-sm-12 mb-3">
                                        <div class="card">
                                            <div class="card-body fw-bold">
                                                <span>Código de curso</span>
                                                <div class="mt-3">
                                                    <span role="button" onclick="copiarCodigo('<?php echo $fila['cod']; ?>')" class="text-azul arial"><?php echo $fila['cod']; ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-10 col-md-9 col-sm-12">
                                        <div class="card redondear text-white mb-3">
                                            <img id="cursoImagen" src="../../<?php echo $imagencurso; ?>" class="card-img-top banner-curso redondear" alt="curso">
                                            <div class="card-img-overlay bg-fondo redondear">
                                                <div class="">
                                                    <div class="text-end">
                                                        <input type="file" id="fotoCurso" name="fotoCurso" accept="image/*" class="d-none">
                                                        <label for="fotoCurso" class="btn btn-light fw-bold">Imagen</label>
                                                    </div>
                                                </div>
                                                <div class="text-bottom d-flex align-items-end h-75">
                                                    <div>
                                                        <h4 class="card-title fw-bold"><?php echo $fila['nombre']; ?></h4>
                                                        <p class="card-text"><?php echo $fila['aula']; ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="card mb-3">
                                            <div class="card-body">
                                                <div class="form-floating">
                                                    <textarea class="form-control shadow-none" placeholder="Escribir una noticia" id="txtNoticia"></textarea>
                                                    <label for="txtNoticia">Anunciar noticia</label>
                                                </div>
                                                <div class="mt-3">
                                                    <button class="btn bg-azul fw-bold btn-primary" id="btn_add_noticia">Publicar</button>
                                                </div>
                                            </div>
                                        </div>

                                        <?php
                                        $dataNoticias = listarNoticiasDB($conexion, $curso);
                                        foreach ($dataNoticias as $noticia) {
                                            $editar_noticia = $noticia['id'] . "||" .
                                                $noticia['noticia'];
                                        ?>
                                            <div class="card mb-3">
                                                <div class="card-body">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center">
                                                            <div class="me-2">
                                                                <img class="header_img" src="../../<?php echo $imagenprofesor; ?>" alt="logo">
                                                            </div>
                                                            <div>
                                                                <span class="fw-bold"><?php echo $nombre_curso; ?></span><br>
                                                                <span class="text-muted"><?php echo $noticia['fecha']; ?></span>
                                                            </div>
                                                        </div>
                                                        <div>
                                                            <div class="desplegable">
                                                                <div>
                                                                    <i onclick="toggleDropdown(event)" class='bx bx-dots-vertical-rounded fs-4'></i>
                                                                </div>

                                                                <div class="desplegable-content">
                                                                    <a onclick="editarNoticia('<?php echo $editar_noticia; ?>')" role="button">Editar</a>
                                                                    <a onclick="eliminarNoticia('<?php echo $noticia['id']; ?>')" role="button">Eliminar</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mt-3">
                                                        <?php echo $noticia['noticia']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
                            </div>

                        </section>
                        <section>
                            <h2>Actividades</h2>
                            <div class="pt-2 pb-2">
                                <button class="btn bg-azul btn-primary fw-bold redondear" data-bs-toggle="modal" data-bs-target="#addActividad">+ Crear</button>
                                <hr>
                            </div>
                            <div class="accordion accordion-flush" id="accordionFlushExample">
                                <?php foreach ($dataActividades as $actividades) {
                                    $editar = $actividades['id'] . "||" .
                                        $actividades['titulo'] . "||" .
                                        $actividades['descripcion'] . "||" .
                                        $actividades['fechaf'];
                                ?>
                                    <div class="accordion-item">
                                        <div class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="<?php echo "#act" . $actividades['id']; ?>" aria-expanded="false" aria-controls="flush-collapseOne">
                                                <?php echo $actividades['titulo']; ?>
                                            </button>
                                        </div>
                                        <div id="<?php echo "act" . $actividades['id']; ?>" class="accordion-collapse collapse w-100" data-bs-parent="#accordionFlushExample">
                                            <div class="accordion-body">
                                                <div class="d-flex align-items-center justify-content-between">
                                                    <span class="text-muted">Publicado: <?php echo $actividades['fechai']; ?></span>
                                                    <span class="text-success fw-bold">Asignado</span>
                                                </div>
                                                <div class="mt-4">
                                                    <p><?php echo $actividades['descripcion']; ?></p>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="d-flex align-items-center text-dark flex-row gap-2">
                                                    <div>
                                                        <a href="?f=revisar&id=<?php echo $actividades['id']; ?>" type="button" class="btn btn-primary">Revisar actividad</a>
                                                    </div>
                                                    <div>
                                                        <button onclick="eliminarActividad('<?php echo $actividades['id']; ?>')" class="btn btn-danger"><i class='bx bx-trash-alt'></i></button>
                                                    </div>
                                                    <div>
                                                        <button onclick="editarActividad('<?php echo $editar; ?>')" class="btn btn-warning"><i class='bx bx-edit-alt'></i></button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </section>
                        <section>
                            <h2>Alumnos</h2>
                            <div>
                                <div class="pb-4">
                                    <h3 class="fw-bold">Profesor</h3>
                                    <hr>
                                    <span><?php echo $nombre_profesor; ?></span>
                                </div>

                                <div>
                                    <h3 class="fw-bold">Alumnos</h3>
                                    <hr>
                                    <?php
                                    $dataAlumnos = listarAlumnosDB($conexion, $curso);
                                    foreach ($dataAlumnos as $alumno) { ?>
                                        <div class="d-flex align-items-center justify-content-between border-bottom py-1 pe-5">
                                            <span><?php echo $alumno['alumno'] ?></span>
                                            <div class="d-flex align-items-center">
                                                <?php if ($alumno['estado'] == 0) { ?>
                                                    <span role="button" data-bs-toggle="tooltip" data-bs-placement="top" title="El alumno eliminó su cuenta" class="badge bg-danger text-white me-3 rounded-pill">usuario eliminado</span>
                                                <?php } ?>
                                                <div class="desplegable">
                                                    <div>
                                                        <i onclick="toggleDropdown(event)" class='bx bx-dots-vertical-rounded fs-4'></i>
                                                    </div>

                                                    <div class="desplegable-content">
                                                        <a href="https://mail.google.com/mail/?view=cm&fs=1&to=<?php echo $alumno['correo']; ?>" target="_blank" role="button">Enviar correo</a>
                                                        <a onclick="quitarAlumno('<?php echo $alumno['id']; ?>')" role="button">Quitar</a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>

                            </div>
                        </section>
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