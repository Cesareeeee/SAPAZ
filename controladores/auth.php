<?php
// Prevent HTML error output
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../includes/conexion.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Get JSON input
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $action = $input['action'] ?? '';
            $data = $input;
        } else {
            $action = $_POST['action'] ?? '';
            $data = $_POST;
        }

        if ($action === 'login') {
            $usuario = $conn->real_escape_string($data['usuario'] ?? '');
            $contrasena = $data['password'] ?? '';
            $recordar_sesion = isset($data['remember_me']) && $data['remember_me'] === 'true';

            if (empty($usuario) || empty($contrasena)) {
                echo json_encode(['success' => false, 'message' => 'Por favor ingrese usuario y contraseña']);
                exit;
            }

            $sql = "SELECT * FROM usuarios_sistema WHERE usuario = '$usuario' LIMIT 1";
            $resultado = $conn->query($sql);

            if ($resultado && $resultado->num_rows > 0) {
                $usuario_datos = $resultado->fetch_assoc();
                // Verificar contraseña (usando columna 'contrasena')
                if (password_verify($contrasena, $usuario_datos['contrasena'])) {
                    // Establecer sesión
                    $_SESSION['user_id'] = $usuario_datos['id_usuario_sistema'];
                    $_SESSION['usuario'] = $usuario_datos['usuario'];
                    $_SESSION['rol'] = $usuario_datos['rol'];
                    $_SESSION['nombre_completo'] = $usuario_datos['nombre'];
                    
                    // Si "recordar sesión" está marcado, extender duración de sesión
                    if ($recordar_sesion) {
                        // Establecer sesión para durar 30 días
                        $_SESSION['remember_me'] = true;
                        ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 días
                        session_set_cookie_params(30 * 24 * 60 * 60); // 30 días
                    } else {
                        // Sesión normal (expira cuando se cierra el navegador)
                        $_SESSION['remember_me'] = false;
                    }

                    echo json_encode([
                        'success' => true, 
                        'message' => 'Inicio de sesión exitoso. Bienvenido ' . $usuario_datos['nombre'] . ' (' . $usuario_datos['rol'] . ')',
                        'redirect' => '../vistas/inicio.php'
                    ]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
            exit;
        }

        if ($action === 'register') {
            $nombre = $conn->real_escape_string($data['nombre_completo'] ?? '');
            $usuario = $conn->real_escape_string($data['usuario'] ?? '');
            $contrasena = $data['password'] ?? '';
            // Convertir rol a mayúsculas para coincidir con ENUM('ADMIN', 'LECTURISTA')
            $rol = strtoupper($conn->real_escape_string($data['rol'] ?? 'lecturista'));

            if (empty($nombre) || empty($usuario) || empty($contrasena) || empty($rol)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                exit;
            }

            // Verificar si el usuario ya existe
            $verificar = $conn->query("SELECT id_usuario_sistema FROM usuarios_sistema WHERE usuario = '$usuario'");
            if ($verificar->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe']);
                exit;
            }

            // Hash de contraseña de alta seguridad (Bcrypt con costo 12)
            $opciones = ['cost' => 12];
            $contrasena_hash = password_hash($contrasena, PASSWORD_BCRYPT, $opciones);

            // Insertar usando nombres de columna correctos: nombre, contrasena
            $sql = "INSERT INTO usuarios_sistema (nombre, usuario, contrasena, rol, activo) VALUES ('$nombre', '$usuario', '$contrasena_hash', '$rol', 1)";

            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente']);
            } else {
                throw new Exception('Error DB: ' . $conn->error);
            }
            exit;
        }
    }
    
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);

} catch (Exception $e) {
    // Return error as JSON
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
