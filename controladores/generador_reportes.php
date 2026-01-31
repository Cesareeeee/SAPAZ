<?php
// INICIO CRÍTICO: Capturar todo el buffer y desactivar errores visibles
ob_start();
error_reporting(0); // Silenciar warnings para no corromper PDF/Excel
ini_set('display_errors', 0);

// Configuración regional
date_default_timezone_set('America/Mexico_City');

// Rutas
$base_dir = dirname(__DIR__);
$fpdf_path = $base_dir . '/recursos/libs/fpdf/fpdf.php'; // Verificar si es libs o lib
// Intento de fallback para fpdf si la ruta es lib
if (!file_exists($fpdf_path)) {
    $fpdf_path = $base_dir . '/recursos/lib/fpdf/fpdf.php';
}

$conexion_path = $base_dir . '/includes/conexion.php';

// Validar archivos antes de requerir - Si fallan, limpiar buffer y mostrar error HTML limpio
if (!file_exists($fpdf_path)) {
    ob_end_clean();
    die("Error: FPDF no encontrado. Buscando en: $fpdf_path");
}
if (!file_exists($conexion_path)) {
    ob_end_clean();
    die("Error: Conexión no encontrada en $conexion_path");
}

require($fpdf_path);
require_once($conexion_path);

// Parámetros
$tipo = $_GET['tipo'] ?? '';
$formato = $_GET['formato'] ?? 'pdf';
$fecha_inicio = $_GET['inicio'] ?? '';
$fecha_fin = $_GET['fin'] ?? '';

