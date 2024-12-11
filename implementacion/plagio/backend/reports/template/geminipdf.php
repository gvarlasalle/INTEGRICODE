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
$actividad = $_POST['id'];
//$actividad = 1;
$idProfesor = $_SESSION['idProfesor'];

$respuestagemini = $_POST['gemini'];
//$idProfesor = 7;

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
        $this->SetX(66);
        $this->Write(11, 'GENERADO CON IA GEMINI');
    }

    function addFormattedText($text)
{
    // Eliminar múltiples saltos de línea consecutivos
    $text = preg_replace("/\n{2,}/", "\n", $text);

    // Detectar y reemplazar el texto que está entre ** por el mismo texto formateado en negrita
    $parts = preg_split('/(\*\*.*?\*\*)/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    foreach ($parts as $part) {
        // Si el texto está entre **, quitar los asteriscos y poner en negrita
        if (preg_match('/^\*\*(.*?)\*\*$/', $part, $matches)) {
            // Texto entre ** en negrita, eliminar los asteriscos
            $this->SetFont('Arial', 'B', 12);
            $this->MultiCell(0, 10, utf8_decode($matches[1]));
        } else {
            // Texto normal, quitar los asteriscos si los hay
            $part = trim($part);
            $part = str_replace('*', '', $part);
            $part = str_replace('.', '', $part);
            //si la linea trae * o . no la imprime
            if ($part != "") {
                  // Eliminar posibles espacios extra
             // Eliminar asteriscos sobrantes
            $this->SetFont('Arial', '', 12);
            $this->MultiCell(0, 10, utf8_decode($part));
            }
            
        }
        
    }
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
$fpdf->Ln(16);

$fpdf->SetFont('Arial', '', 11);
$fpdf->addFormattedText($respuestagemini);
//$fpdf->MultiCell(190, 8, utf8_decode($respuestagemini), 0, 'L');


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