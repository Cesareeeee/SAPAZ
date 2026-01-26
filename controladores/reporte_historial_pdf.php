<?php
require_once '../includes/conexion.php';

if (!isset($_GET['id_usuario'])) {
    die("ID de usuario no especificado.");
}

$id_usuario = intval($_GET['id_usuario']);

// Obtener información del usuario
$stmtInfo = $conn->prepare("
    SELECT us.*, d.calle, d.barrio 
    FROM usuarios_servicio us
    LEFT JOIN domicilios d ON us.id_domicilio = d.id_domicilio
    WHERE us.id_usuario = ?
");
$stmtInfo->bind_param("i", $id_usuario);
$stmtInfo->execute();
$userInfo = $stmtInfo->get_result()->fetch_assoc();

if (!$userInfo) {
    die("Usuario no encontrado.");
}

// Obtener historial completo
$sql = "SELECT l.id_lectura, l.fecha_lectura, l.consumo_m3, l.lectura_anterior, l.lectura_actual, 
               f.id_factura, f.monto_total, f.estado as estado_factura, f.fecha_emision
        FROM lecturas l
        LEFT JOIN facturas f ON l.id_lectura = f.id_lectura
        WHERE l.id_usuario = ?
        ORDER BY l.fecha_lectura DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$res = $stmt->get_result();
$historial = [];
while ($row = $res->fetch_assoc()) {
    $historial[] = $row;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial de Lecturas - <?php echo htmlspecialchars($userInfo['nombre']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            color: #1f2937;
            background: white;
            padding: 2mm; /* Margen para impresión */
        }
        .header {
            text-align: center;
            margin-bottom: 2rem;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 1rem;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 1.5rem;
            text-transform: uppercase;
        }
        .logo-text {
            color: #0ea5e9; 
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        .user-info {
            background: #f8fafc;
            padding: 1rem;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            margin-bottom: 2rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        .info-row span:first-child {
            font-weight: 600;
            color: #64748b;
        }
        .info-row span:last-child {
            font-weight: 700;
            color: #1e293b;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        
        thead th {
            text-align: left;
            padding: 0.75rem;
            background-color: #1e40af; /* Azul fuerte para encabezado */
            color: white;
            font-weight: 600;
            border: 1px solid #1e40af;
        }
        
        tbody td {
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        
        tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        .status-badge {
            padding: 0.2rem 0.6rem;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            display: inline-block;
        }
        .status-pagado {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-pendiente {
            background-color: #ffedd5;
            color: #9a3412;
        }
        .status-sin-factura {
            background-color: #f1f5f9;
            color: #64748b;
        }

        .footer {
            margin-top: 2rem;
            text-align: center;
            font-size: 0.8rem;
            color: #9ca3af;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
        }
        
        @media print {
            body { 
                -webkit-print-color-adjust: exact; 
                print-color-adjust: exact; 
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="header">
        <div class="logo-text">SAPAZ - SISTEMA DE AGUA POTABLE</div>
        <h1>Historial de Consumo y Pagos</h1>
        <p style="margin:0.2rem 0; color:#64748b; font-size:0.9rem;">Fecha de Emisión: <?php echo date('d/m/Y H:i'); ?></p>
    </div>

    <div class="user-info">
        <div class="info-row">
            <span>Usuario:</span>
            <span><?php echo htmlspecialchars($userInfo['nombre']); ?></span>
        </div>
        <div class="info-row">
            <span>Contrato:</span>
            <span><?php echo htmlspecialchars($userInfo['no_contrato']); ?></span>
        </div>
        <div class="info-row">
            <span>Medidor:</span>
            <span><?php echo htmlspecialchars($userInfo['no_medidor'] ?? 'N/A'); ?></span>
        </div>
        <div class="info-row">
            <span>Dirección:</span>
            <span><?php echo htmlspecialchars($userInfo['calle'] . ' ' . $userInfo['barrio']); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Periodo / Fecha</th>
                <th>Lecturas (Ant - Act)</th>
                <th>Consumo (m³)</th>
                <th>Estado</th>
                <th style="text-align:right;">Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($historial) > 0): ?>
                <?php foreach ($historial as $h): 
                    $fecha = date('d/m/Y', strtotime($h['fecha_lectura']));
                    $estatus = 'Sin Factura';
                    $clase = 'status-sin-factura';
                    $monto = '-';

                    if ($h['id_factura']) {
                        $estatus = $h['estado_factura'];
                        $monto = '$' . number_format($h['monto_total'], 2);
                        if ($estatus === 'Pagado') $clase = 'status-pagado';
                        else if ($estatus === 'Pendiente') $clase = 'status-pendiente';
                    }
                ?>
                <tr>
                    <td>
                        <strong><?php echo $fecha; ?></strong>
                    </td>
                    <td>
                        <?php echo $h['lectura_anterior']; ?> - <?php echo $h['lectura_actual']; ?>
                    </td>
                    <td>
                        <strong><?php echo $h['consumo_m3']; ?></strong>
                    </td>
                    <td>
                        <span class="status-badge <?php echo $clase; ?>">
                            <?php echo $estatus; ?>
                        </span>
                    </td>
                    <td style="text-align:right;">
                        <?php echo $monto; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 2rem;">No se encontraron registros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Este documento es un reporte informativo generado automáticamente por el sistema.
    </div>

</body>
</html>
