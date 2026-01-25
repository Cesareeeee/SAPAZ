<?php
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'search') {
        $query = trim($_GET['query'] ?? '');
        if (empty($query)) {
            echo json_encode(['success' => false, 'message' => 'Consulta requerida']);
            exit;
        }
        // Buscar por nombre o número de medidor
        $stmt = $conn->prepare("SELECT us.id_usuario, us.nombre, us.no_medidor, d.calle FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio WHERE (us.nombre LIKE ? OR us.no_medidor LIKE ?) AND us.activo = 1 LIMIT 10");
        $search = "%$query%";
        $stmt->bind_param("ss", $search, $search);
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

    if ($action === 'get_last_reading') {
        $id_usuario = $_GET['id_usuario'] ?? '';
        if (empty($id_usuario)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            exit;
        }
        // Obtener la última lectura del mes pasado
        $stmt = $conn->prepare("SELECT lectura_actual, fecha_lectura FROM lecturas WHERE id_usuario = ? ORDER BY id_lectura DESC LIMIT 1");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $lectura = $resultado->fetch_assoc();
            echo json_encode(['success' => true, 'lectura_anterior' => $lectura['lectura_actual'], 'fecha_anterior' => $lectura['fecha_lectura']]);
        } else {
            echo json_encode(['success' => true, 'lectura_anterior' => 0, 'fecha_anterior' => null]);
        }
        $conn->close();
        exit;
    }

    if ($action === 'get_users_without_reading') {
        $pagina = intval($_GET['pagina'] ?? 1);
        $limite = intval($_GET['limite'] ?? 10);
        $offset = ($pagina - 1) * $limite;
        $calle = trim($_GET['calle'] ?? '');
        $barrio = trim($_GET['barrio'] ?? '');

        // Base query para usuarios sin lectura en el mes actual
        $query = "SELECT us.id_usuario, us.nombre, us.no_medidor, d.calle, d.barrio FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio WHERE us.activo = 1 AND us.id_usuario NOT IN (SELECT DISTINCT id_usuario FROM lecturas WHERE MONTH(fecha_lectura) = MONTH(CURDATE()) AND YEAR(fecha_lectura) = YEAR(CURDATE()))";

        $params = [];
        $types = '';

        if (!empty($calle)) {
            $query .= " AND d.calle LIKE ?";
            $params[] = "%$calle%";
            $types .= 's';
        }

        if (!empty($barrio)) {
            $query .= " AND d.barrio LIKE ?";
            $params[] = "%$barrio%";
            $types .= 's';
        }

        $query .= " ORDER BY us.nombre LIMIT ? OFFSET ?";
        $params[] = $limite;
        $params[] = $offset;
        $types .= 'ii';

        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuarios = [];
        while ($row = $resultado->fetch_assoc()) {
            $usuarios[] = $row;
        }

        // Contar total
        $countQuery = "SELECT COUNT(*) as total FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio WHERE us.activo = 1 AND us.id_usuario NOT IN (SELECT DISTINCT id_usuario FROM lecturas WHERE MONTH(fecha_lectura) = MONTH(CURDATE()) AND YEAR(fecha_lectura) = YEAR(CURDATE()))";

        $countParams = [];
        $countTypes = '';

        if (!empty($calle)) {
            $countQuery .= " AND d.calle LIKE ?";
            $countParams[] = "%$calle%";
            $countTypes .= 's';
        }

        if (!empty($barrio)) {
            $countQuery .= " AND d.barrio LIKE ?";
            $countParams[] = "%$barrio%";
            $countTypes .= 's';
        }

        $countStmt = $conn->prepare($countQuery);
        if (!empty($countParams)) {
            $countStmt->bind_param($countTypes, ...$countParams);
        }
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $total = $countResult->fetch_assoc()['total'];

        echo json_encode(['success' => true, 'usuarios' => $usuarios, 'total' => $total]);
        $conn->close();
        exit;
    }

    if ($action === 'get_calles') {
        $stmt = $conn->prepare("SELECT DISTINCT calle FROM domicilios WHERE calle IS NOT NULL AND calle != '' ORDER BY calle");
        $stmt->execute();
        $resultado = $stmt->get_result();
        $calles = [];
        while ($row = $resultado->fetch_assoc()) {
            $calles[] = $row['calle'];
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
            $barrios[] = $row['barrio'];
        }
        echo json_encode(['success' => true, 'barrios' => $barrios]);
        $conn->close();
        exit;
    }

    if ($action === 'get_lecturas_usuario') {
        $id_usuario = $_GET['id_usuario'] ?? '';
        if (empty($id_usuario)) {
            echo json_encode(['success' => false, 'message' => 'ID de usuario requerido']);
            exit;
        }
        // Obtener todas las lecturas del usuario, con info de pago, agrupadas por mes
        $stmt = $conn->prepare("
            SELECT l.id_lectura, l.fecha_lectura, l.consumo_m3, l.observaciones, l.lectura_actual, l.lectura_anterior,
                    IFNULL(f.estado, 'Sin Factura') AS estado_pago,
                    MONTH(l.fecha_lectura) AS mes, YEAR(l.fecha_lectura) AS anio
            FROM lecturas l
            LEFT JOIN facturas f ON l.id_lectura = f.id_lectura
            WHERE l.id_usuario = ?
            ORDER BY l.fecha_lectura DESC
        ");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $lecturas_agrupadas = [];
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        while ($row = $resultado->fetch_assoc()) {
            $mes_anio = $meses[$row['mes']] . ' ' . $row['anio'];
            if (!isset($lecturas_agrupadas[$mes_anio])) {
                $lecturas_agrupadas[$mes_anio] = [];
            }
            unset($row['mes'], $row['anio']); // Remover campos auxiliares
            $lecturas_agrupadas[$mes_anio][] = $row;
        }

        // Obtener datos del usuario independientemente de si tiene lecturas
        $stmtUsuario = $conn->prepare("SELECT nombre, no_medidor FROM usuarios_servicio WHERE id_usuario = ?");
        $stmtUsuario->bind_param("i", $id_usuario);
        $stmtUsuario->execute();
        $resUsuario = $stmtUsuario->get_result();
        $usuarioData = $resUsuario->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'lecturas' => $lecturas_agrupadas,
            'usuario' => $usuarioData // Send user info
        ]);
        $conn->close();
        exit;
    }

    if ($action === 'update_estado_pago') {
        $id_lectura = $_GET['id_lectura'] ?? '';
        $estado = $_GET['estado'] ?? '';
        if (empty($id_lectura) || empty($estado)) {
            echo json_encode(['success' => false, 'message' => 'ID de lectura y estado requeridos']);
            exit;
        }
        if (!in_array($estado, ['Pagado', 'Pendiente', 'Cancelado'])) {
            echo json_encode(['success' => false, 'message' => 'Estado inválido']);
            exit;
        }
        // Verificar si existe factura para esta lectura
        $stmtCheck = $conn->prepare("SELECT id_factura FROM facturas WHERE id_lectura = ?");
        $stmtCheck->bind_param("i", $id_lectura);
        $stmtCheck->execute();
        $resCheck = $stmtCheck->get_result();
        if ($resCheck->num_rows > 0) {
            // Actualizar estado
            $stmt = $conn->prepare("UPDATE facturas SET estado = ? WHERE id_lectura = ?");
            $stmt->bind_param("si", $estado, $id_lectura);
        } else {
            // Insertar nueva factura con estado
            $stmt = $conn->prepare("INSERT INTO facturas (id_lectura, estado, fecha_emision) VALUES (?, ?, NOW())");
            $stmt->bind_param("is", $id_lectura, $estado);
        }
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar estado']);
        }
        $conn->close();
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $id_usuario = $_POST['id_usuario'] ?? '';
    $lectura_actual = trim($_POST['lectura_actual'] ?? '');
    $fecha_lectura = $_POST['fecha_lectura'] ?? '';
    $observaciones = trim($_POST['observaciones'] ?? '');
    $id_usuario_sistema = 1; // Asumiendo admin por defecto

    if (empty($id_usuario) || empty($lectura_actual) || empty($fecha_lectura)) {
        echo json_encode(['success' => false, 'message' => 'Campos requeridos']);
        exit;
    }

    // Obtener última lectura
    $stmt = $conn->prepare("SELECT lectura_actual FROM lecturas WHERE id_usuario = ? ORDER BY id_lectura DESC LIMIT 1");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $lectura_anterior = 0;
    if ($resultado->num_rows > 0) {
        $row = $resultado->fetch_assoc();
        $lectura_anterior = $row['lectura_actual'];
    }

    $consumo = $lectura_actual - $lectura_anterior;

    // Insertar nueva lectura
    $stmt = $conn->prepare("INSERT INTO lecturas (id_usuario, fecha_lectura, lectura_anterior, lectura_actual, consumo_m3, id_usuario_sistema, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdddss", $id_usuario, $fecha_lectura, $lectura_anterior, $lectura_actual, $consumo, $id_usuario_sistema, $observaciones);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar']);
    }

    $conn->close();
    exit;
}
?>