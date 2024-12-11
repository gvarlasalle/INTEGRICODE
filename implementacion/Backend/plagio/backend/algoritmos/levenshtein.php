<?php

require_once('../../inc/conexion.php');
session_start();

try{
    
$conexion = conectar();

$idActividad = $_POST['id'];

// Consultar todas las rutas de archivos de texto asociadas a la actividad
$sql = "SELECT da.id, da.rutatxt FROM detalleact da JOIN usuarios u ON da.alumno = u.id WHERE da.actividad = '$idActividad' and da.estado IN (1, 2)";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {

    // Almacenar los datos de los archivos en un array
    while ($row = $resultado->fetch_assoc()) {
        $archivos[] = [
            'id' => $row['id'],
            'ruta' => $row['rutatxt'] // Asegurarse de usar la ruta correcta
        ];
    }
    // Función para calcular el porcentaje de similitud usando Levenshtein Distance
    function calcularSimilitudLevenshtein($archivo1, $archivo2)
    {

        // Calcular la distancia de Levenshtein
        $distancia = levenshtein($archivo1, $archivo2);

        // Calcular la longitud máxima entre ambos archivos (para calcular el porcentaje)
        $longitudMaxima = max(strlen($archivo1), strlen($archivo2));

        // Si uno de los archivos está vacío, evita la división por cero
        if ($longitudMaxima === 0) {
            return 100; // Si ambos archivos están vacíos, se consideran idénticos
        }

        // Calcular el porcentaje de similitud
        $porcentajeSimilitud = (1 - $distancia / $longitudMaxima) * 100;

        return round($porcentajeSimilitud,0);
    }

    // Función para actualizar la similitud en la base de datos
    function actualizarSimilitud($id, $similitud, $conexion)
    {
        $similitud = round($similitud, 0);
        $sql = "UPDATE detalleact SET similitud = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('si', $similitud, $id);
        $stmt->execute();
        $stmt->close();
    }


    $totalArchivos = count($archivos);

    for ($i = 0; $i < $totalArchivos; $i++) {
        $codigoBase = file_get_contents("../".$archivos[$i]['ruta']);

        if ($codigoBase === false) {
            $respuesta[] = ["status" => "error", "id" => $archivos[$i]['id'], "message" => "Error al leer el archivo: " . $archivos[$i]['ruta']];
            continue;
        }

        $similitudMaxima = 0;

        // Comparar el archivo base con todos los demás archivos (sin comparar consigo mismo)
        for ($j = 0; $j < $totalArchivos; $j++) {
            if ($i != $j) {
                $codigoComparar = file_get_contents("../".$archivos[$j]['ruta']);

                if ($codigoComparar === false) {
                    $respuesta = ["status" => "error", "id" => $archivos[$j]['id'], "message" => "Error al leer el archivo: " . $archivos[$j]['ruta']];
                    continue;
                }

                // Calcular la similitud y sumarla al total
                $similitud = calcularSimilitudLevenshtein($codigoBase, $codigoComparar);
                // Si la similitud calculada es mayor que la similitud máxima actual, la actualizamos
                if ($similitud > $similitudMaxima) {
                    $similitudMaxima = $similitud;
                }
            }
        }

        //operador ternario un if-else condicion ? verdadero : falso
        // Actualizar la similitud del archivo base en la base de datos
        actualizarSimilitud($archivos[$i]['id'], $similitudMaxima, $conexion);
    }
    $respuesta = ["status" => "success", "message" => "Todos los archivos han sido procesados correctamente"];
} else {
    $respuesta = ["status" => "error", "message" => "No se encontraron registros para la actividad con id: $idActividad"];
}

$conexion->close();

}catch(Exception $e){
    $respuesta = ["status" => "catch", "message" => $e->getMessage()];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');

echo json_encode($respuesta);


