<?php

require_once("../../../inc/conexion.php");
require_once("variables.php");


function data()
{
    try {
        $con = conectar();
        $idActividad = $_POST['id'];
        $similitudes = analizarSimilitudes($con, $idActividad);

        $top3 = [];

        foreach ($similitudes as $alumno) {
            // Inicializar los máximos para este alumno
            $mayores = [
                'variables' => 0,
                'levenshtein' => 0,
                'coseno' => 0,
            ];

            foreach ($alumno['similitudes'] as $similitud) {
                // Comparar y mantener el valor máximo para cada métrica
                $mayores['variables'] = max($mayores['variables'], $similitud['variables']);
                $mayores['levenshtein'] = max($mayores['levenshtein'], $similitud['levenshtein']);
                $mayores['coseno'] = max($mayores['coseno'], $similitud['coseno']);
            }

            // Guardar los datos procesados del alumno
            $sumaTotal = array_sum($mayores);
            $top3[] = [
                'nombre' => $alumno['alumno'],
                'variables' => $mayores['variables'],
                'levenshtein' => $mayores['levenshtein'],
                'coseno' => $mayores['coseno'],
                'sumaTotal' => $sumaTotal,
            ];
        }

        // Ordenar por la mayor similitud de 'variables'
        usort($top3, function ($a, $b) {
            return $b['sumaTotal'] <=> $a['sumaTotal'];
        });

        // Devolver solo el top 3
        echo json_encode(array_slice($top3, 0, 3));
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}


if (function_exists($_GET['f'])) {
    $_GET['f'](); //llama la función si es que existe
}
