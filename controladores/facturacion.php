<?php
require_once '../includes/conexion.php';

// Ensure table exists (Facturas)
$checkTable = $conn->query("SHOW TABLES LIKE 'facturas'");
if ($checkTable->num_rows == 0) {
    $sql = "CREATE TABLE facturas (
        id_factura INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        id_lectura INT NULL,
        fecha_emision DATETIME DEFAULT CURRENT_TIMESTAMP,
        monto_total DECIMAL(10, 2) NOT NULL,
        estado ENUM('Pendiente', 'Pagado', 'Cancelado') DEFAULT 'Pendiente',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (id_usuario),
        INDEX (id_lectura)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    if (!$conn->query($sql)) {
        die(json_encode(['success' => false, 'message' => 'Error DB Schema Facturas: ' . $conn->error]));
    }
}

// Ensure table exists (Configuracion)
$checkConfig = $conn->query("SHOW TABLES LIKE 'configuracion'");
if ($checkConfig->num_rows == 0) {
    $sqlConfig = "CREATE TABLE configuracion (
        clave VARCHAR(50) PRIMARY KEY,
        valor TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci";
    if (!$conn->query($sqlConfig)) {
        die(json_encode(['success' => false, 'message' => 'Error DB Schema Config: ' . $conn->error]));
    }
    // Insert default rate
    $conn->query("INSERT INTO configuracion (clave, valor) VALUES ('tarifa_m3', '10.00')");
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? '';

    if ($action === 'get_rate') {
        $res = $conn->query("SELECT clave, valor FROM configuracion WHERE clave IN ('tarifa_m3', 'tarifa_excedente')");
        $rate = 10.00;
        $rateExcess = 15.00; // Default excess rate
        
        while ($row = $res->fetch_assoc()) {
            if ($row['clave'] === 'tarifa_m3') $rate = floatval($row['valor']);
            if ($row['clave'] === 'tarifa_excedente') $rateExcess = floatval($row['valor']);
        }
        
        echo json_encode([
            'success' => true, 
            'rate' => $rate,
            'rate_excess' => $rateExcess
        ]);
        exit;
    }

    if ($action === 'search_users') {
        $q = $_GET['q'] ?? '';
        // Buscar usuarios e incluir la fecha de la lectura pendiente más antigua
        $sql = "SELECT u.id_usuario, u.nombre, u.no_contrato, u.no_medidor,
                (SELECT fecha_lectura FROM lecturas l 
                 WHERE l.id_usuario = u.id_usuario 
                 AND l.id_lectura NOT IN (SELECT id_lectura FROM facturas WHERE id_lectura IS NOT NULL AND estado != 'Cancelado') 
                 ORDER BY l.fecha_lectura ASC LIMIT 1) as mes_pendiente
                FROM usuarios_servicio u 
                WHERE u.nombre LIKE ? OR u.no_medidor LIKE ? 
                LIMIT 10";
        
        $stmt = $conn->prepare($sql);
        $like = "%$q%";
        $stmt->bind_param("ss", $like, $like);
        $stmt->execute();
        $res = $stmt->get_result();
        $users = [];
        while ($r = $res->fetch_assoc()) {
            if ($r['mes_pendiente']) {
                // Formatear mes para visualización (solo Mes Año)
                $date = new DateTime($r['mes_pendiente']);
                setlocale(LC_TIME, 'es_ES.UTF-8', 'es_MX.UTF-8', 'es_ES'); // Intentar configurar locale
                // Fallback manual de meses si locale falla
                $meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
                $mesIndex = intval($date->format('m')) - 1;
                $r['mes_texto'] = $meses[$mesIndex] . ' ' . $date->format('Y');
            } else {
                $r['mes_texto'] = null;
            }
            $users[] = $r;
        }
        echo json_encode(['success' => true, 'users' => $users]);
        exit;
    }

    if ($action === 'get_pending_readings') {
        $id_usuario = intval($_GET['id_usuario']);
        // Get ALL readings that are NOT billed
        $sql = "SELECT l.* FROM lecturas l 
                WHERE l.id_usuario = ? 
                AND l.id_lectura NOT IN (SELECT id_lectura FROM facturas WHERE id_lectura IS NOT NULL AND estado != 'Cancelado')
                ORDER BY l.fecha_lectura DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $readings = [];
        while ($r = $res->fetch_assoc()) {
            $readings[] = $r;
        }
        if (count($readings) > 0) {
            echo json_encode(['success' => true, 'readings' => $readings, 'count' => count($readings)]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No pending readings']);
        }
        exit;
    }

    if ($action === 'get_lectura_by_id') {
        $id_lectura = intval($_GET['id_lectura']);
        $sql = "SELECT l.*, u.nombre, u.no_contrato, u.no_medidor 
                FROM lecturas l
                JOIN usuarios_servicio u ON l.id_usuario = u.id_usuario
                WHERE l.id_lectura = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_lectura);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($r = $res->fetch_assoc()) {
             echo json_encode(['success' => true, 'lectura' => $r]);
        } else {
             echo json_encode(['success' => false]);
        }
        exit;
    }

    if ($action === 'get_single_invoice') {
        $id_factura = intval($_GET['id_factura']);
        // Join with usuarios to get details
        $sql = "SELECT f.*, us.nombre, us.no_contrato, us.no_medidor, l.consumo_m3, l.fecha_lectura 
                FROM facturas f
                JOIN usuarios_servicio us ON f.id_usuario = us.id_usuario
                LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura
                WHERE f.id_factura = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id_factura);
        $stmt->execute();
        $res = $stmt->get_result();
        $invoice = $res->fetch_assoc();
        if ($invoice) {
            echo json_encode(['success' => true, 'invoice' => $invoice]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invoice not found']);
        }
        exit;
    }

    if ($action === 'get_user_details') {
        $id_usuario = intval($_GET['id_usuario']);
        $stmt = $conn->prepare("SELECT * FROM usuarios_servicio WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();
        if ($user) {
             echo json_encode(['success' => true, 'user' => $user]);
        } else {
             echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        exit;
    }

    if ($action === 'get_invoices') {
        $id_usuario = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : null;
        
        $sql = "SELECT f.*, us.nombre, us.no_medidor, l.fecha_lectura, l.consumo_m3
                FROM facturas f 
                JOIN usuarios_servicio us ON f.id_usuario = us.id_usuario 
                LEFT JOIN lecturas l ON f.id_lectura = l.id_lectura ";
        
        if ($id_usuario) {
            $sql .= "WHERE f.id_usuario = ? ";
        }
        $sql .= "ORDER BY f.fecha_emision DESC LIMIT 100";
        
        $stmt = $conn->prepare($sql);
        if ($id_usuario) {
            $stmt->bind_param("i", $id_usuario);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $invoices = [];
        while ($r = $res->fetch_assoc()) $invoices[] = $r;
        echo json_encode(['success' => true, 'invoices' => $invoices]);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_rate') {
        $rate = floatval($_POST['rate']);
        $rateExcess = isset($_POST['rate_excess']) ? floatval($_POST['rate_excess']) : 0;
        
        if ($rate > 0) {
            $stmt = $conn->prepare("INSERT INTO configuracion (clave, valor) VALUES ('tarifa_m3', ?) ON DUPLICATE KEY UPDATE valor = ?");
            $rateStr = strval($rate);
            $stmt->bind_param("ss", $rateStr, $rateStr);
            $stmt->execute();
            
            if ($rateExcess >= 0) {
                $stmtExcess = $conn->prepare("INSERT INTO configuracion (clave, valor) VALUES ('tarifa_excedente', ?) ON DUPLICATE KEY UPDATE valor = ?");
                $rateExcessStr = strval($rateExcess);
                $stmtExcess->bind_param("ss", $rateExcessStr, $rateExcessStr);
                $stmtExcess->execute();
            }
            
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid rate']);
        }
        exit;
    }

    if ($action === 'create_invoice') {
        $id_usuario = intval($_POST['id_usuario']);
        $id_lectura = intval($_POST['id_lectura']);
        $monto = floatval($_POST['monto']);
        
        $stmt = $conn->prepare("INSERT INTO facturas (id_usuario, id_lectura, monto_total, estado) VALUES (?, ?, ?, 'Pendiente')");
        $stmt->bind_param("iid", $id_usuario, $id_lectura, $monto);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'id' => $stmt->insert_id]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error DB: ' . $conn->error]);
        }
        exit;
    }

    if ($action === 'pay_invoice') {
        $id_factura = intval($_POST['id_factura']);
        $stmt = $conn->prepare("UPDATE facturas SET estado = 'Pagado' WHERE id_factura = ?");
        $stmt->bind_param("i", $id_factura);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar pago']);
        }
        exit;
    }

    if ($action === 'revert_payment') {
        $id_factura = intval($_POST['id_factura']);
        // Validar permisos adicionales aquí si fuera necesario
        $stmt = $conn->prepare("UPDATE facturas SET estado = 'Pendiente' WHERE id_factura = ?");
        $stmt->bind_param("i", $id_factura);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al revertir pago']);
        }
        exit;
    }
}
?>
