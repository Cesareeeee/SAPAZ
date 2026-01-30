<?php
// Buffer de salida para evitar errores de "headers sent" por espacios en blanco en includes
ob_start();

// Habilitar errores temporalmente para debug (luego se puede quitar o comentar)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configuración regional
date_default_timezone_set('America/Mexico_City');

// Rutas absolutas para evitar problemas relativos
$base_dir = dirname(__DIR__); // c:\xampp\htdocs\AGUA
$fpdf_path = $base_dir . '/recursos/libs/fpdf/fpdf.php';
$conexion_path = $base_dir . '/includes/conexion.php';

// Verificaciones de archivos críticos
if (!file_exists($fpdf_path)) {
    ob_end_clean();
    die("<h1 style='color:red;'>Error Crítico</h1><p>No se encuentra la librería FPDF en:<br><code>$fpdf_path</code></p>");
}
if (!file_exists($conexion_path)) {
    ob_end_clean();
    die("<h1 style='color:red;'>Error Crítico</h1><p>No se encuentra el archivo de conexión en:<br><code>$conexion_path</code></p>");
}

require($fpdf_path);
require_once($conexion_path);

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    ob_end_clean();
    die("Error: Acceso no autorizado. Por favor inicie sesión.");
}

$tipo = $_GET['tipo'] ?? '';
$fecha_inicio = $_GET['inicio'] ?? '';
$fecha_fin = $_GET['fin'] ?? '';

// Clase PDF Extendida
class ReportePDF extends FPDF {
    public $tituloReporte = '';
    public $subtituloReporte = '';
    public $logoPath = '';

