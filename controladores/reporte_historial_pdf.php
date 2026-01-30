<?php
require('../recursos/libs/fpdf/fpdf.php');
require_once '../includes/conexion.php';

if (!isset($_GET['id_usuario'])) {
    die("Error: No se ha especificado un usuario.");
}

$id_usuario = intval($_GET['id_usuario']);

// Obtener datos del usuario
$sqlUsuario = "SELECT us.*, d.calle, d.barrio 
               FROM usuarios_servicio us 
               LEFT JOIN domicilios d ON us.id_domicilio = d.id_domicilio 
               WHERE us.id_usuario = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$resUsuario = $stmt->get_result();
$usuario = $resUsuario->fetch_assoc();

if (!$usuario) {
    die("Error: Usuario no encontrado.");
}

// Obtener tarifa base
$sqlConfig = "SELECT valor FROM configuracion WHERE clave = 'tarifa_m3'";
$resConfig = $conn->query($sqlConfig);
$tarifaBase = '10.00'; // Default fallback
if ($resConfig && $rowConfig = $resConfig->fetch_assoc()) {
    $tarifaBase = $rowConfig['valor'];
}

// Obtener historial
$sqlHistorial = "SELECT f.fecha_emision, f.id_factura, f.monto_total, f.estado,
                 l.lectura_anterior, l.lectura_actual, l.consumo_m3
                 FROM facturas f
                 LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura
                 WHERE f.id_usuario = ?
                 ORDER BY f.fecha_emision DESC
                 LIMIT 50";
$stmtH = $conn->prepare($sqlHistorial);
$stmtH->bind_param("i", $id_usuario);
$stmtH->execute();
$resHistorial = $stmtH->get_result();
$historial = [];
while ($row = $resHistorial->fetch_assoc()) {
    $historial[] = $row;
}
$stmtH->close();
$stmt->close();
$conn->close();

