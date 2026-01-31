<?php
require_once '../includes/conexion.php';

// Verificar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'get_stats') {
        $stats = [];
        
        // 1. Total Clientes
        $sql = "SELECT COUNT(*) as total FROM usuarios_servicio WHERE activo = 1";
        $result = $conn->query($sql);
        $total_clientes = $result->fetch_assoc()['total'];
        $stats['total_clientes'] = $total_clientes;

        // 2. Lecturas del Mes Actual vs Anterior
        $sql = "SELECT COUNT(*) as total FROM lecturas WHERE MONTH(fecha_lectura) = MONTH(CURRENT_DATE()) AND YEAR(fecha_lectura) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $lecturas_mes = $result->fetch_assoc()['total'];
        $stats['lecturas_mes'] = $lecturas_mes;

        // 3. Facturas Pagadas vs Pendientes (Eficiencia)
        $sql = "SELECT 
                    SUM(CASE WHEN estado = 'Pagado' THEN 1 ELSE 0 END) as pagadas,
                    SUM(CASE WHEN estado = 'Pendiente' OR estado = 'Vencido' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'Pagado' THEN monto_total ELSE 0 END) as monto_cobrado,
                    SUM(CASE WHEN estado = 'Pendiente' OR estado = 'Vencido' THEN monto_total ELSE 0 END) as deuda_total
                FROM facturas 
                WHERE MONTH(fecha_emision) = MONTH(CURRENT_DATE()) AND YEAR(fecha_emision) = YEAR(CURRENT_DATE())";
        
        $result = $conn->query($sql);
        $row_facturas = $result->fetch_assoc();
        
        $stats['facturas_pagadas'] = $row_facturas['pagadas'] ?? 0;
        $stats['facturas_pendientes'] = $row_facturas['pendientes'] ?? 0;
        $stats['monto_cobrado'] = $row_facturas['monto_cobrado'] ?? 0;
        $stats['deuda_total'] = $row_facturas['deuda_total'] ?? 0; // Deuda del mes actual

        // Deuda Histórica Total (Todo lo que no se ha pagado nunca)
        $sql_deuda = "SELECT SUM(monto_total) as total FROM facturas WHERE estado IN ('Pendiente', 'Vencido')";
        $res_deuda = $conn->query($sql_deuda);
        $stats['deuda_historica'] = $res_deuda->fetch_assoc()['total'] ?? 0;

        // 4. Consumo Promedio
        $sql = "SELECT AVG(consumo_m3) as promedio FROM lecturas WHERE MONTH(fecha_lectura) = MONTH(CURRENT_DATE()) AND YEAR(fecha_lectura) = YEAR(CURRENT_DATE())";
        $result = $conn->query($sql);
        $consumo_promedio = $result->fetch_assoc()['promedio'];
        $stats['consumo_promedio'] = round($consumo_promedio ?? 0, 1);

        // 5. Chart Data: Consumo Anual (Se mantiene igual)
        $chart_data = array_fill(1, 12, 0); 
        $sql = "SELECT MONTH(fecha_lectura) as mes, SUM(consumo_m3) as total 
                FROM lecturas 
                WHERE YEAR(fecha_lectura) = YEAR(CURRENT_DATE()) 
                GROUP BY MONTH(fecha_lectura)";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $chart_data[$row['mes']] = (float)$row['total'];
        }
        $stats['chart_data_consumo'] = array_values($chart_data);

        // 6. Recientes Readings
        $sql = "SELECT l.id_lectura, u.nombre, u.no_medidor, l.lectura_actual, l.consumo_m3, l.fecha_lectura, 
                COALESCE(f.estado, 'Pendiente') as estado_pago 
                FROM lecturas l 
                JOIN usuarios_servicio u ON l.id_usuario = u.id_usuario 
                LEFT JOIN facturas f ON l.id_lectura = f.id_lectura 
                ORDER BY l.fecha_lectura DESC LIMIT 5";
        $result = $conn->query($sql);
        $recientes = [];
        while ($row = $result->fetch_assoc()) {
            $recientes[] = [
                'id_lectura' => $row['id_lectura'],
                'nombre' => $row['nombre'],
                'medidor' => $row['no_medidor'],
                'lectura_actual' => $row['lectura_actual'],
                'consumo' => $row['consumo_m3'],
                'fecha' => date('d/m/Y', strtotime($row['fecha_lectura'])),
                'estado' => $row['estado_pago']
            ];
        }
        $stats['recientes'] = $recientes;

        echo json_encode(['success' => true, 'data' => $stats]);
        $conn->close();
        exit;
    }

    if ($action === 'get_income_data') {
        $filter = $_GET['filter'] ?? 'year'; // year, month
        $year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');
        $month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');

        $labels = [];
        $data = [];
        $title = "";

        if ($filter === 'year') {
            // Ingresos por mes del año seleccionado
            $title = "Ingresos Mensuales - Año $year";
            $labels = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $data = array_fill(0, 12, 0);

            $sql = "SELECT MONTH(fecha_emision) as mes, SUM(monto_total) as total 
                    FROM facturas 
                    WHERE YEAR(fecha_emision) = ? AND estado = 'Pagado'
                    GROUP BY MONTH(fecha_emision)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $year);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                // Adjust index (month 1 -> index 0)
                $data[$row['mes'] - 1] = (float)$row['total'];
            }
        } elseif ($filter === 'month') {
            // Ingresos por día del mes seleccionado
            $monthName = date('F', mktime(0, 0, 0, $month, 10)); // Simple month name
            // Spanish Month Name
            $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $title = "Ingresos Diarios - " . $meses[$month] . " $year";
            
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $labels[] = $d;
                $data[] = 0;
            }

            $sql = "SELECT DAY(fecha_emision) as dia, SUM(monto_total) as total 
                    FROM facturas 
                    WHERE YEAR(fecha_emision) = ? AND MONTH(fecha_emision) = ? AND estado = 'Pagado'
                    GROUP BY DAY(fecha_emision)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ii", $year, $month);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $dayIndex = $row['dia'] - 1;
                if (isset($data[$dayIndex])) {
                    $data[$dayIndex] = (float)$row['total'];
                }
            }
        } elseif ($filter === 'day') {
            $day = isset($_GET['day']) ? (int)$_GET['day'] : 1;
            // Ingresos por hora del día seleccionado
            $meses = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            $title = "Ingresos por Hora - $day de " . $meses[$month] . " de $year";
            
            // Labels 00:00 - 23:00
            for ($h = 0; $h < 24; $h++) {
                $labels[] = sprintf("%02d:00", $h);
                $data[] = 0;
            }

            $sql = "SELECT HOUR(fecha_emision) as hora, SUM(monto_total) as total 
                    FROM facturas 
                    WHERE YEAR(fecha_emision) = ? AND MONTH(fecha_emision) = ? AND DAY(fecha_emision) = ? AND estado = 'Pagado'
                    GROUP BY HOUR(fecha_emision)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $year, $month, $day);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                $hourIndex = (int)$row['hora'];
                if (isset($data[$hourIndex])) {
                    $data[$hourIndex] = (float)$row['total'];
                }
            }
        }

        echo json_encode([
            'success' => true, 
            'labels' => $labels, 
            'data' => $data,
            'title' => $title
        ]);
        $conn->close();
        exit;
    }
}
?>
