<?php

require_once('../../inc/conexion.php');
require_once('../../vendor/autoload.php');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__, '../../inc/.env');
$dotenv->safeLoad();
session_start();



function gemini($prompt)
{
    $api = $_ENV['API'];
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=$api";

    // Datos a enviar
    $data = [
        "contents" => [
            [
                "parts" => [
                    ["text" => $prompt]
                ]
            ]
        ]
    ];

    // Configurar cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    // Ejecutar la solicitud y capturar la respuesta
    $response = curl_exec($ch);

    // Manejar errores
    if (curl_errno($ch)) {
        $error = 'Error: ' . curl_error($ch);
        curl_close($ch);
        return $error; // Devuelve el error en caso de fallo
    }

    // Cerrar cURL
    curl_close($ch);

    // Parse the JSON response
    $decoded_response = json_decode($response, true);

    return $decoded_response['candidates'][0]['content']['parts'][0]['text'];
}

try {
    $conexion = conectar();
    $idActividad = $_POST['id'];

    // Consultar todas las rutas de archivos de texto asociadas a la actividad y los nombres
    $sql = "SELECT da.id, CONCAT(u.nombre, ' ', u.apellidos) as nombre, da.rutatxt
        FROM detalleact da 
        JOIN usuarios u ON da.alumno = u.id 
        WHERE da.actividad = '$idActividad' AND da.estado IN (1, 2)
    ";
    $resultado = $conexion->query($sql);

    if ($resultado->num_rows > 0) {
        $archivos = [];

        // Almacenar los datos de los archivos en un array
        while ($row = $resultado->fetch_assoc()) {
            $archivos[] = [
                'id' => $row['id'],
                'nombre' => $row['nombre'], // Nombre del usuario
                'ruta' => $row['rutatxt']  // Ruta del archivo
            ];
        }

        // Generar el texto con formato "Nombre = Código"
        $resultadosTexto = "analísame estos códigos java y dime el porcentaje de similitud entre ellos mismos, dame el porcentaje detallado uno por uno, escribe en español ";
        foreach ($archivos as $archivo) {
            $codigo = file_get_contents("../" . $archivo['ruta']);

            if ($codigo === false) {
                $respuesta[] = [
                    "status" => "error",
                    "id" => $archivo['id'],
                    "message" => "Error al leer el archivo: " . $archivo['ruta']
                ];
                continue;
            }

            // Agregar el nombre y código formateado
            $resultadosTexto .= $archivo['nombre'] . " = " . $codigo . " - ";
        }
        //consultar a gemini
        $gemini = gemini($resultadosTexto);
        $respuesta = [
            "status" => "success",
            "message" => "Archivos procesados correctamente",
            "data" => $gemini
        ];
    } else {
        $respuesta = [
            "status" => "error",
            "message" => "No se encontraron registros para la actividad con id: $idActividad"
        ];
    }

    $conexion->close();
} catch (Exception $e) {
    $respuesta = [
        "status" => "catch",
        "message" => $e->getMessage()
    ];
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($respuesta);