class PDF extends FPDF {
    function Header() {
        // Logo (Ajustar ruta si es necesaria)
        if (file_exists('../recursos/imagenes/SAPAZ.jpeg')) {
            $this->Image('../recursos/imagenes/SAPAZ.jpeg', 10, 10, 20);
        }
        
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 10, mb_convert_encoding('COMITÉ DEL SISTEMA DE AGUA POTABLE Y ALCANTARILLADO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(0, 5, 'HISTORIAL DE PAGOS Y CONSUMOS', 0, 1, 'C');
        $this->Ln(10);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->Cell(0, 10, mb_convert_encoding('Página ', 'ISO-8859-1', 'UTF-8') . $this->PageNo() . '/{nb}', 0, 0, 'C');
    }
}

$pdf = new PDF('P', 'mm', 'Letter');
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetMargins(10, 10, 10);
$pdf->SetAutoPageBreak(true, 20);

// --- DATOS GENERALES ---
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(230, 230, 230);

// Anchos de columna para Datos Generales (Total ~196)
// Row 1: Contrato (30), Nombre (80), Tarifa (40), Diam (46)
// Row 2: Medidor (30), Domicilio (80), Cuota (40), Clase (46)

$pdf->Cell(30, 6, 'BENEFICIARIO', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(166, 6, mb_convert_encoding($usuario['nombre'], 'ISO-8859-1', 'UTF-8'), 1, 1, 'L');

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, 'No. CONTRATO', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(80, 6, $usuario['no_contrato'], 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, 'TARIFA', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(46, 6, '', 1, 1, 'L'); // Vacío

$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, 'No. MEDIDOR', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(80, 6, $usuario['no_medidor'], 1, 0, 'L');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(40, 6, 'CUOTA FIJA $', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 9);
$pdf->Cell(46, 6, $tarifaBase, 1, 1, 'L');

// Domicilio completo
$domicilio = ($usuario['calle'] ?? '') . ', ' . ($usuario['barrio'] ?? '');
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell(30, 6, 'DOMICILIO', 1, 0, 'L', true);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(166, 6, mb_convert_encoding($domicilio, 'ISO-8859-1', 'UTF-8'), 1, 1, 'L');

$pdf->Ln(5);

// --- TABLA PRINCIPAL ---
// Definición de anchos
$wFecha = 25;
$wRecibo = 20;
// Servicio Medidor (3 cols)
$wLecAnt = 18;
$wLecAct = 18;
$wConsumo = 18;
// Movimientos (4 cols)
$wCargos = 20;
$wRecargos = 20;
$wCreditos = 20;
$wSaldo = 20;

// Observaciones (Resto)
// Total width usage so far: 25+20 + (18*3) + (20*4) = 45 + 54 + 80 = 179.
// Page width ~196. Remaining: 17. Bit small for Obs. Let's adjust.
// Reduce Subcols?
// Fecha: 22, Recibo: 18 -> 40
// Lecs: 15*3 = 45 -> 85
// Movs: 18*4 = 72 -> 157
// Obs: 196 - 157 = 39. Better.

$wFecha = 22;
$wRecibo = 18;
$wLecAny = 15;
$wLecAct = 15;
$wConsum = 15;
$wCargos = 18;
$wRecarg = 18;
$wCredit = 18;
$wSaldo = 18;
$wObs = 39;

// Header Row 1
$pdf->SetFont('Arial', 'B', 8);
$pdf->SetFillColor(220, 220, 220); // Gris claro encabezado

$x = $pdf->GetX();
$y = $pdf->GetY();

$pdf->Cell($wFecha, 10, 'FECHA', 1, 0, 'C', true);
$pdf->Cell($wRecibo, 10, 'RECIBO', 1, 0, 'C', true);

// Group Headers
$pdf->Cell($wLecAny + $wLecAct + $wConsum, 5, 'SERVICIO POR MEDIDOR', 1, 0, 'C', true);
$pdf->Cell($wCargos + $wRecarg + $wCredit + $wSaldo, 5, 'MOVIMIENTOS', 1, 0, 'C', true);

// Observaciones
$pdf->Cell($wObs, 10, 'OBSERVACIONES', 1, 0, 'C', true);

// Go back and down to draw sub-rows
$pdf->SetXY($x + $wFecha + $wRecibo, $y + 5);

// Subheaders Row 2
$pdf->Cell($wLecAny, 5, 'LEC. ANT.', 1, 0, 'C', true);
$pdf->Cell($wLecAct, 5, 'LEC. ACT.', 1, 0, 'C', true);
$pdf->Cell($wConsum, 5, 'M3', 1, 0, 'C', true);

$pdf->Cell($wCargos, 5, 'CARGO', 1, 0, 'C', true);
$pdf->Cell($wRecarg, 5, 'RECAR', 1, 0, 'C', true);
$pdf->Cell($wCredit, 5, 'CRED', 1, 0, 'C', true);
$pdf->Cell($wSaldo, 5, 'SALDO', 1, 0, 'C', true);

$pdf->SetXY($x, $y + 10); // Reset for data

// --- DATA ---
$pdf->SetFont('Arial', '', 8);

foreach ($historial as $row) {
    $fecha = date('d/m/Y', strtotime($row['fecha_emision']));
    $recibo = $row['id_factura'];
    $lecAnt = $row['lectura_anterior'] ?? '-';
    $lecAct = $row['lectura_actual'] ?? '-';
    $consumo = $row['consumo_m3'] ?? '-';
    $cargo = number_format($row['monto_total'], 2);
    $recargo = '0.00'; // DB field?
    $credito = '';
    $saldo = '';
    $obs = $row['estado'];

    $pdf->Cell($wFecha, 6, $fecha, 1, 0, 'C');
    $pdf->Cell($wRecibo, 6, $recibo, 1, 0, 'C');
    
    $pdf->Cell($wLecAny, 6, $lecAnt, 1, 0, 'C');
    $pdf->Cell($wLecAct, 6, $lecAct, 1, 0, 'C');
    $pdf->Cell($wConsum, 6, $consumo, 1, 0, 'C');
    
    $pdf->Cell($wCargos, 6, $cargo, 1, 0, 'R');
    $pdf->Cell($wRecarg, 6, $recargo, 1, 0, 'R');
    $pdf->Cell($wCredit, 6, $credito, 1, 0, 'R');
    $pdf->Cell($wSaldo, 6, $saldo, 1, 0, 'R');
    
    $pdf->Cell($wObs, 6, mb_convert_encoding($obs, 'ISO-8859-1', 'UTF-8'), 1, 1, 'L');
}

// Fill remaining rows to ensure at least 15 or more lines
$rowsPrinted = count($historial);
$minRows = 15;
$emptyRows = $minRows - $rowsPrinted;
if ($emptyRows < 0) $emptyRows = 5; // Always add a few empty if list is long? Prompt says "al menos 15 filas vacías". Assuming total additional space.

for ($i = 0; $i < 15; $i++) {
    $pdf->Cell($wFecha, 6, '', 1, 0, 'C');
    $pdf->Cell($wRecibo, 6, '', 1, 0, 'C');
    $pdf->Cell($wLecAny, 6, '', 1, 0, 'C');
    $pdf->Cell($wLecAct, 6, '', 1, 0, 'C');
    $pdf->Cell($wConsum, 6, '', 1, 0, 'C');
    $pdf->Cell($wCargos, 6, '', 1, 0, 'R');
    $pdf->Cell($wRecarg, 6, '', 1, 0, 'R');
    $pdf->Cell($wCredit, 6, '', 1, 0, 'R');
    $pdf->Cell($wSaldo, 6, '', 1, 0, 'R');
    $pdf->Cell($wObs, 6, '', 1, 1, 'L');
}

$pdf->Output('I', 'Historial_' . $usuario['no_contrato'] . '.pdf');
?>
