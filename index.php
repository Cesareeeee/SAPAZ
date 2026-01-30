<?php
/**
 * Archivo Index Principal - SAPAZ
 * Sistema de Agua Potable y Alcantarillado de Zecalacoayan
 * 
 * Este archivo redirige automáticamente a la página de login
 * y protege el acceso directo a la raíz del proyecto
 */

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ============================================
// CONFIGURACIÓN DEL SISTEMA EN ESPAÑOL
// ============================================

// Información del Sistema
$nombre_sistema = "SAPAZ";
$descripcion_completa = "Sistema de Agua Potable y Alcantarillado de Zecalacoayan";
$version_actual = "1.0.0";
$fecha_version = "2026-01-29";

// Rutas de Navegación
$ruta_login = "vistas/login.php";
$ruta_registro = "vistas/registro.php";
$ruta_inicio = "vistas/inicio.php";
$ruta_dashboard = "vistas/inicio.php";

// Variables de Sesión
$id_usuario_sesion = $_SESSION['user_id'] ?? null;
$nombre_usuario_sesion = $_SESSION['usuario'] ?? null;
$rol_usuario_sesion = $_SESSION['rol'] ?? null;
$nombre_completo_sesion = $_SESSION['nombre_completo'] ?? null;
$sesion_activa = isset($_SESSION['user_id']) && isset($_SESSION['rol']);

// Configuración de Seguridad
$tiempo_sesion_normal = 3600; // 1 hora en segundos
$tiempo_sesion_recordar = 30 * 24 * 60 * 60; // 30 días en segundos
$recordar_sesion = $_SESSION['remember_me'] ?? false;

// ============================================
// LÓGICA DE REDIRECCIÓN
// ============================================

// Verificar si el usuario ya tiene sesión iniciada
if ($sesion_activa) {
    // Si ya está autenticado, redirigir al dashboard
    header("Location: " . $ruta_dashboard);
    exit();
} else {
    // Si no está autenticado, redirigir al login
    header("Location: " . $ruta_login);
    exit();
}
?>