// --- BLOQUE DE FUNCIONES ---
function descargarExcel($filename, $header, $data, $titulo = '', $subtitulo = '') {
    // Limpieza final y absoluta del buffer
    while (ob_get_level()) ob_end_clean();
    
    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=$filename.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "\xEF\xBB\xBF"; // BOM UTF-8

    // Estilos optimizados para Excel
    $styleHeader = "background-color:#1F4E79; color:white; font-weight:bold; border:1px solid #000; text-align:center;";
    $styleSubHeader = "background-color:#DDEBF7; color:#333; font-weight:bold; border:1px solid #000; text-align:center;";
    $styleCell = "border:1px solid #000; vertical-align:middle;";
    
    echo "<table border='1'>";
    
    $cols = count($header);
    
    // Título Global
    if ($titulo) {
        echo "<tr><td colspan='$cols' style='$styleHeader; font-size:16px; height:30px;'>" . 
             htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    }
    // Subtítulo Global
    if ($subtitulo) {
        echo "<tr><td colspan='$cols' style='$styleSubHeader; font-size:12px; height:20px;'>" . 
             htmlspecialchars($subtitulo, ENT_QUOTES, 'UTF-8') . "</td></tr>";
    }
    
    // Encabezados
    echo "<tr>";
    foreach ($header as $th) {
        echo "<th style='$styleHeader'>" . htmlspecialchars($th, ENT_QUOTES, 'UTF-8') . "</th>";
    }
    echo "</tr>";
    
    // Datos
    foreach ($data as $row) {
        echo "<tr>";
        foreach ($row as $cell) {
            echo "<td style='$styleCell'>" . ($cell ?? '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";
    exit;
}

class ReportePDF extends FPDF {
    public $tituloReporte = '';
    public $subtituloReporte = '';
    public $logoPath = '';

    function Header() {
        if (file_exists($this->logoPath)) {
            $this->Image($this->logoPath, 15, 10, 25);
        }
        
        $this->SetFont('Arial', 'B', 14);
        $this->SetTextColor(31, 78, 121);
        $this->Cell(0, 8, $this->encode('SISTEMA DE AGUA POTABLE Y ALCANTARILLADO'), 0, 1, 'C');
        
        $this->SetFont('Arial', 'B', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Cell(0, 5, $this->encode('SAN NICOLAS ZECALACOAYAN'), 0, 1, 'C');
        $this->Ln(3);

        $this->SetFont('Arial', 'B', 16);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(0, 8, $this->encode($this->tituloReporte), 0, 1, 'C');
        
        if ($this->subtituloReporte) {
            $this->SetFont('Arial', 'I', 11);
            $this->Cell(0, 6, $this->encode($this->subtituloReporte), 0, 1, 'C');
        }
        $this->Ln(5);
        
        $this->SetDrawColor(31, 78, 121);
        $this->SetLineWidth(0.5);
        $this->Line(10, $this->GetY(), 205, $this->GetY());
        $this->Ln(5);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 8);
        $this->SetTextColor(128);
        $this->Cell(0, 10, $this->encode('Página ') . $this->PageNo() . '/{nb} | ' . date('d/m/Y H:i'), 0, 0, 'C');
    }

    function encode($str) {
        if (function_exists('mb_convert_encoding')) {
            try { return mb_convert_encoding($str ?? '', 'ISO-8859-1', 'UTF-8'); } 
            catch (Exception $e) { return $str; }
        }
        return utf8_decode($str ?? '');
    }

    function TableHeader($header, $widths) {
        $this->SetFont('Arial', 'B', 9);
        $this->SetFillColor(31, 78, 121);
        $this->SetTextColor(255);
        $this->SetDrawColor(31, 78, 121);
        for ($i=0; $i<count($header); $i++) {
            $this->Cell($widths[$i], 8, $this->encode($header[$i]), 1, 0, 'C', true);
        }
        $this->Ln();
    }
}

// --- LOGICA PRINCIPAL ---
try {
    // Si falta tipo, error
    if (empty($tipo)) throw new Exception("Tipo de reporte no especificado");

    // Preparar Datos Comunes
    $titulo = "";
    $subtitulo = "";
    $header_cols = [];
    $data_rows = [];
    $widths = []; // Solo para PDF

    // ---------------------------------------------------------
    // 1. USUARIOS
    // ---------------------------------------------------------
    if ($tipo === 'usuarios') {
        $titulo = "PADRÓN DE USUARIOS";
        $subtitulo = "Todos los usuarios activos";
        
        $filtro_tipo = $_GET['filtro_tipo'] ?? '';
        $filtro_valor = $_GET['filtro_valor'] ?? '';
        $where = "WHERE u.activo = 1";

        if ($filtro_tipo === 'calle' && !empty($filtro_valor)) {
            $subtitulo = "Filtro por Calle: $filtro_valor";
            $where .= " AND d.calle = '" . $conn->real_escape_string($filtro_valor) . "'";
        } elseif ($filtro_tipo === 'barrio' && !empty($filtro_valor)) {
            $subtitulo = "Filtro por Barrio: $filtro_valor";
            $where .= " AND d.barrio = '" . $conn->real_escape_string($filtro_valor) . "'";
        }

        $sql = "SELECT u.id_usuario, u.no_contrato, u.nombre, u.no_medidor 
                FROM usuarios_servicio u 
                LEFT JOIN domicilios d ON u.id_domicilio = d.id_domicilio 
                $where ORDER BY u.nombre ASC";
        
        $res = $conn->query($sql);
        if (!$res) throw new Exception($conn->error);

        $header_cols = ['ID', 'No. Contrato', 'Beneficiario', 'Medidor'];
        $widths = [15, 30, 115, 30];

        while($row = $res->fetch_assoc()) {
            $data_rows[] = [
                $row['id_usuario'], 
                $row['no_contrato'], 
                $row['nombre'], 
                $row['no_medidor']
            ];
        }

    // ---------------------------------------------------------
    // 2. LECTURAS
    // ---------------------------------------------------------
    } elseif ($tipo === 'lecturas') {
        if (!$fecha_inicio || !$fecha_fin) throw new Exception("Fechas requeridas");
        $titulo = "REPORTE DE LECTURAS";
        $subtitulo = "Periodo: $fecha_inicio al $fecha_fin";

        $sql = "SELECT l.fecha_lectura, u.no_contrato, u.nombre, l.lectura_anterior, l.lectura_actual, l.consumo_m3 
                FROM lecturas l 
                JOIN usuarios_servicio u ON l.id_usuario = u.id_usuario 
                WHERE l.fecha_lectura BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                ORDER BY l.fecha_lectura DESC";
        
        $res = $conn->query($sql);
        if (!$res) throw new Exception($conn->error);

        $header_cols = ['Fecha', 'Contrato', 'Beneficiario', 'Ant.', 'Act.', 'M3'];
        $widths = [25, 25, 80, 20, 20, 20];

        while($row = $res->fetch_assoc()) {
            $data_rows[] = [
                date('d/m/Y', strtotime($row['fecha_lectura'])), 
                $row['no_contrato'], 
                $row['nombre'],
                $row['lectura_anterior'], 
                $row['lectura_actual'], 
                $row['consumo_m3']
            ];
        }

    // ---------------------------------------------------------
    // 3. INGRESOS
    // ---------------------------------------------------------
    } elseif ($tipo === 'ingresos') {
        if (!$fecha_inicio || !$fecha_fin) throw new Exception("Fechas requeridas");
        $titulo = "REPORTE DE INGRESOS";
        $subtitulo = "Cobros realizados del $fecha_inicio al $fecha_fin";

        // Usamos fecha_emision como proxy de fecha de pago y estado 'Pagado'
        $sql = "SELECT f.id_factura, f.fecha_emision as fecha_pago, u.no_contrato, u.nombre, f.monto_total 
                FROM facturas f JOIN usuarios_servicio u ON f.id_usuario = u.id_usuario 
                WHERE f.estado = 'Pagado' AND f.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY f.fecha_emision DESC";
        
        $res = $conn->query($sql);
        if (!$res) throw new Exception($conn->error);

        $header_cols = ['Folio', 'Fecha', 'Contrato', 'Beneficiario', 'Monto'];
        $widths = [20, 25, 25, 90, 30];

        $total = 0;
        while($row = $res->fetch_assoc()) {
            $total += $row['monto_total'];
            $data_rows[] = [
                $row['id_factura'],
                date('d/m/Y', strtotime($row['fecha_pago'])),
                $row['no_contrato'],
                $row['nombre'],
                '$' . number_format($row['monto_total'], 2)
            ];
        }
        // Fila extra para excel
        if ($formato === 'excel') {
            $data_rows[] = ['', '', '', 'TOTAL', '$' . number_format($total, 2)];
        }

    // ---------------------------------------------------------
    // 4. ADEUDOS
    // ---------------------------------------------------------
    }

    // 5. HISTORIAL GLOBAL (MASIVO - ESTADO DE CUENTA POR USUARIO)
    elseif ($tipo === 'historial_global') {
        $titulo = "HISTORIAL DE PAGOS Y CONSUMOS";
        $subtitulo = "Reporte Masivo Detallado";

        $filtro_tipo = $_GET['filtro_tipo'] ?? '';
        $filtro_valor = $_GET['filtro_valor'] ?? '';
        
        $whereUsuarios = "WHERE u.activo = 1";
        if ($filtro_tipo === 'calle' && !empty($filtro_valor)) {
            $whereUsuarios .= " AND d.calle = '" . $conn->real_escape_string($filtro_valor) . "'";
        } elseif ($filtro_tipo === 'barrio' && !empty($filtro_valor)) {
            $whereUsuarios .= " AND d.barrio = '" . $conn->real_escape_string($filtro_valor) . "'";
        }

        // 1. Obtener Tarifa Base (Una sola vez)
        $resConf = $conn->query("SELECT valor FROM configuracion WHERE clave = 'tarifa_m3'");
        $tarifaBase = '10.00';
        if ($resConf && $rowConf = $resConf->fetch_assoc()) {
            $tarifaBase = $rowConf['valor'];
        }

        // 2. Obtener Lista de Usuarios a Procesar
        $sqlUsuarios = "SELECT u.*, d.calle, d.barrio 
                        FROM usuarios_servicio u 
                        LEFT JOIN domicilios d ON u.id_domicilio = d.id_domicilio 
                        $whereUsuarios 
                        ORDER BY u.nombre ASC";
        $resUsuarios = $conn->query($sqlUsuarios);
        
        if ($formato === 'excel') {
             // Lógica Excel (Simplificada flujos continuos)
             // ... Por brevedad y dado que la solicitud es PDF específico, 
             // mantendremos el excel simple o redirigimos a PDF
             // El usuario pidió "EN HOJAS SEPARADAS, PERO TODAS EN UN MISMO ARCHIVO" (PDF Context)
             // Para Excel pondremos lista plana con identificador de usuario
             $header_cols = ['Contrato', 'Beneficiario', 'Fecha', 'Recibo', 'Lec. Ant', 'Lec. Act', 'M3', 'Monto', 'Detalle'];
             while($u = $resUsuarios->fetch_assoc()) {
                 // Fetch historial simple
                 $sqlH = "SELECT f.fecha_emision, f.id_factura, f.monto_total, f.estado, l.lectura_anterior, l.lectura_actual, l.consumo_m3
                          FROM facturas f LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura
                          WHERE f.id_usuario = " . $u['id_usuario'] . " ORDER BY f.fecha_emision DESC LIMIT 50";
                 $resH = $conn->query($sqlH);
                 while($row = $resH->fetch_assoc()) {
                     $data_rows[] = [
                         $u['no_contrato'], $u['nombre'], 
                         date('d/m/Y', strtotime($row['fecha_emision'])),
                         $row['id_factura'],
                         $row['lectura_anterior'], $row['lectura_actual'], $row['consumo_m3'],
                         $row['monto_total'], $row['estado']
                     ];
                 }
             }
             descargarExcel("Historial_Masivo", $header_cols, $data_rows);
        } else {
            // PDF MASIVO
            while (ob_get_level()) ob_end_clean();

            $pdf = new ReportePDF('P', 'mm', 'Letter');
            $pdf->tituloReporte = $titulo;
            $pdf->subtituloReporte = $subtitulo;
            $pdf->logoPath = $base_dir . '/recursos/imagenes/SAPAZ.jpeg';
            $pdf->AliasNbPages();

            // Loop Usuarios
            while($usuario = $resUsuarios->fetch_assoc()) {
                $pdf->AddPage();
                
                // --- DATOS GENERALES ---
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->SetFillColor(230, 230, 230);
                $pdf->SetDrawColor(0, 0, 0);
                $pdf->SetLineWidth(0.2);

                // Row 1
                $pdf->Cell(30, 6, 'BENEFICIARIO', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                // Truncar nombre si muy largo
                $nom = $pdf->encode($usuario['nombre']);
                if(strlen($nom)>70) $nom = substr($nom,0,67).'...';
                $pdf->Cell(166, 6, $nom, 1, 1, 'L');

                // Row 2
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'No. CONTRATO', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(80, 6, $usuario['no_contrato'], 1, 0, 'L');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(40, 6, 'TARIFA', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(46, 6, '', 1, 1, 'L'); // Vacío o tipo tarifa

                // Row 3
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'No. MEDIDOR', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(80, 6, $usuario['no_medidor'], 1, 0, 'L');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(40, 6, 'CUOTA FIJA $', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 9);
                $pdf->Cell(46, 6, $tarifaBase, 1, 1, 'L');

                // Row 4
                $domicilio = ($usuario['calle'] ?? '') . ', ' . ($usuario['barrio'] ?? '');
                $pdf->SetFont('Arial', 'B', 9);
                $pdf->Cell(30, 6, 'DOMICILIO', 1, 0, 'L', true);
                $pdf->SetFont('Arial', '', 8);
                $pdf->Cell(166, 6, $pdf->encode($domicilio), 1, 1, 'L');

                $pdf->Ln(5);

                // --- TABLA HISTORIAL ---
                // Config Columns
                $wFecha = 22; $wRecibo = 18;
                $wLecAny = 15; $wLecAct = 15; $wConsum = 15;
                $wCargos = 18; $wRecarg = 18; $wCredit = 18; $wSaldo = 18;
                $wObs = 39;

                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(220, 220, 220);

                $x = $pdf->GetX();
                $y = $pdf->GetY();

                // Header Main
                $pdf->Cell($wFecha, 10, 'FECHA', 1, 0, 'C', true);
                $pdf->Cell($wRecibo, 10, 'RECIBO', 1, 0, 'C', true);
                $pdf->Cell($wLecAny + $wLecAct + $wConsum, 5, 'SERVICIO POR MEDIDOR', 1, 0, 'C', true);
                $pdf->Cell($wCargos + $wRecarg + $wCredit + $wSaldo, 5, 'MOVIMIENTOS', 1, 0, 'C', true);
                $pdf->Cell($wObs, 10, 'OBSERVACIONES', 1, 0, 'C', true);

                $pdf->SetXY($x + $wFecha + $wRecibo, $y + 5);
                // Subheaders
                $pdf->Cell($wLecAny, 5, 'LEC. ANT.', 1, 0, 'C', true);
                $pdf->Cell($wLecAct, 5, 'LEC. ACT.', 1, 0, 'C', true);
                $pdf->Cell($wConsum, 5, 'M3', 1, 0, 'C', true);
                $pdf->Cell($wCargos, 5, 'CARGO', 1, 0, 'C', true);
                $pdf->Cell($wRecarg, 5, 'RECAR', 1, 0, 'C', true);
                $pdf->Cell($wCredit, 5, 'CRED', 1, 0, 'C', true);
                $pdf->Cell($wSaldo, 5, 'SALDO', 1, 0, 'C', true);

                $pdf->SetXY($x, $y + 10);

                // Fetch Historial User
                $sqlH = "SELECT f.fecha_emision, f.id_factura, f.monto_total, f.estado,
                         l.lectura_anterior, l.lectura_actual, l.consumo_m3
                         FROM facturas f
                         LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura
                         WHERE f.id_usuario = " . $usuario['id_usuario'] . "
                         ORDER BY f.fecha_emision DESC
                         LIMIT 40"; // Limitado para que quepa en 1-2 páginas por usuario usualmente
                $resH = $conn->query($sqlH);

                $pdf->SetFont('Arial', '', 8);
                $countEx = 0;
                while($row = $resH->fetch_assoc()) {
                    $countEx++;
                    $fecha = date('d/m/Y', strtotime($row['fecha_emision']));
                    $recibo = $row['id_factura'];
                    $lecAnt = $row['lectura_anterior'] ?? '-';
                    $lecAct = $row['lectura_actual'] ?? '-';
                    $consumo = $row['consumo_m3'] ?? '-';
                    $cargo = number_format($row['monto_total'], 2);
                    $obs = $pdf->encode($row['estado']);

                    $pdf->Cell($wFecha, 6, $fecha, 1, 0, 'C');
                    $pdf->Cell($wRecibo, 6, $recibo, 1, 0, 'C');
                    $pdf->Cell($wLecAny, 6, $lecAnt, 1, 0, 'C');
                    $pdf->Cell($wLecAct, 6, $lecAct, 1, 0, 'C');
                    $pdf->Cell($wConsum, 6, $consumo, 1, 0, 'C');
                    $pdf->Cell($wCargos, 6, $cargo, 1, 0, 'R');
                    $pdf->Cell($wRecarg, 6, '0.00', 1, 0, 'R');
                    $pdf->Cell($wCredit, 6, '', 1, 0, 'R');
                    $pdf->Cell($wSaldo, 6, '', 1, 0, 'R');
                    $pdf->Cell($wObs, 6, $obs, 1, 1, 'L');
                }

                // Filler rows
                $rowsPrinted = $countEx;
                $minRows = 15;
                $needed = $minRows - $rowsPrinted;
                if ($needed < 0) $needed = 2; // Minimo espacio extra
                
                for($i=0; $i<$needed; $i++) {
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
            }

            $pdf->Output('I', 'Historial_Masivo.pdf', true);
        }
    }

    // 4. REPORTE DE ADEUDOS
    elseif ($tipo === 'adeudos') {
        $titulo = "REPORTE DE ADEUDOS";
        $subtitulo = "Pendientes (Facturado y Sin Facturar) al " . date('d/m/Y');

        // 1. Obtener tarifa para calcular montos de lecturas sin factura
        $resConf = $conn->query("SELECT valor FROM configuracion WHERE clave = 'tarifa_m3'");
        $tarifa = 10.00; // Default
        if ($resConf && $rowConf = $resConf->fetch_assoc()) {
            $tarifa = floatval($rowConf['valor']);
        }

        // 2. Consulta Unificada: Facturas Pendientes + Lecturas Sin Facturar
        // Parte A: Facturas emitidas no pagadas
        // Parte B: Lecturas que no existen en tabla facturas (o solo en canceladas)
        
        $sql = "
        (
            SELECT 
                'Factura' as origen,
                f.fecha_emision as fecha, 
                u.no_contrato, 
                u.nombre, 
                f.monto_total, 
                l.fecha_lectura,
                f.id_factura as id
            FROM facturas f 
            LEFT JOIN usuarios_servicio u ON f.id_usuario = u.id_usuario 
            LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura
            WHERE UPPER(f.estado) NOT IN ('PAGADO', 'CANCELADO')
        )
        UNION ALL
        (
            SELECT 
                'Lectura' as origen,
                l.fecha_lectura as fecha, 
                u.no_contrato, 
                u.nombre, 
                (l.consumo_m3 * $tarifa) as monto_total, 
                l.fecha_lectura,
                l.id_lectura as id
            FROM lecturas l
            JOIN usuarios_servicio u ON l.id_usuario = u.id_usuario
            WHERE l.id_lectura NOT IN (
                SELECT id_lectura FROM facturas WHERE id_lectura IS NOT NULL AND UPPER(estado) != 'CANCELADO'
            )
        )
        ORDER BY fecha ASC";

        $res = $conn->query($sql);
        if (!$res) throw new Exception($conn->error);

        $header_cols = ['Fecha/Emisión', 'Contrato', 'Beneficiario', 'Periodo/Origen', 'Monto'];
        $widths = [25, 25, 95, 25, 20];

        $total = 0;
        while($row = $res->fetch_assoc()) {
            $total += $row['monto_total'];
            
            // Periodo o indicador de Lectura Pendiente
            if ($row['origen'] === 'Factura') {
                $periodo = $row['fecha_lectura'] ? date('m/Y', strtotime($row['fecha_lectura'])) : 'Varios';
            } else {
                $periodo = ($row['fecha_lectura'] ? date('m/Y', strtotime($row['fecha_lectura'])) : '-') . ' (Pend)';
            }
            
            $nombre = $row['nombre'] ?? 'Usuario Desconocido';
            $contrato = $row['no_contrato'] ?? 'S/N';

            $data_rows[] = [
                date('d/m/Y', strtotime($row['fecha'])),
                $contrato,
                $nombre,
                $periodo,
                '$' . number_format($row['monto_total'], 2)
            ];
        }

        // Si no hay resultados
        if (empty($data_rows)) {
            $data_rows[] = [date('d/m/Y'), '-', 'SIN ADEUDOS REGISTRADOS', '-', '$0.00'];
        }

        if ($formato === 'excel') {
            $data_rows[] = ['', '', '', 'TOTAL', '$' . number_format($total, 2)];
        }
    }

    // ---------------------------------------------------------
    // GENERACIÓN
    // ---------------------------------------------------------

    // Asegurar nombre de archivo limpio y capitalizado
    $nombreArchivo = 'Reporte_' . ucfirst($tipo);

    if ($formato === 'excel') {
        descargarExcel($nombreArchivo, $header_cols, $data_rows, $titulo, $subtitulo);
    } else {
        // PDF
        while (ob_get_level()) ob_end_clean(); // Limpiar buffers previos

        $pdf = new ReportePDF('P', 'mm', 'Letter');
        $pdf->SetTitle($titulo, true); // Establecer título del documento en metadatos
        $pdf->SetAuthor('SAPAZ', true);
        
        $pdf->logoPath = $base_dir . '/recursos/imagenes/SAPAZ.jpeg';
        $pdf->tituloReporte = $titulo;
        $pdf->subtituloReporte = $subtitulo;
        $pdf->AliasNbPages();
        $pdf->AddPage();
        
        $pdf->TableHeader($header_cols, $widths);
        
        $pdf->SetFont('Arial', '', 8); // Reducir un poco la fuente
        $pdf->SetTextColor(0);
        $fill = false;

        foreach ($data_rows as $row) {
            // Ignorar fila de total de Excel si se coló
            if (count($row) < count($widths)) continue; 

            $pdf->SetFillColor(240, 245, 255);
            // Renderizar celdas
            for ($i=0; $i < count($widths); $i++) {
                $align = 'C';
                if ($i == 2) $align = 'L'; // Nombre suele ser columna 2 o 3
                if (isset($row[$i]) && strpos($row[$i], '$') !== false) $align = 'R'; // Dinero a la derecha

                // Truncar textos largos para PDF
                $txt = $row[$i];
                if (strlen($txt) > 55 && $i==2) $txt = substr($txt, 0, 52) . '...';

                $pdf->Cell($widths[$i], 7, $pdf->encode($txt), 1, 0, $align, $fill);
            }
            $pdf->Ln();
            $fill = !$fill;
        }

        // Totales PDF (calculados al vuelo si es necesario, o manual)
        if ($tipo === 'ingresos' || $tipo === 'adeudos') {
             // Ya calculamos $total arriba
             $pdf->SetFont('Arial', 'B', 10);
             $wTotal = 0;
             for($k=0;$k<count($widths)-1;$k++) $wTotal += $widths[$k];
             $pdf->Cell($wTotal, 10, 'TOTAL:', 1, 0, 'R');
             $pdf->Cell($widths[count($widths)-1], 10, '$'.number_format(isset($total)?$total:0, 2), 1, 1, 'R');
        }

        $pdf->Output('I', $nombreArchivo . '.pdf', true);
    }

} catch (Exception $e) {
    ob_end_clean(); // Limpiar para mostrar error limpio en HTML
    echo "<style>body{font-family:sans-serif; text-align:center; margin-top:50px;}</style>";
    echo "<h1 style='color:red;'>Error al generar el reporte</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
