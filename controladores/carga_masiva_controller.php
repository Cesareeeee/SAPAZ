<?php
require_once '../includes/conexion.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'guardar_masivo') {
        $calle = trim($_POST['calle'] ?? '');
        $beneficiarios = json_decode($_POST['beneficiarios'] ?? '[]', true);
        
        if (empty($calle)) {
            echo json_encode(['success' => false, 'message' => 'Debe seleccionar una calle']);
            exit;
        }
        
        if (empty($beneficiarios) || !is_array($beneficiarios)) {
            echo json_encode(['success' => false, 'message' => 'No hay beneficiarios para guardar']);
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
        
        $guardados = 0;
        $errores = [];
        $advertencias = [];
        $fecha_alta = date('Y-m-d');
        
        foreach ($beneficiarios as $index => $ben) {
            $numero_contrato = trim($ben['contrato'] ?? '');
            $numero_medidor = trim($ben['medidor'] ?? '');
            $nombre = trim($ben['nombre'] ?? '');
            
            // Validar que al menos tenga nombre
            if (empty($nombre)) {
                $errores[] = "Fila " . ($index + 1) . ": El nombre es requerido";
                continue;
            }
            
            // Convertir a NULL si están vacíos
            $numero_contrato = empty($numero_contrato) ? null : $numero_contrato;
            $numero_medidor = empty($numero_medidor) ? null : $numero_medidor;
            
            // Validar longitud de contrato (1-4 dígitos)
            if ($numero_contrato !== null && (strlen($numero_contrato) > 4 || !is_numeric($numero_contrato))) {
                $errores[] = "Fila " . ($index + 1) . ": Número de contrato inválido (máximo 4 dígitos)";
                continue;
            }
            
            // Validar longitud de medidor (8 dígitos)
            if ($numero_medidor !== null && (strlen($numero_medidor) != 8 || !is_numeric($numero_medidor))) {
                $errores[] = "Fila " . ($index + 1) . ": Número de medidor inválido (debe tener 8 dígitos)";
                continue;
            }
            
            // Verificar duplicados de contrato (solo advertir, NO bloquear)
            if ($numero_contrato !== null) {
                $check = $conn->prepare("SELECT nombre FROM usuarios_servicio WHERE no_contrato = ?");
                $check->bind_param("s", $numero_contrato);
                $check->execute();
                $res = $check->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $advertencias[] = "Fila " . ($index + 1) . ": Contrato duplicado (ya existe para: " . $row['nombre'] . ")";
                    // NO hacer continue, permitir que se guarde
                }
            }
            
            // Verificar duplicados de medidor (solo advertir, NO bloquear)
            if ($numero_medidor !== null) {
                $check = $conn->prepare("SELECT nombre FROM usuarios_servicio WHERE no_medidor = ?");
                $check->bind_param("s", $numero_medidor);
                $check->execute();
                $res = $check->get_result();
                if ($res->num_rows > 0) {
                    $row = $res->fetch_assoc();
                    $advertencias[] = "Fila " . ($index + 1) . ": Medidor duplicado (ya existe para: " . $row['nombre'] . ")";
                    // NO hacer continue, permitir que se guarde
                }
            }
            
            // Insertar beneficiario
            try {
                if ($numero_contrato === null && $numero_medidor === null) {
                    $stmt = $conn->prepare("INSERT INTO usuarios_servicio (nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?)");
                    $stmt->bind_param("sds", $nombre, $id_domicilio, $fecha_alta);
                } elseif ($numero_contrato === null) {
                    $stmt = $conn->prepare("INSERT INTO usuarios_servicio (no_medidor, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssds", $numero_medidor, $nombre, $id_domicilio, $fecha_alta);
                } elseif ($numero_medidor === null) {
                    $stmt = $conn->prepare("INSERT INTO usuarios_servicio (no_contrato, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("ssds", $numero_contrato, $nombre, $id_domicilio, $fecha_alta);
                } else {
                    $stmt = $conn->prepare("INSERT INTO usuarios_servicio (no_contrato, no_medidor, nombre, id_domicilio, fecha_alta) VALUES (?, ?, ?, ?, ?)");
                    $stmt->bind_param("sssds", $numero_contrato, $numero_medidor, $nombre, $id_domicilio, $fecha_alta);
                }
                
                if ($stmt->execute()) {
                    $guardados++;
                } else {
                    $errores[] = "Fila " . ($index + 1) . ": Error al guardar - " . $stmt->error;
                }
            } catch (Exception $e) {
                $errores[] = "Fila " . ($index + 1) . ": " . $e->getMessage();
            }
        }
        
        $conn->close();
        
        $response = [
            'success' => $guardados > 0,
            'guardados' => $guardados,
            'total' => count($beneficiarios),
            'errores' => $errores,
            'advertencias' => $advertencias
        ];
        
        echo json_encode($response);
        exit;
    }
}

// Si no es POST o acción no reconocida
echo json_encode(['success' => false, 'message' => 'Acción no válida']);
?>
