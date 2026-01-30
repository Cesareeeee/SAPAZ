<?php
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'get_calles') {
        $consulta = $conn->prepare("SELECT DISTINCT calle FROM domicilios ORDER BY calle");
        $consulta->execute();
        $resultado = $consulta->get_result();
        $calles = [];
        while ($fila = $resultado->fetch_assoc()) {
            $calles[] = $fila['calle'];
        }
        echo json_encode(['success' => true, 'calles' => $calles]);
        $conn->close();
        exit;
    }

    if ($action === 'get_barrios') {
        $consulta = $conn->prepare("SELECT DISTINCT barrio FROM domicilios ORDER BY barrio");
        $consulta->execute();
        $resultado = $consulta->get_result();
        $barrios = [];
        while ($fila = $resultado->fetch_assoc()) {
            $barrios[] = $fila['barrio'];
        }
        echo json_encode(['success' => true, 'barrios' => $barrios]);
        $conn->close();
        exit;
    }

    if ($action === 'get_usuario') {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        $consulta = $conn->prepare("SELECT us.*, d.calle, d.barrio FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio WHERE us.id_usuario = ?");
        $consulta->bind_param("i", $id);
        $consulta->execute();
        $resultado = $consulta->get_result();
        if ($fila = $resultado->fetch_assoc()) {
            echo json_encode(['success' => true, 'usuario' => $fila]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        $conn->close();
        exit;
    }

    if ($action === 'get_beneficiarios') {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        $sql = "SELECT us.*, d.calle, d.barrio FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio ORDER BY us.fecha_alta DESC LIMIT ? OFFSET ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result();
        $beneficiarios = [];
        while ($row = $result->fetch_assoc()) {
            $beneficiarios[] = $row;
        }
        // Get total count
        $consulta = $conn->prepare("SELECT COUNT(*) as total FROM usuarios_servicio");
        $consulta->execute();
        $resultado = $consulta->get_result();
        $fila = $resultado->fetch_assoc();
        $total = $fila['total'];
        echo json_encode(['success' => true, 'beneficiarios' => $beneficiarios, 'total' => $total, 'page' => $page, 'limit' => $limit]);
        $conn->close();
        exit;
    }

    if ($action === 'search_beneficiarios') {
        $searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
        $calle = isset($_GET['calle']) ? trim($_GET['calle']) : '';
        $barrio = isset($_GET['barrio']) ? trim($_GET['barrio']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 15;
        $offset = ($page - 1) * $limit;
        
        // Construir condiciones WHERE dinámicamente
        $conditions = [];
        $params = [];
        $types = '';
        
        if (!empty($searchTerm)) {
            $conditions[] = "(us.nombre LIKE ? OR us.no_medidor LIKE ?)";
            $searchPattern = "%{$searchTerm}%";
            $params[] = $searchPattern;
            $params[] = $searchPattern;
            $types .= 'ss';
        }
        
        if (!empty($calle)) {
            $conditions[] = "d.calle = ?";
            $params[] = $calle;
            $types .= 's';
        }
        
        if (!empty($barrio)) {
            $conditions[] = "d.barrio = ?";
            $params[] = $barrio;
            $types .= 's';
        }
        
        // Construir SQL base
        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        
        // Consulta principal con paginación
        $sql = "SELECT us.*, d.calle, d.barrio FROM usuarios_servicio us 
                JOIN domicilios d ON us.id_domicilio = d.id_domicilio 
                {$whereClause}
                ORDER BY us.fecha_alta DESC LIMIT ? OFFSET ?";
        
        $stmt = $conn->prepare($sql);
        
        // Agregar parámetros de paginación
        $params[] = $limit;
        $params[] = $offset;
        $types .= 'ii';
        
        // Bind dinámico de parámetros
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Contar total de resultados
        $countSql = "SELECT COUNT(*) as total FROM usuarios_servicio us 
                     JOIN domicilios d ON us.id_domicilio = d.id_domicilio 
                     {$whereClause}";
        
        $countStmt = $conn->prepare($countSql);
        
        // Bind parámetros para el count (sin limit y offset)
        if (!empty($conditions)) {
            $countTypes = substr($types, 0, -2); // Remover 'ii' del final
            $countParams = array_slice($params, 0, -2); // Remover limit y offset
            $countStmt->bind_param($countTypes, ...$countParams);
        }
        
        $countStmt->execute();
        $countResult = $countStmt->get_result();
        $countRow = $countResult->fetch_assoc();
        $total = $countRow['total'];
        
        $beneficiarios = [];
        while ($row = $result->fetch_assoc()) {
            $beneficiarios[] = $row;
        }
        
        echo json_encode(['success' => true, 'beneficiarios' => $beneficiarios, 'total' => $total, 'page' => $page, 'limit' => $limit]);
        $conn->close();
        exit;
    }

    if ($action === 'check_duplicate') {
        $tipo = $_GET['tipo'] ?? ''; // 'contrato' o 'medidor'
        $valor = $_GET['valor'] ?? '';
        $id_excluir = $_GET['id_excluir'] ?? null; // Para excluir en edición
        
        if (empty($tipo) || empty($valor)) {
            echo json_encode(['success' => false, 'exists' => false]);
            exit;
        }
        
        $campo = ($tipo === 'contrato') ? 'no_contrato' : 'no_medidor';
        
        if ($id_excluir) {
            $consulta = $conn->prepare("SELECT id_usuario, nombre FROM usuarios_servicio WHERE $campo = ? AND id_usuario != ?");
            $consulta->bind_param("si", $valor, $id_excluir);
        } else {
            $consulta = $conn->prepare("SELECT id_usuario, nombre FROM usuarios_servicio WHERE $campo = ?");
            $consulta->bind_param("s", $valor);
        }
        
        $consulta->execute();
        $resultado = $consulta->get_result();
        
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            echo json_encode([
                'success' => true, 
                'exists' => true, 
                'beneficiario' => $row['nombre'],
                'id' => $row['id_usuario']
            ]);
        } else {
            echo json_encode(['success' => true, 'exists' => false]);
        }
        $conn->close();
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $action = $_POST['action'] ?? 'insert';

    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }

        // Verificar si tiene lecturas
        $check = $conn->prepare("SELECT count(*) as total FROM lecturas WHERE id_usuario = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result();
        $row = $res->fetch_assoc();
        
        if ($row['total'] > 0) {
            echo json_encode(['success' => false, 'message' => 'No se puede eliminar porque este beneficiario tiene lecturas registradas.']);
            exit;
        }

        $consulta = $conn->prepare("DELETE FROM usuarios_servicio WHERE id_usuario = ?");
        $consulta->bind_param("i", $id);
        if ($consulta->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar']);
        }
        $conn->close();
        exit;
    }

    if ($action === 'update') {
        $id = $_POST['id'] ?? '';
        $numero_contrato = trim($_POST['contractNumber'] ?? '');
        $numero_medidor = trim($_POST['meterNumber'] ?? '');
        $nombre = trim($_POST['beneficiaryName'] ?? '');
        $calle = trim($_POST['streetAndNumber'] ?? '');
        $activo = $_POST['status'] ?? '1';

        // Validar solo campos obligatorios
        if (empty($id) || empty($nombre) || empty($calle)) {
            echo json_encode(['success' => false, 'message' => 'ID, nombre y calle son requeridos']);
            exit;
        }

        // Convertir a NULL si están vacíos
        $numero_contrato = empty($numero_contrato) ? null : $numero_contrato;
        $numero_medidor = empty($numero_medidor) ? null : $numero_medidor;

        // Verificar si contrato o medidor ya existe en otro registro (solo si tienen valor)
        if ($numero_contrato !== null) {
            $consulta = $conn->prepare("SELECT id_usuario FROM usuarios_servicio WHERE no_contrato = ? AND id_usuario != ?");
            $consulta->bind_param("si", $numero_contrato, $id);
            $consulta->execute();
            $resultado = $consulta->get_result();
            if ($resultado->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'El número de contrato ya existe en otro registro']);
                $conn->close();
                exit;
            }
        }
        
        if ($numero_medidor !== null) {
            $consulta = $conn->prepare("SELECT id_usuario FROM usuarios_servicio WHERE no_medidor = ? AND id_usuario != ?");
            $consulta->bind_param("si", $numero_medidor, $id);
            $consulta->execute();
            $resultado = $consulta->get_result();
            if ($resultado->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'El número de medidor ya existe en otro registro']);
                $conn->close();
                exit;
            }
        }

        // Obtener datos actuales para verificar cambio de nombre
        $consulta_actual = $conn->prepare("SELECT nombre, nombre_anterior FROM usuarios_servicio WHERE id_usuario = ?");
        $consulta_actual->bind_param("i", $id);
        $consulta_actual->execute();
        $resultado_actual = $consulta_actual->get_result();
        $datos_actuales = $resultado_actual->fetch_assoc();
        
        $nombre_anterior_db = $datos_actuales['nombre_anterior'];
        if ($nombre !== $datos_actuales['nombre']) {
            $nombre_anterior_db = $datos_actuales['nombre'];
        }

        // Obtener o crear id_domicilio
        $consulta = $conn->prepare("SELECT id_domicilio FROM domicilios WHERE calle = ?");
        $consulta->bind_param("s", $calle);
        $consulta->execute();
        $resultado = $consulta->get_result();
        if ($resultado->num_rows > 0) {
            $fila = $resultado->fetch_assoc();
            $id_domicilio = $fila['id_domicilio'];
        } else {
            $barrio = 'Centro'; // Default
            $consulta = $conn->prepare("INSERT INTO domicilios (calle, barrio) VALUES (?, ?)");
            $consulta->bind_param("ss", $calle, $barrio);
            $consulta->execute();
            $id_domicilio = $conn->insert_id;
        }

        // Actualizar
        $consulta = $conn->prepare("UPDATE usuarios_servicio SET no_contrato = ?, no_medidor = ?, nombre = ?, nombre_anterior = ?, id_domicilio = ?, activo = ? WHERE id_usuario = ?");
        $consulta->bind_param("ssssiii", $numero_contrato, $numero_medidor, $nombre, $nombre_anterior_db, $id_domicilio, $activo, $id);
        
        if ($consulta->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar']);
        }

        $conn->close();
        exit;
    }

    $numero_contrato = trim($_POST['contractNumber'] ?? '');
    $numero_medidor = trim($_POST['meterNumber'] ?? '');
    $nombre = trim($_POST['beneficiaryName'] ?? '');
    $calle = trim($_POST['streetAndNumber'] ?? '');
    $fecha_alta = trim($_POST['registrationDate'] ?? '');

    // Validar solo campos obligatorios (nombre, calle, fecha)
    if (empty($nombre) || empty($calle) || empty($fecha_alta)) {
        echo json_encode(['success' => false, 'message' => 'Nombre, calle y fecha son requeridos']);
        exit;
    }

    // Convertir a NULL si están vacíos
    $numero_contrato = empty($numero_contrato) ? null : $numero_contrato;
    $numero_medidor = empty($numero_medidor) ? null : $numero_medidor;

    // Verificar si ya existe (solo si se proporcionaron valores)
    $warnings = [];
    if ($numero_contrato !== null) {
        $consulta = $conn->prepare("SELECT id_usuario, nombre FROM usuarios_servicio WHERE no_contrato = ?");
        $consulta->bind_param("s", $numero_contrato);
        $consulta->execute();
        $resultado = $consulta->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $warnings[] = "El número de contrato ya está registrado para: " . $row['nombre'];
        }
    }
    
    if ($numero_medidor !== null) {
        $consulta = $conn->prepare("SELECT id_usuario, nombre FROM usuarios_servicio WHERE no_medidor = ?");
        $consulta->bind_param("s", $numero_medidor);
        $consulta->execute();
        $resultado = $consulta->get_result();
        if ($resultado->num_rows > 0) {
            $row = $resultado->fetch_assoc();
            $warnings[] = "El número de medidor ya está registrado para: " . $row['nombre'];
        }
    }

    // Obtener o crear id_domicilio
    $consulta = $conn->prepare("SELECT id_domicilio FROM domicilios WHERE calle = ?");
    $consulta->bind_param("s", $calle);
    $consulta->execute();
    $resultado = $consulta->get_result();
    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $id_domicilio = $fila['id_domicilio'];
    } else {
        $barrio = 'Centro'; // Default
        $consulta = $conn->prepare("INSERT INTO domicilios (calle, barrio) VALUES (?, ?)");
        $consulta->bind_param("ss", $calle, $barrio);
        $consulta->execute();
        $id_domicilio = $conn->insert_id;
    }

    // Insertar
    if ($numero_contrato === null && $numero_medidor === null) {
        // Ambos son NULL
        $consulta = $conn->prepare("INSERT INTO usuarios_servicio (nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?)");
        $consulta->bind_param("sds", $nombre, $id_domicilio, $fecha_alta);
    } elseif ($numero_contrato === null) {
        // Solo contrato es NULL
        $consulta = $conn->prepare("INSERT INTO usuarios_servicio (no_medidor, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?)");
        $consulta->bind_param("ssds", $numero_medidor, $nombre, $id_domicilio, $fecha_alta);
    } elseif ($numero_medidor === null) {
        // Solo medidor es NULL
        $consulta = $conn->prepare("INSERT INTO usuarios_servicio (no_contrato, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?)");
        $consulta->bind_param("ssds", $numero_contrato, $nombre, $id_domicilio, $fecha_alta);
    } else {
        // Ambos tienen valores
        $consulta = $conn->prepare("INSERT INTO usuarios_servicio (no_contrato, no_medidor, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?, ?)");
        $consulta->bind_param("sssds", $numero_contrato, $numero_medidor, $nombre, $id_domicilio, $fecha_alta);
    }
    
    if ($consulta->execute()) {
        $response = ['success' => true];
        if (!empty($warnings)) {
            $response['warnings'] = $warnings;
        }
        echo json_encode($response);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar']);
    }

    $conn->close();
    exit;
}
?>