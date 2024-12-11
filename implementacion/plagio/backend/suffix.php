<?php

require_once('../inc/conexion.php');
session_start();

$conexion = conectar();

$idActividad = $_POST['id'];

// Consultar todas las rutas de archivos de texto asociadas a la actividad
$sql = "SELECT da.id, da.rutatxt FROM detalleact da JOIN usuarios u ON da.alumno = u.id WHERE da.actividad = '$idActividad' and da.estado = 1";
$resultado = $conexion->query($sql);

if ($resultado->num_rows > 0) {
    // Almacenar los datos de los archivos en un array
    $archivos = [];
    while ($row = $resultado->fetch_assoc()) {
        $archivos[] = [
            'id' => $row['id'],
            'ruta' => $row['rutatxt']
        ];
    }

    // Definir la clase Suffix Tree Node
    class SuffixTreeNode {
        public $children = [];
        public $end = false;

        public function insertSuffix($suffix) {
            $current = $this;
            foreach (str_split($suffix) as $char) {
                if (!isset($current->children[$char])) {
                    $current->children[$char] = new SuffixTreeNode();
                }
                $current = $current->children[$char];
            }
            $current->end = true;
        }
    }

    // Definir la clase Suffix Tree
    class SuffixTree {
        private $root;

        public function __construct($text) {
            $this->root = new SuffixTreeNode();
            for ($i = 0; $i < strlen($text); $i++) {
                $this->root->insertSuffix(substr($text, $i));
            }
        }

        public function getRoot() {
            return $this->root;
        }
    }

    // Función para comparar dos árboles de sufijos
    function compararArbolesDeSufijos($node1, $node2) {
        $coincidencias = 0;

        if (!$node1 || !$node2) {
            return 0;
        }

        foreach ($node1->children as $key => $child1) {
            if (isset($node2->children[$key])) {
                $coincidencias++;
                $coincidencias += compararArbolesDeSufijos($child1, $node2->children[$key]);
            }
        }

        return $coincidencias;
    }

    // Función para calcular la similitud basada en árboles de sufijos
    function calcularSimilitudArbolSufijo($codigo1, $codigo2) {
        if (strlen($codigo1) === 0 || strlen($codigo2) === 0) {
            return 0;
        }

        $tree1 = new SuffixTree($codigo1);
        $tree2 = new SuffixTree($codigo2);

        $coincidencias = compararArbolesDeSufijos($tree1->getRoot(), $tree2->getRoot());

        // Calcular el número total de sufijos en ambos textos
        $sufijosTotales = (strlen($codigo1) * (strlen($codigo1) + 1)) / 2 + (strlen($codigo2) * (strlen($codigo2) + 1)) / 2;

        // Evitar que la similitud exceda el 100%
        $porcentajeSimilitud = ($coincidencias / $sufijosTotales) * 100;

        return round($porcentajeSimilitud, 0); // Asegurarse que nunca supere 100
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

    $totalArchivos = count($archivos);

    for ($i = 0; $i < $totalArchivos; $i++) {
        $codigoBase = file_get_contents($archivos[$i]['ruta']);

        if ($codigoBase === false) {
            $respuesta[] = ["status" => "error", "id" => $archivos[$i]['id'], "message" => "Error al leer el archivo: " . $archivos[$i]['ruta']];
            continue;
        }

        $similitudMaxima = 0;

        // Comparar el archivo base con todos los demás archivos (sin comparar consigo mismo)
        for ($j = 0; $j < $totalArchivos; $j++) {
            if ($i != $j) {
                $codigoComparar = file_get_contents($archivos[$j]['ruta']);

                if ($codigoComparar === false) {
                    $respuesta[] = ["status" => "error", "id" => $archivos[$j]['id'], "message" => "Error al leer el archivo: " . $archivos[$j]['ruta']];
                    continue;
                }

                // Calcular la similitud usando el Árbol de Sufijos
                $similitud = calcularSimilitudArbolSufijo($codigoBase, $codigoComparar);
                if ($similitud > $similitudMaxima) {
                    $similitudMaxima = $similitud;
                }
            }
        }


        // Actualizar la similitud del archivo base en la base de datos
        actualizarSimilitud($archivos[$i]['id'], $similitudMaxima, $conexion);
    }

    $respuesta = ["status" => "success", "message" => "Todos los archivos han sido procesados correctamente"];
} else {
    $respuesta = ["status" => "error", "message" => "No se encontraron registros para la actividad con id: $idActividad"];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($respuesta);

$conexion->close();

?>
