<?php
require_once('../../inc/conexion.php');
session_start();

try{
    set_time_limit(300); // 5 minutos
ini_set('memory_limit', '256M'); // Aumentar límite de memoria

$conexion = conectar();

$idActividad = $_POST['id'];

// Consultar todas las rutas de archivos de texto asociadas a la actividad
$sql = "SELECT da.id, da.rutatxt FROM detalleact da JOIN usuarios u ON da.alumno = u.id WHERE da.actividad = '$idActividad' AND da.estado IN (1, 2)";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    $archivos = [];
    while ($row = $resultado->fetch_assoc()) {
        $archivos[] = [
            'id' => $row['id'],
            'ruta' => $row['rutatxt']
        ];
    }

    function cosineSimilarity($text1, $text2) {
        $vector1 = array_count_values(str_word_count($text1, 1));
        $vector2 = array_count_values(str_word_count($text2, 1));

        $dotProduct = 0;
        $normA = 0;
        $normB = 0;

        foreach ($vector1 as $key => $value) {
            $dotProduct += $value * ($vector2[$key] ?? 0);
            $normA += $value ** 2;
        }
        foreach ($vector2 as $value) {
            $normB += $value ** 2;
        }

        return ($normA * $normB) > 0 ? ($dotProduct / sqrt($normA * $normB)) * 100 : 0;
    }

    function actualizarSimilitud($id, $similitud, $conexion) {
        $sql = "UPDATE detalleact SET similitud = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('di', $similitud, $id); // Cambia 'si' a 'di' para permitir decimales
        $stmt->execute();
        $stmt->close();
    }

    $totalArchivos = count($archivos);
    $respuesta = [];

    foreach ($archivos as $i => $archivoActual) {
        try {
            $codigoBase = @file_get_contents("../".$archivoActual['ruta']);
            if ($codigoBase === false) {
                $respuesta[] = ["status" => "error", "id" => $archivoActual['id'], "message" => "Error al leer el archivo: " . $archivoActual['ruta']];
                continue; // Saltar a la siguiente iteración
            }

            $similitudMaxima = 0;

            foreach ($archivos as $j => $archivoComparar) {
                if ($i !== $j) {
                    $codigoComparar = @file_get_contents("../".$archivoComparar['ruta']);
                    if ($codigoComparar === false) {
                        $respuesta[] = ["status" => "error", "id" => $archivoComparar['id'], "message" => "Error al leer el archivo: " . $archivoComparar['ruta']];
                        continue;
                    }
                    
                    // Calcular la similitud de Coseno
                    $similitud = cosineSimilarity($codigoBase, $codigoComparar);
                    if ($similitud > $similitudMaxima) {
                        $similitudMaxima = $similitud;
                    }
                }
            }
            actualizarSimilitud($archivoActual['id'], round($similitudMaxima, 0), $conexion);
        } catch (Exception $e) {
            error_log("Error procesando archivo: " . $e->getMessage());
        }
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
