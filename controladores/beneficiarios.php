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

        if (empty($id) || empty($numero_contrato) || empty($numero_medidor) || empty($nombre) || empty($calle)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
            exit;
        }

        // Verificar si contrato o medidor ya existe en otro registro
        $consulta = $conn->prepare("SELECT id_usuario FROM usuarios_servicio WHERE (no_contrato = ? OR no_medidor = ?) AND id_usuario != ?");
        $consulta->bind_param("ssi", $numero_contrato, $numero_medidor, $id);
        $consulta->execute();
        $resultado = $consulta->get_result();
        if ($resultado->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Contrato o medidor ya existe']);
            $conn->close();
            exit;
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

    // Validar
    if (empty($numero_contrato) || empty($numero_medidor) || empty($nombre) || empty($calle) || empty($fecha_alta)) {
        echo json_encode(['success' => false, 'message' => 'Todos los campos son requeridos']);
        exit;
    }

    // Verificar si ya existe
    $consulta = $conn->prepare("SELECT id_usuario FROM usuarios_servicio WHERE no_contrato = ? OR no_medidor = ?");
    $consulta->bind_param("ss", $numero_contrato, $numero_medidor);
    $consulta->execute();
    $resultado = $consulta->get_result();
    if ($resultado->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Contrato o medidor ya existe']);
        $conn->close();
        exit;
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
    $consulta = $conn->prepare("INSERT INTO usuarios_servicio (no_contrato, no_medidor, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?, ?)");
    $consulta->bind_param("sssds", $numero_contrato, $numero_medidor, $nombre, $id_domicilio, $fecha_alta);
    if ($consulta->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar']);
    }

    $conn->close();
    exit;
}
?>