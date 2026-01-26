<?php
require_once '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    // Obtener filtros (Barrios y Calles)
    if ($action === 'get_filters') {
        $data = ['barrios' => [], 'calles' => []];
        
        $resB = $conn->query("SELECT DISTINCT barrio FROM domicilios WHERE barrio IS NOT NULL AND barrio != '' ORDER BY barrio");
        while ($row = $resB->fetch_assoc()) $data['barrios'][] = $row['barrio'];

        $resC = $conn->query("SELECT DISTINCT calle FROM domicilios WHERE calle IS NOT NULL AND calle != '' ORDER BY calle");
        while ($row = $resC->fetch_assoc()) $data['calles'][] = $row['calle'];

        echo json_encode(['success' => true, 'filters' => $data]);
        exit;
    }

    // Buscar Beneficiarios con Estatus
    if ($action === 'search_beneficiaries') {
        $q = $_GET['q'] ?? '';
        $barrio = $_GET['barrio'] ?? '';
        $calle = $_GET['calle'] ?? '';
        $status = $_GET['status'] ?? 'all'; // all, paid, debt
        $page = intval($_GET['page'] ?? 1);
        $limit = 20;
        $offset = ($page - 1) * $limit;

        // Construir Query Base
        // El estatus se determina así:
        // Si tiene lecturas NO pagadas (id_lectura no en facturas O factura estado != 'Pagado'), es Deudor.
        // Sino, es Al Corriente.
        
        $whereClauses = ["us.activo = 1"];
        $params = [];
        $types = "";

        if ($q) {
            $whereClauses[] = "(us.nombre LIKE ? OR us.no_contrato LIKE ? OR us.no_medidor LIKE ?)";
            $wild = "%$q%";
            $params[] = $wild; $params[] = $wild; $params[] = $wild;
            $types .= "sss";
        }
        if ($barrio) {
            $whereClauses[] = "d.barrio = ?";
            $params[] = $barrio;
            $types .= "s";
        }
        if ($calle) {
            $whereClauses[] = "d.calle = ?";
            $params[] = $calle;
            $types .= "s";
        }

        /* 
           Lógica de Deuda:
           Contar cuantas lecturas tiene el usuario que NO están pagadas.
           Una lectura no pagada es aquella que:
           1. No existe en la tabla facturas.
           2. O existe en facturas pero estado != 'Pagado'.
        */

        $sql = "SELECT us.id_usuario, us.nombre, us.no_contrato, us.no_medidor, d.calle, d.barrio,
                (SELECT COUNT(*) FROM lecturas l 
                 LEFT JOIN facturas f ON l.id_lectura = f.id_lectura 
                 WHERE l.id_usuario = us.id_usuario 
                 AND (f.id_factura IS NULL OR f.estado != 'Pagado')) as lecturas_pendientes,
                (SELECT MAX(fecha_lectura) FROM lecturas WHERE id_usuario = us.id_usuario) as ultima_lectura
                FROM usuarios_servicio us
                JOIN domicilios d ON us.id_domicilio = d.id_domicilio
                WHERE " . implode(" AND ", $whereClauses);

        // Filtrado por Estatus (Post-Query o Subquery compleja? Subquery es mejor para paginación)
        // Para simplificar y optimizar, usamos HAVING si filtramos estatus
        if ($status === 'debt') {
            $sql .= " HAVING lecturas_pendientes > 0";
        } elseif ($status === 'paid') {
            $sql .= " HAVING lecturas_pendientes = 0";
        }

        // Ordenamiento y Paginación
        $sql .= " ORDER BY us.nombre ASC LIMIT ? OFFSET ?";
        
        // Prepare stmt
        $stmt = $conn->prepare($sql);
        
        // Bind dynamic params
        $params[] = $limit;
        $params[] = $offset;
        $types .= "ii";
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $users = [];
        while ($r = $res->fetch_assoc()) {
            $r['estatus'] = ($r['lecturas_pendientes'] > 0) ? 'Deudor' : 'Al Corriente';
            $users[] = $r;
        }

        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    // Obtener Historial de Lecturas/Pagos de un Usuario
    if ($action === 'get_user_history') {
        $id_usuario = intval($_GET['id_usuario']);

        $sql = "SELECT l.id_lectura, l.fecha_lectura, l.consumo_m3, l.lectura_actual, 
                f.id_factura, f.monto_total, f.estado as estado_factura, f.fecha_emision
                FROM lecturas l
                LEFT JOIN facturas f ON l.id_lectura = f.id_lectura
                WHERE l.id_usuario = ?
                ORDER BY l.fecha_lectura DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $history = [];
        while ($r = $res->fetch_assoc()) {
            $history[] = $r;
        }

        echo json_encode(['success' => true, 'history' => $history]);
        exit;
    }
}
?>
