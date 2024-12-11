<?php
utf8_decode('áéíóúñÁÉÍÓÚÑ');
setlocale(LC_ALL, 'es_ES');
require_once('../../../assets/libs/fpdf/fpdf.php');
require_once("../../../inc/conexion.php");
require_once("../model/variables.php");
require_once("../../validaciones.php");
session_start();

$con = conectar();
date_default_timezone_set("America/Lima");
$fechad = getdate();
$fecha = "Fecha: " . $fechad['mday'] . "-" . $fechad['mon'] . "-" . $fechad['year'];
function money(float $valor, string $simbolo = 'S/.'): string
{
    return $simbolo . number_format($valor, 2, '.', ',');
}
$actividad = $_GET['id'];
$idProfesor = $_SESSION['idProfesor'];

if (!empty(trim($_POST['imagen']))) {
    $imagen = $_POST['imagen'];
} else {
    die("No se gnero el grafico");
}

if (!validarActividadProfesor($con, $actividad, $idProfesor)) {
    die("Actividad no encontrada");
}



class PDF extends FPDF
{
    // Cabecera de página
    public function Header()
    {
        $this->SetY(10);
        $this->SetFont('Arial', 'B', 40);
        $this->SetFillColor(0, 0, 0);
        $this->Rect(0, 0, 210, 30, 'F');
        $this->SetTextColor(66, 139, 255);
        $this->SetDrawColor(66, 139, 255);
        $this->SetLineWidth(1.5);
        $this->Rect(10, 7.5, 15, 15, 'D');
        $this->SetX(15);
        $this->Write(11, 'I');
        $this->SetX(28);
        $this->SetFont('Arial', 'B', 16);
        $this->Write(11, 'INTEGRICODE');

        $this->SetLineWidth(0.5);
        $this->Line(76, 6, 76, 22);
        $this->SetX(79);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(255, 255, 255);
        $this->Write(0, 'Sistema antiplagio');
        $this->Ln();
        $this->SetX(79);
        $this->Write(9, 'Lima - Peru');
        $this->Ln();
        $this->SetX(79);
        $this->Write(0, '07076');

        $this->SetLineWidth(0.5);
        $this->Line(123, 6, 123, 22);
        $this->SetX(126);
        $this->SetFont('Arial', '', 9);
        $this->SetTextColor(255, 255, 255);
        $this->Write(-18, 'Telefono: 123456789');
        $this->Ln();
        $this->SetX(126);
        $this->Write(27, 'Correo: integricodecorreo@gmail.com');
        $this->Ln();
        $this->SetX(126);
        $this->Write(-18, 'Web: www.integricode.com');
        $this->SetY(35);
        $this->Cell(0, 0, '', 0, 0, 'C', 0);
    }

    // Pie de página
    public function Footer()
    {
        $this->SetFont('Arial', 'B', 40);
        $this->SetFillColor(66, 139, 255);
        $this->Rect(0, 277, 210, 20, 'F');
        $this->SetY(-15);
        $this->SetFont('Arial', '', 16);
        $this->SetTextColor(255, 255, 255);
        $this->SetX(60);
        $this->Write(11, 'GRACIAS POR LEER EL REPORTE');
    }
}

$fpdf = new PDF();
$fpdf->AddPage();
$fpdf->SetFont('Arial', 'B', 18);
$fpdf->SetTextColor(0, 0, 0);
$fpdf->SetMargins(10, 30, 20, 20);
$fpdf->SetFillColor(255, 255, 255);

$fpdf->Ln();
$fpdf->Cell(100, 10, utf8_decode('REPORTE DE SIMILITUD'), 0, 0, 'L', 1);
$fpdf->Cell(90, 10, utf8_decode($fecha), 0, 0, 'R', 1);
$fpdf->Ln(20);
$fpdf->SetFont('Arial', 'B', 14);

$titulo = traerTitulo($con, $actividad);
$fpdf->Cell(190, 10, strtoupper(utf8_decode($titulo)), 0, 0, 'C', 1);
$fpdf->Ln(20);

//*data de los usuarios
$data = analizarSimilitudes($con, $actividad);
//ancho total 190
foreach ($data as $similitud) {
    $fpdf->SetFillColor(255, 255, 255);
    $fpdf->SetTextColor(0, 0, 0);
    $fpdf->SetDrawColor(185, 185, 185);
    $fpdf->SetFont('Arial', 'B', 11);

    $fpdf->Cell(109, 8, strtoupper(utf8_decode('Reporte de ' . $similitud['alumno'])), 0, 0, 'S', 1);
    $fpdf->SetFillColor(66, 139, 255);
    $fpdf->SetTextColor(255, 255, 255);
    $fpdf->Cell(81, 8, 'ALGORITMOS DE SIMILITUD', 1, 0, 'C', 1);

    $fpdf->Ln();

    $fpdf->Cell(109, 8, ' ESTUDIANTE', 1, 0, 'S', 1);
    $fpdf->Cell(28, 8, 'VARIABLES', 1, 0, 'C', 1);
    $fpdf->Cell(31, 8, 'LEVENSHTEIN', 1, 0, 'C', 1);
    $fpdf->Cell(22, 8, 'COSENO', 1, 0, 'C', 1);
    $fpdf->Ln();

    $fpdf->SetDrawColor(185, 185, 185);
    $fpdf->SetTextColor(0, 0, 0);
    $fpdf->SetFillColor(255, 255, 255);
    $fpdf->SetFont('Arial', '', 11);

    foreach ($similitud['similitudes'] as $porcentaje) {
        $fpdf->Cell(109, 8, utf8_decode($porcentaje['alumno']), 1, 0, 'L', 1); // Nombre alineado a la izquierda
        $fpdf->Cell(28, 8, utf8_decode($porcentaje['variables'] . "%"), 1, 0, 'C', 1); // Edad alineada al centro
        $fpdf->Cell(31, 8, utf8_decode($porcentaje['levenshtein'] . "%"), 1, 0, 'C', 1); // Otro valor alineado al centro
        $fpdf->Cell(22, 8, utf8_decode($porcentaje['coseno'] . "%"), 1, 0, 'C', 1); // Otro valor alineado al centro
        $fpdf->Ln();
    }
    $fpdf->Ln();
}

