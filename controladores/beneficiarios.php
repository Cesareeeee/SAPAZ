<?php
require_once '../includes/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    $action = $_GET['action'];

    if ($action === 'get') {
        $id = $_GET['id'] ?? '';
        if (empty($id)) {
            echo json_encode(['success' => false, 'message' => 'ID requerido']);
            exit;
        }
        $stmt = $conn->prepare("SELECT us.*, d.calle FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio WHERE us.id_usuario = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        if ($resultado->num_rows > 0) {
            $beneficiario = $resultado->fetch_assoc();
            echo json_encode(['success' => true, 'beneficiario' => $beneficiario]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Beneficiario no encontrado']);
        }
        $conn->close();
        exit;
    }

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
        $consulta = $conn->prepare("UPDATE usuarios_servicio SET no_contrato = ?, no_medidor = ?, nombre = ?, nome_anterior = ?, id_domicilio = ?, activo = ? WHERE id_usuario = ?");
        // Note: I need to make sure column name is correct. I used 'nombre_anterior'. 
        // Wait, the previous turn script used 'nombre_anterior'.
        // Let me check the column name I created.
        // I ran `ALTER TABLE ... ADD COLUMN nombre_anterior ...`.
        // So column is `nombre_anterior`.
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