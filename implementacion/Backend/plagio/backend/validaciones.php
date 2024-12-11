<?php

function validarCursoAlumno($conexion, $curso, $alumno){
    $sql = "SELECT alumno FROM detallecurso WHERE curso='$curso' AND alumno='$alumno'";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function validarCursoProfesor($conexion, $curso, $profesor)
{
    $sql = "SELECT nombre FROM cursos WHERE id='$curso' AND usuario='$profesor' and estado=1";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function validarActividadAlumno($conexion, $actividad, $alumno){
    $sql = "SELECT a.titulo FROM actividades a join detallecurso dc on a.curso=dc.curso WHERE a.id='$actividad' AND dc.alumno='$alumno' AND a.estado=1";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function validarActividadProfesor($conexion, $actividad, $profesor){
    $sql = "SELECT a.titulo FROM actividades a join cursos c on a.curso=c.id WHERE a.id='$actividad' AND c.usuario='$profesor' AND a.estado=1";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}

function validarNoticiaProfesor($conexion, $noticia, $profesor){
    $sql = "SELECT n.id from noticias n join cursos c on n.curso=c.id WHERE c.usuario='$profesor' AND n.id='$noticia'";
    $resultado = $conexion->query($sql);
    if ($resultado->num_rows > 0) {
        return true;
    } else {
        return false;
    }
}