<?php
// Archivo de validación de sesión
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id']) || !isset($_SESSION['usuario'])) {
    // Si no hay sesión activa, redirigir al login
    header('Location: login.php');
    exit();
}

// Variables de sesión disponibles para usar en las páginas:
// $_SESSION['user_id'] - ID del usuario
// $_SESSION['usuario'] - Nombre de usuario
// $_SESSION['rol'] - Rol (ADMIN o LECTURISTA)
// $_SESSION['nombre_completo'] - Nombre completo del usuario
?>