//*grafico
$posY = $fpdf->GetY();
$alturaGrafico = 70; // Altura del gráfico en mm
$espacioDisponible = 297 - $posY - 20; 

if ($espacioDisponible < $alturaGrafico) {
    $fpdf->AddPage();
    $posY = $fpdf->GetY(); // Reinicia la posición después de agregar página
}

$img = explode(',', $imagen, 2)[1];
$pic = 'data://text/plain;base64,' . $img;
$fpdf->Image($pic, 20, $posY, 170, $alturaGrafico, 'png');

$fpdf->SetY($posY + $alturaGrafico + 10);

$fpdf->SetFillColor(255, 255, 255);
$fpdf->SetTextColor(0, 0, 0);
$fpdf->SetDrawColor(185, 185, 185);
$fpdf->SetFont('Arial', 'B', 11);

$fpdf->Cell(190, 8, strtoupper(utf8_decode('TOP 3 DEL PORCENTAJE DE SIMILITUD')), 0, 0, 'S', 1);
$fpdf->SetFillColor(66, 139, 255);
$fpdf->SetTextColor(255, 255, 255);

$fpdf->Ln();

$fpdf->Cell(109, 8, ' ESTUDIANTE', 1, 0, 'S', 1);
$fpdf->Cell(59, 8, 'PORCENTAJE DE SIMILITUD', 1, 0, 'C', 1);
$fpdf->Cell(22, 8, 'RIESGO', 1, 0, 'C', 1);
$fpdf->Ln();
$top3 = traerTop($data);
foreach ($top3 as $top) {
    $fpdf->SetDrawColor(185, 185, 185);
    $fpdf->SetTextColor(0, 0, 0);
    $fpdf->SetFillColor(255, 255, 255);
    $fpdf->SetFont('Arial', '', 11);
    $fpdf->Cell(109, 8, utf8_decode($top['nombre']), 1, 0, 'L', 1); // Nombre alineado a la izquierda
    $fpdf->Cell(59, 8, utf8_decode($top['sumaTotal'] . "%"), 1, 0, 'C', 1); // Edad alineada al centro
    
    if($top['riesgo'] == 'BAJO'){
        $fpdf->SetTextColor(74, 177, 81);
    }else if($top['riesgo'] == 'MEDIO'){
        $fpdf->SetTextColor(253, 167, 37);
    }else if($top['riesgo'] == 'ALTO'){
        $fpdf->SetTextColor(254, 99, 131);
    }
    $fpdf->SetFont('Arial', 'B', 11);
    $fpdf->Cell(22, 8, utf8_decode($top['riesgo']), 1, 0, 'C', 1); // Otro valor alineado al centro
    $fpdf->Ln();
}

$fpdf->Ln();
$fpdf->SetTextColor(0, 0, 0);

$fpdf->SetFillColor(74, 177, 81); // verde
$fpdf->Cell(8, 8, '', 0, 0, 'C', 1); // Cuadrado de color verde
$fpdf->SetFillColor(255, 255, 255);
$fpdf->Cell(40, 8, '0 - 35%', 0, 0, 'S', 1);

$fpdf->SetFillColor(253, 167, 37); // amarillo
$fpdf->Cell(8, 8, '', 0, 0, 'C', 1); // Cuadrado de color amarillo
$fpdf->SetFillColor(255, 255, 255);
$fpdf->Cell(40, 8, '35 - 65%', 0, 0, 'S', 1);

$fpdf->SetFillColor(254, 99, 131); // rojo
$fpdf->Cell(8, 8, '', 0, 0, 'C', 1); // Cuadrado de color rojo
$fpdf->SetFillColor(255, 255, 255);
$fpdf->Cell(40, 8, '65 - 100%', 0, 0, 'S', 1);

$fpdf->Ln();

/*
$user_agent = $_SERVER["HTTP_USER_AGENT"];
if (preg_match("/(android|webos|avantgo|iphone|ipod|ipad|bolt|boost|cricket|docomo|fone|hiptop|opera mini|mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $user_agent)) {
    $fpdf->Output('D', 'reporte.pdf');
} else {
    $fpdf->Output();
}
*/
$pdf_output = $fpdf->Output('S'); // Obtener PDF como cadena
header('Content-Type: application/json');
echo json_encode(['pdf' => base64_encode($pdf_output)]);
