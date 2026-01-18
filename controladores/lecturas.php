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
        $stmt = $conn->prepare("SELECT lectura_actual, fecha_lectura FROM lecturas WHERE id_usuario = ? ORDER BY fecha_lectura DESC LIMIT 1");
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
    $stmt = $conn->prepare("SELECT lectura_actual FROM lecturas WHERE id_usuario = ? ORDER BY fecha_lectura DESC LIMIT 1");
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