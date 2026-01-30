<?php
// Desactivar salida de errores HTML
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();
require_once '../includes/conexion.php';

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Obtener entrada JSON
        $input = json_decode(file_get_contents('php://input'), true);
        if ($input) {
            $accion = $input['accion'] ?? '';
            $datos = $input;
        } else {
            $accion = $_POST['accion'] ?? '';
            $datos = $_POST;
        }

        // Obtener ID del usuario actual de la sesión
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['exito' => false, 'mensaje' => 'Sesión no iniciada']);
            exit;
        }
        $id_usuario = $_SESSION['user_id'];

        // Acción: Obtener datos del usuario
        if ($accion === 'obtener_datos') {
            $sql = "SELECT nombre, usuario, rol FROM usuarios_sistema WHERE id_usuario_sistema = $id_usuario";
            $resultado = $conn->query($sql);
            
            if ($resultado && $resultado->num_rows > 0) {
                $usuario_datos = $resultado->fetch_assoc();
                echo json_encode(['exito' => true, 'datos' => $usuario_datos]);
            } else {
                echo json_encode(['exito' => false, 'mensaje' => 'Usuario no encontrado']);
            }
            exit;
        }

        // Acción: Actualizar Información Personal (Nombre y Usuario)
        if ($accion === 'actualizar_info') {
            $nombre = $conn->real_escape_string($datos['nombre'] ?? '');
            $usuario = $conn->real_escape_string($datos['usuario'] ?? '');

            // Validaciones básicas
            if (empty($nombre) || empty($usuario)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Nombre y usuario son obligatorios']);
                exit;
            }

            // Verificar si el nombre de usuario ya está en uso por otro usuario
            $check_sql = "SELECT id_usuario_sistema FROM usuarios_sistema WHERE usuario = '$usuario' AND id_usuario_sistema != $id_usuario";
            $check_result = $conn->query($check_sql);
            if ($check_result && $check_result->num_rows > 0) {
                echo json_encode(['exito' => false, 'mensaje' => 'El nombre de usuario ya está en uso']);
                exit;
            }

            // Actualizar solo nombre y usuario
            $sql = "UPDATE usuarios_sistema SET nombre = '$nombre', usuario = '$usuario' WHERE id_usuario_sistema = $id_usuario";

            if ($conn->query($sql)) {
                // Actualizar sesión
                $_SESSION['usuario'] = $usuario;
                $_SESSION['nombre_completo'] = $nombre;
                
                echo json_encode(['exito' => true, 'mensaje' => 'Información actualizada correctamente']);
            } else {
                throw new Exception('Error en la base de datos: ' . $conn->error);
            }
            exit;
        }

        // Acción: Actualizar Contraseña
        if ($accion === 'actualizar_password') {
            $contrasena_actual = $datos['contrasena_actual'] ?? '';
            $nueva_contrasena = $datos['nueva_contrasena'] ?? '';
            $confirmar_contrasena = $datos['confirmar_contrasena'] ?? '';

            if (empty($contrasena_actual) || empty($nueva_contrasena)) {
                echo json_encode(['exito' => false, 'mensaje' => 'Todos los campos de contraseña son obligatorios']);
                exit;
            }

            // Verificar contraseña actual
            $sql_pass = "SELECT contrasena FROM usuarios_sistema WHERE id_usuario_sistema = $id_usuario";
            $res_pass = $conn->query($sql_pass);
            $row_pass = $res_pass->fetch_assoc();

            if (!password_verify($contrasena_actual, $row_pass['contrasena'])) {
                echo json_encode(['exito' => false, 'mensaje' => 'La contraseña actual es incorrecta']);
                exit;
            }

            if ($nueva_contrasena !== $confirmar_contrasena) {
                echo json_encode(['exito' => false, 'mensaje' => 'Las nuevas contraseñas no coinciden']);
                exit;
            }

            // Hash de la nueva contraseña
            $opciones = ['cost' => 12];
            $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_BCRYPT, $opciones);
            
            $sql = "UPDATE usuarios_sistema SET contrasena = '$hash_contrasena' WHERE id_usuario_sistema = $id_usuario";

            if ($conn->query($sql)) {
                echo json_encode(['exito' => true, 'mensaje' => 'Contraseña actualizada correctamente']);
            } else {
                throw new Exception('Error en la base de datos: ' . $conn->error);
            }
            exit;
        }
    }
    
    echo json_encode(['exito' => false, 'mensaje' => 'Acción no válida']);

} catch (Exception $e) {
    echo json_encode(['exito' => false, 'mensaje' => 'Error del servidor: ' . $e->getMessage()]);
}
?>
