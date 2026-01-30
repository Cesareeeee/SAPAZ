<?php
// Archivo de validación de permisos de administrador
// Este archivo debe incluirse en páginas que solo pueden acceder los administradores

// Primero validar que hay sesión activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}

// Verificar que el usuario sea administrador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'ADMIN') {
    // Si no es administrador, redirigir a inicio con mensaje de error
    header('Location: inicio.php?error=acceso_denegado');
    exit();
}

// Si llegamos aquí, el usuario es administrador y puede acceder a la página
?>
