<?php
require_once '../includes/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'get_history') {
        $busqueda_usuario = $_GET['busqueda_usuario'] ?? null;
        $calle = $_GET['calle'] ?? null;
        $barrio = $_GET['barrio'] ?? null;
        $fecha_inicio = $_GET['fecha_inicio'] ?? null;
        $fecha_fin = $_GET['fecha_fin'] ?? null;
        $mes = $_GET['mes'] ?? null;
        $anio = $_GET['anio'] ?? null;
        $consumo_tipo = $_GET['consumo_tipo'] ?? null;
        $orden = strtoupper($_GET['orden'] ?? 'DESC');
        if (!in_array($orden, ['ASC', 'DESC'])) {
            $orden = 'DESC';
        }
        $pagina = intval($_GET['pagina'] ?? 1);
        $limite = intval($_GET['limite'] ?? 10);
        $offset = ($pagina - 1) * $limite;

        $where_clauses = [];
        $params = [];
        $types = '';

        if ($busqueda_usuario) {
            $where_clauses[] = '(us.nombre LIKE ? OR us.no_medidor LIKE ?)';
            $params[] = '%' . $busqueda_usuario . '%';
            $params[] = '%' . $busqueda_usuario . '%';
            $types .= 'ss';
        }

        if ($calle) {
            $where_clauses[] = 'd.calle = ?';
            $params[] = $calle;
            $types .= 's';
        }

        if ($barrio) {
            $where_clauses[] = 'd.barrio = ?';
            $params[] = $barrio;
            $types .= 's';
        }

        if ($fecha_inicio) {
            $where_clauses[] = 'l.fecha_lectura >= ?';
            $params[] = $fecha_inicio;
            $types .= 's';
        }

        if ($fecha_fin) {
            $where_clauses[] = 'l.fecha_lectura <= ?';
            $params[] = $fecha_fin;
            $types .= 's';
        }

        if ($mes) {
            $where_clauses[] = 'SUBSTRING(l.fecha_lectura, 6, 2) = ?';
            $params[] = str_pad($mes, 2, '0', STR_PAD_LEFT);
            $types .= 's';
        }

        if ($anio) {
            $where_clauses[] = 'SUBSTRING(l.fecha_lectura, 1, 4) = ?';
            $params[] = $anio;
            $types .= 's';
        }

        if ($consumo_tipo === 'negativo') {
            $where_clauses[] = 'l.consumo_m3 < 0';
        } elseif ($consumo_tipo === 'alto') {
            $where_clauses[] = 'l.consumo_m3 > 30';
        }

        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        // Contar total
        $count_sql = "SELECT COUNT(*) as total FROM lecturas l
                      JOIN usuarios_servicio us ON l.id_usuario = us.id_usuario
                      JOIN domicilios d ON us.id_domicilio = d.id_domicilio
                      $where_sql";
        $stmt = $conn->prepare($count_sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $total_result = $stmt->get_result()->fetch_assoc();
        $total = $total_result['total'];

        // Obtener lecturas
        $sql = "SELECT l.id_lectura, l.fecha_lectura, l.lectura_anterior, l.lectura_actual, l.consumo_m3, l.observaciones, l.created_at,
                        us.nombre, us.no_medidor, d.calle, d.barrio,
                        uss.nombre AS registrado_por, uss.rol AS rol_registro
                 FROM lecturas l
                 JOIN usuarios_servicio us ON l.id_usuario = us.id_usuario
                 JOIN domicilios d ON us.id_domicilio = d.id_domicilio
                 LEFT JOIN usuarios_sistema uss ON l.id_usuario_sistema = uss.id_usuario_sistema
                 $where_sql
                 ORDER BY l.created_at $orden
                 LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $resultado = $stmt->get_result();

        $lecturas = [];
        while ($row = $resultado->fetch_assoc()) {
            $lecturas[] = $row;
        }

        echo json_encode([
            'success' => true,
            'lecturas' => $lecturas,
            'total' => $total,
            'pagina' => $pagina,
            'limite' => $limite,
            'total_paginas' => ceil($total / $limite)
        ]);
        $conn->close();
        exit;
    }

    if ($action === 'get_users') {
        $stmt = $conn->prepare("SELECT id_usuario, nombre, no_medidor FROM usuarios_servicio WHERE activo = 1 ORDER BY nombre");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuarios = [];
        while ($row = $resultado->fetch_assoc()) {
            $usuarios[] = $row;
        }
        echo json_encode(['success' => true, 'usuarios' => $usuarios]);
        $conn->close();
        exit;
    }

    if ($action === 'get_calles') {
        $stmt = $conn->prepare("SELECT DISTINCT calle FROM domicilios WHERE calle IS NOT NULL AND calle != '' ORDER BY calle");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $calles = [];
        while ($row = $resultado->fetch_assoc()) {
            $calles[] = $row;
        }
        echo json_encode(['success' => true, 'calles' => $calles]);
        $conn->close();
        exit;
    }

    if ($action === 'get_barrios') {
        $stmt = $conn->prepare("SELECT DISTINCT barrio FROM domicilios WHERE barrio IS NOT NULL AND barrio != '' ORDER BY barrio");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $barrios = [];
        while ($row = $resultado->fetch_assoc()) {
            $barrios[] = $row;
        }
        echo json_encode(['success' => true, 'barrios' => $barrios]);
        $conn->close();
        exit;
    }

    if ($action === 'get_lectura') {
        $id_lectura = intval($_GET['id'] ?? 0);
        if (!$id_lectura) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $sql = "SELECT l.id_lectura, l.fecha_lectura, l.lectura_anterior, l.lectura_actual, l.consumo_m3, l.observaciones, l.created_at,
                       us.nombre, us.no_medidor, d.calle, d.barrio,
                       uss.nombre AS registrado_por, uss.rol AS rol_registro
                FROM lecturas l
                JOIN usuarios_servicio us ON l.id_usuario = us.id_usuario
                JOIN domicilios d ON us.id_domicilio = d.id_domicilio
                LEFT JOIN usuarios_sistema uss ON l.id_usuario_sistema = uss.id_usuario_sistema
                WHERE l.id_lectura = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id_lectura);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $lectura = $resultado->fetch_assoc();

        if ($lectura) {
            echo json_encode(['success' => true, 'lectura' => $lectura]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Lectura no encontrada']);
        }
        $conn->close();
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action'])) {
    $action = $_GET['action'];

    if ($action === 'update_lectura') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_lectura = $input['id_lectura'] ?? null;
        $fecha_lectura = $input['fecha_lectura'] ?? null;
        $lectura_actual = $input['lectura_actual'] ?? null;
        $observaciones = $input['observaciones'] ?? '';

        if (!$id_lectura || !$fecha_lectura || !is_numeric($lectura_actual)) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        // Obtener lectura anterior para calcular consumo
        $stmt = $conn->prepare("SELECT lectura_anterior FROM lecturas WHERE id_lectura = ?");
        $stmt->bind_param('i', $id_lectura);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        if (!$result) {
            echo json_encode(['success' => false, 'message' => 'Lectura no encontrada']);
            exit;
        }
        $lectura_anterior = $result['lectura_anterior'];
        $consumo_m3 = $lectura_actual - $lectura_anterior;

        $stmt = $conn->prepare("UPDATE lecturas SET fecha_lectura = ?, lectura_actual = ?, consumo_m3 = ?, observaciones = ? WHERE id_lectura = ?");
        $stmt->bind_param('sddsi', $fecha_lectura, $lectura_actual, $consumo_m3, $observaciones, $id_lectura);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }
        $conn->close();
        exit;
    }

    if ($action === 'delete_lectura') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id_lectura = $input['id_lectura'] ?? null;

        if (!$id_lectura) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM lecturas WHERE id_lectura = ?");
        $stmt->bind_param('i', $id_lectura);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        $conn->close();
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Acción no válida']);
?>