    function Header() {
        // Logo
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 10, 25);
        }
        
        // Encabezado
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(31, 78, 121);
        $this->Cell(0, 10, $this->encode('SISTEMA DE AGUA POTABLE Y ALCANTARILLADO'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, $this->encode('ZECALACOAYAN, MUNICIPIO DE ALMOLOYA DE JUÁREZ'), 0, 1, 'C');
        
        $this->Ln(5);
        
        // Título
        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 10, $this->encode($this->tituloReporte), 0, 1, 'C');
        
        if ($this->subtituloReporte) {
            $this->SetFont('Arial', 'I', 11);
            $this->Cell(0, 6, $this->encode($this->subtituloReporte), 0, 1, 'C');
        }
        
        $this->Ln(10);
        
        // Línea
        $this->SetDrawColor(31, 78, 121);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 205, $this->GetY());
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->encode('Página ') . $this->PageNo() . '/{nb} | Generado el: ' . date('d/m/Y H:i:s'), 0, 0, 'C');
    }

    // Helper seguro para codificación
    function encode($str) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
        }
        return utf8_decode($str);
    }

    function TableHeader($header, $widths) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(31, 78, 121);
        $this->SetTextColor(255);
        $this->SetDrawColor(31, 78, 121);
        $this->SetLineWidth(0.3);

        for($i=0; $i<count($header); $i++) {
            $this->Cell($widths[$i], 8, $this->encode($header[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
    }
}

try {
    // Instancia PDF
    $pdf = new ReportePDF('P', 'mm', 'Letter');
    $pdf->logoPath = $base_dir . '/recursos/imagenes/SAPAZ.jpeg';
    $pdf->AliasNbPages();

    // 1. REPORTE DE USUARIOS
    if ($tipo === 'usuarios') {
        $pdf->tituloReporte = "PADRÓN DE USUARIOS REGISTRADOS";
        $pdf->subtituloReporte = "Listado completo de beneficiarios activos";
        
        $pdf->AddPage();
        
        $header = ['ID', 'No. Contrato', 'Nombre del Beneficiario', 'Medidor', 'Servicio'];
        $widths = [15, 35, 90, 30, 25];
        
        $pdf->TableHeader($header, $widths);
        
        $result = $conn->query("SELECT id_usuario, no_contrato, nombre, no_medidor, tipo_servicio FROM usuarios_servicio WHERE activo = 1 ORDER BY nombre ASC");
        
        if (!$result) throw new Exception("Error BD: " . $conn->error);

        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $fill = false;
        
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(240, 245, 255);
            $pdf->Cell($widths[0], 7, $row['id_usuario'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[1], 7, $row['no_contrato'], 1, 0, 'C', $fill);
            
            // Truncar nombre larguísimo
            $nombre = $row['nombre'];
            if (strlen($nombre) > 50) $nombre = substr($nombre, 0, 47) . '...';
            
            $pdf->Cell($widths[2], 7, $pdf->encode($nombre), 1, 0, 'L', $fill);
            $pdf->Cell($widths[3], 7, $row['no_medidor'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[4], 7, $row['tipo_servicio'], 1, 0, 'C', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
    }

    // 2. REPORTE DE LECTURAS
    elseif ($tipo === 'lecturas') {
        if (empty($fecha_inicio) || empty($fecha_fin)) {
            throw new Exception("Debe seleccionar las fechas de inicio y fin.");
        }
        
        $pdf->tituloReporte = "REPORTE DE LECTURAS";
        $pdf->subtituloReporte = "Periodo: " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin));
        
        $pdf->AddPage();
        
        $header = ['Fecha', 'Contrato', 'Beneficiario', 'L. Ant', 'L. Act', 'Consumo'];
        $widths = [25, 30, 80, 20, 20, 20];
        
        $pdf->TableHeader($header, $widths);
        
        $sql = "SELECT l.fecha_lectura, u.no_contrato, u.nombre, l.lectura_anterior, l.lectura_actual, l.consumo_m3 
                FROM lecturas l 
                JOIN usuarios_servicio u ON l.id_usuario = u.id_usuario 
                WHERE l.fecha_lectura BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                ORDER BY l.fecha_lectura DESC, u.nombre ASC";
                
        $result = $conn->query($sql);
        if (!$result) throw new Exception("Error BD: " . $conn->error);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $fill = false;
        
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(240, 245, 255);
            $pdf->Cell($widths[0], 7, date('d/m/Y', strtotime($row['fecha_lectura'])), 1, 0, 'C', $fill);
            $pdf->Cell($widths[1], 7, $row['no_contrato'], 1, 0, 'C', $fill);
            
            $nombre = $row['nombre'];
            if (strlen($nombre) > 35) $nombre = substr($nombre, 0, 32) . '...';
            
            $pdf->Cell($widths[2], 7, $pdf->encode($nombre), 1, 0, 'L', $fill);
            $pdf->Cell($widths[3], 7, $row['lectura_anterior'], 1, 0, 'R', $fill);
            $pdf->Cell($widths[4], 7, $row['lectura_actual'], 1, 0, 'R', $fill);
            $pdf->Cell($widths[5], 7, $row['consumo_m3'] . ' m3', 1, 0, 'R', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
    }

    // 3. REPORTE DE INGRESOS
    elseif ($tipo === 'ingresos') {
        if (empty($fecha_inicio) || empty($fecha_fin)) {
            throw new Exception("Debe seleccionar las fechas de inicio y fin.");
        }
        
        $pdf->tituloReporte = "REPORTE DE INGRESOS";
        $pdf->subtituloReporte = "Cobros realizados del " . date('d/m/Y', strtotime($fecha_inicio)) . " al " . date('d/m/Y', strtotime($fecha_fin));
        
        $pdf->AddPage();
        
        $header = ['Folio', 'Fecha Pago', 'Contrato', 'Beneficiario', 'Monto'];
        $widths = [20, 30, 30, 85, 30];
        
        $pdf->TableHeader($header, $widths);
        
        $sql = "SELECT f.id_factura, f.fecha_pago, u.no_contrato, u.nombre, f.monto_total 
                FROM facturas f 
                JOIN usuarios_servicio u ON f.id_usuario = u.id_usuario 
                WHERE f.estado = 'PAGADA' 
                AND f.fecha_pago BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                ORDER BY f.fecha_pago DESC";
                
        $result = $conn->query($sql);
        if (!$result) throw new Exception("Error BD: " . $conn->error);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $fill = false; // Reset fill
        $totalIngresos = 0;
        
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(240, 245, 255);
            $totalIngresos += $row['monto_total'];
            
            $pdf->Cell($widths[0], 7, $row['id_factura'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[1], 7, date('d/m/Y', strtotime($row['fecha_pago'])), 1, 0, 'C', $fill);
            $pdf->Cell($widths[2], 7, $row['no_contrato'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[3], 7, $pdf->encode($row['nombre']), 1, 0, 'L', $fill);
            $pdf->Cell($widths[4], 7, '$' . number_format($row['monto_total'], 2), 1, 0, 'R', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(array_sum(array_slice($widths, 0, 4)), 10, 'TOTAL INGRESOS PERIODO:', 1, 0, 'R');
        $pdf->Cell($widths[4], 10, '$' . number_format($totalIngresos, 2), 1, 1, 'R');
    }

    // 4. REPORTE DE ADEUDOS
    elseif ($tipo === 'adeudos') {
        $pdf->tituloReporte = "REPORTE DE ADEUDOS";
        $pdf->subtituloReporte = "Listado de facturas pendientes de pago al " . date('d/m/Y');
        
        $pdf->AddPage();
        
        $header = ['Emisión', 'Contrato', 'Beneficiario', 'Periodo', 'Monto'];
        $widths = [30, 30, 95, 20, 20];
        
        $pdf->TableHeader($header, $widths);
        
        $sql = "SELECT f.fecha_emision, u.no_contrato, u.nombre, f.monto_total, f.mes_facturado, f.anio_facturado 
                FROM facturas f 
                JOIN usuarios_servicio u ON f.id_usuario = u.id_usuario 
                WHERE f.estado IN ('PENDIENTE', 'VENCIDA') 
                ORDER BY f.fecha_emision ASC";
                
        $result = $conn->query($sql);
        if (!$result) throw new Exception("Error BD: " . $conn->error);
        
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetTextColor(0);
        $fill = false;
        $totalAdeudo = 0;
        
        while ($row = $result->fetch_assoc()) {
            $pdf->SetFillColor(255, 235, 235);
            $totalAdeudo += $row['monto_total'];
            
            $periodo = $row['mes_facturado'] . '/' . $row['anio_facturado'];
            
            $pdf->Cell($widths[0], 7, date('d/m/Y', strtotime($row['fecha_emision'])), 1, 0, 'C', $fill);
            $pdf->Cell($widths[1], 7, $row['no_contrato'], 1, 0, 'C', $fill);
            $pdf->Cell($widths[2], 7, $pdf->encode($row['nombre']), 1, 0, 'L', $fill);
            $pdf->Cell($widths[3], 7, $periodo, 1, 0, 'C', $fill);
            $pdf->Cell($widths[4], 7, '$' . number_format($row['monto_total'], 2), 1, 0, 'R', $fill);
            $pdf->Ln();
            $fill = !$fill;
        }
        
        // Total
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(array_sum(array_slice($widths, 0, 4)), 10, 'TOTAL ADEUDO GENERAL:', 1, 0, 'R');
        $pdf->Cell($widths[4], 10, '$' . number_format($totalAdeudo, 2), 1, 1, 'R');
    }
    else {
        throw new Exception("Tipo de reporte no especificado o inválido.");
    }

    // Limpiar cualquier salida previa antes de generar el PDF
    if (ob_get_length()) ob_end_clean();
    
    // Salida
    $pdf->Output('I', 'Reporte_' . ucfirst($tipo) . '.pdf');

} catch (Exception $e) {
    if (ob_get_length()) ob_end_clean();
    echo "<h2 style='color:red; font-family:sans-serif;'>Error al generar reporte</h2>";
    echo "<p style='font-family:sans-serif;'>" . $e->getMessage() . "</p>";
}
?>
