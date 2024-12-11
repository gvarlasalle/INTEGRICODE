<?php

function analizarSimilitudes($conexion, $idActividad) {
    try {

        // Consultar todas las rutas de archivos de texto asociadas a la actividad
        $sql = "SELECT da.id, da.rutatxt, CONCAT(u.nombre, ' ', u.apellidos) AS alumno 
                FROM detalleact da 
                JOIN usuarios u ON da.alumno = u.id 
                WHERE da.actividad = ? AND da.estado IN (1, 2)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param("i", $idActividad); // Asegúrate de que el ID sea un entero
        $stmt->execute();
        $resultado = $stmt->get_result();

        // Verificar si la consulta trajo resultados
        if ($resultado->num_rows > 0) {
            $archivos = [];
            while ($row = $resultado->fetch_assoc()) {
                $archivos[] = [
                    'id' => $row['id'],
                    'ruta' => $row['rutatxt'],
                    'alumno' => $row['alumno']
                ];
            }

            // Función para extraer variables de un fragmento de código
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

            // Función para calcular la similitud de Levenshtein en porcentaje
            function levenshteinSimilarity($text1, $text2) {
                $distance = levenshtein($text1, $text2);
                $maxLength = max(strlen($text1), strlen($text2));
                return $maxLength > 0 ? (1 - $distance / $maxLength) * 100 : 0;
            }

            // Función para calcular la similitud del coseno en porcentaje
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

            // Crear el array final con las similitudes
            $similitudes = [];
            $totalArchivos = count($archivos);

            for ($i = 0; $i < $totalArchivos; $i++) {
                $codigoBase = file_get_contents("../../" . $archivos[$i]['ruta']);
                $variablesBase = extractVariables($codigoBase);

                $result = [
                    'alumno' => $archivos[$i]['alumno'],
                    'similitudes' => []
                ];

                for ($j = 0; $j < $totalArchivos; $j++) {
                    if ($i != $j) {
                        $codigoComparar = file_get_contents("../../" . $archivos[$j]['ruta']);
                        $variablesComparar = extractVariables($codigoComparar);

                        // Calcular las tres similitudes
                        $similitudVariables = compareVariables($variablesBase, $variablesComparar);
                        $similitudLevenshtein = levenshteinSimilarity($codigoBase, $codigoComparar);
                        $similitudCoseno = cosineSimilarity($codigoBase, $codigoComparar);

                        // Agregar al array de similitudes
                        $result['similitudes'][] = [
                            'alumno' => $archivos[$j]['alumno'],
                            'variables' => round($similitudVariables, 0),
                            'levenshtein' => round($similitudLevenshtein, 0),
                            'coseno' => round($similitudCoseno, 0)
                        ];
                    }
                }

                $similitudes[] = $result;
            }
        } else {
            $similitudes = [];
        }

        $conexion->close();
        return $similitudes;

    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}

function traerTitulo($conexion, $idActividad){
    $sql = "SELECT titulo from actividades WHERE id='$idActividad' AND estado=1";
    $resultado = $conexion->query($sql);
    $result = $resultado->fetch_assoc();
    $titulo = $result['titulo'];
    return $titulo;
}

function traerTop($similitudes)
{
    try {

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
            $sumaTotal = round(array_sum($mayores) / 3, 0);
            if($sumaTotal >= 0 && $sumaTotal <= 35){
                $riesgo = 'BAJO';
            }else if($sumaTotal > 35 && $sumaTotal <= 65){
                $riesgo = 'MEDIO';
            }else if($sumaTotal > 65 && $sumaTotal <= 100){
                $riesgo = 'ALTO';
            }
            $top3[] = [
                'nombre' => $alumno['alumno'],
                'sumaTotal' => $sumaTotal,
                'riesgo' => $riesgo,
            ];
        }

        // Ordenar por la mayor similitud de 'variables'
        usort($top3, function ($a, $b) {
            return $b['sumaTotal'] <=> $a['sumaTotal'];
        });

        // Devolver solo el top 3
        return array_slice($top3, 0, 3);
    } catch (Exception $e) {
        return ['status' => 'error', 'message' => $e->getMessage()];
    }
}
