<?php
require_once('../../inc/conexion.php');
session_start();

try{
    $conexion = conectar();

$idActividad = $_POST['id'];

// Consultar todas las rutas de archivos de texto asociadas a la actividad
$sql = "SELECT da.id, da.rutatxt FROM detalleact da JOIN usuarios u ON da.alumno = u.id WHERE da.actividad = '$idActividad' and da.estado IN (1, 2)";
$resultado = $conexion->query($sql);

// Verificar si la consulta trajo resultados
if ($resultado->num_rows > 0) {
    $archivos = [];

    // Almacenar los datos de los archivos en un array
    while ($row = $resultado->fetch_assoc()) {
        $archivos[] = [
            'id' => $row['id'],
            'ruta' => $row['rutatxt'] // Asegurarse de usar la ruta correcta
        ];
    }

    // Función para extraer variables de un fragmento de código Java
    function extractVariables($code) {
        $pattern = '/\b(?:int|float|double|boolean|char|String|long|short|byte)\s+([a-zA-Z_]\w*)\s*(?:[=;,\)])/';
        preg_match_all($pattern, $code, $matches);
        return $matches[1];
    }

    // Función para calcular el porcentaje de similitud de las variables
    function compareVariables($vars1, $vars2) {
        $set1 = array_unique($vars1);
        $set2 = array_unique($vars2);

        $commonVars = array_intersect($set1, $set2);
        $numCommon = count($commonVars);

        $totalVars = count($set1) + count($set2) - $numCommon;

        return $totalVars > 0 ? ($numCommon / $totalVars) * 100 : 0;
    }

    // Función para actualizar la similitud en la base de datos
    function actualizarSimilitud($id, $similitud, $conexion) {
        $similitud = round($similitud, 0);
        $sql = "UPDATE detalleact SET similitud = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('si', $similitud, $id);
        $stmt->execute();
        $stmt->close();
    }

    // Bucle para comparar cada archivo con los demás y calcular la similitud promedio
    $totalArchivos = count($archivos);

    for ($i = 0; $i < $totalArchivos; $i++) {
        $codigoBase = file_get_contents("../".$archivos[$i]['ruta']);

        if ($codigoBase === false) {
            $respuesta[] = ["status" => "error", "id" => $archivos[$i]['id'], "message" => "Error al leer el archivo: " . $archivos[$i]['ruta']];
            continue;
        }

        // Extraer variables del archivo base
        $variablesBase = extractVariables($codigoBase);
        $similitudMaxima = 0;

        // Comparar el archivo base con todos los demás archivos (sin comparar consigo mismo)
        for ($j = 0; $j < $totalArchivos; $j++) {
            if ($i != $j) {
                $codigoComparar = file_get_contents("../".$archivos[$j]['ruta']);

                if ($codigoComparar === false) {
                    $respuesta = ["status" => "error", "id" => $archivos[$j]['id'], "message" => "Error al leer el archivo: " . $archivos[$j]['ruta']];
                    continue;
                }

                // Extraer variables del archivo a comparar
                $variablesComparar = extractVariables($codigoComparar);

                // Calcular la similitud y sumarla al total
                $similitud = compareVariables($variablesBase, $variablesComparar);
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


?>
