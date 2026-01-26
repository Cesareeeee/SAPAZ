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
            $password = $data['password'] ?? '';
            $remember_me = isset($data['remember_me']) && $data['remember_me'] === 'true';

            if (empty($usuario) || empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Por favor ingrese usuario y contraseña']);
                exit;
            }

            $sql = "SELECT * FROM usuarios_sistema WHERE usuario = '$usuario' LIMIT 1";
            $result = $conn->query($sql);

            if ($result && $result->num_rows > 0) {
                $user = $result->fetch_assoc();
                // Verify password (using 'contrasena' column)
                if (password_verify($password, $user['contrasena'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id_usuario_sistema'];
                    $_SESSION['usuario'] = $user['usuario'];
                    $_SESSION['rol'] = $user['rol'];
                    $_SESSION['nombre_completo'] = $user['nombre'];
                    
                    // If remember me is checked, extend session lifetime
                    if ($remember_me) {
                        // Set session to last 30 days
                        $_SESSION['remember_me'] = true;
                        ini_set('session.gc_maxlifetime', 30 * 24 * 60 * 60); // 30 days
                        session_set_cookie_params(30 * 24 * 60 * 60); // 30 days
                    } else {
                        // Normal session (expires when browser closes)
                        $_SESSION['remember_me'] = false;
                    }

                    echo json_encode([
                        'success' => true, 
                        'message' => 'Inicio de sesión exitoso. Bienvenido ' . $user['nombre'] . ' (' . $user['rol'] . ')',
                        'redirect' => '../vistas/lecturas.php'
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
            $password = $data['password'] ?? '';
            // Convert role to uppercase to match ENUM('ADMIN', 'LECTURISTA')
            $rol = strtoupper($conn->real_escape_string($data['rol'] ?? 'lecturista'));

            if (empty($nombre) || empty($usuario) || empty($password) || empty($rol)) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                exit;
            }

            // Check if user exists
            $check = $conn->query("SELECT id_usuario_sistema FROM usuarios_sistema WHERE usuario = '$usuario'");
            if ($check->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya existe']);
                exit;
            }

            // High Security Password Hashing (Bcrypt with Cost 12)
            $options = ['cost' => 12];
            $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

            // Insert using correct column names: nombre, contrasena
            $sql = "INSERT INTO usuarios_sistema (nombre, usuario, contrasena, rol, activo) VALUES ('$nombre', '$usuario', '$hashed_password', '$rol', 1)";

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
