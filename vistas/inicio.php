<?php require_once '../includes/validar_sesion.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Inicio</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.0">
    <link rel="stylesheet" href="../recursos/estilos/inicio.css?v=1.0">
</head>
<body class="inicio-page">

    <!-- Sidebar (Initally hidden/minimized on this page as per request) -->
    <?php include '../includes/sidebar.php'; ?>

    <main class="main-content" id="mainContent">
        <div class="welcome-container">
            <h1 class="welcome-title">Bienvenido a S.A.P.A.Z</h1>
            <p class="welcome-subtitle">Seleccione una opción para continuar</p>
        </div>

        <?php
        // Obtener el rol del usuario de la sesión
        $rol_usuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
        $es_admin = ($rol_usuario === 'ADMIN');
        ?>

        <div class="menu-cards-container">
            <!-- Agregar Nueva Lectura - Todos los usuarios -->
            <a href="lecturas.php" class="menu-card color-blue">
                <div class="card-icon">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="card-info">
                    <h3>Agregar Nueva Lectura</h3>
                    <p>Registrar consumo del mes</p>
                </div>
            </a>

            <!-- Historial - Todos los usuarios -->
            <a href="historial_lecturas.php" class="menu-card color-purple">
                <div class="card-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="card-info">
                    <h3>Historial</h3>
                    <p>Consultar lecturas pasadas</p>
                </div>
            </a>

            <!-- Beneficiarios - Todos los usuarios -->
            <a href="clientes.php" class="menu-card color-green">
                <div class="card-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="card-info">
                    <h3>Beneficiarios</h3>
                    <p>Lista de clientes</p>
                </div>
            </a>

            <!-- Agregar Beneficiario - Todos los usuarios -->
            <a href="clientes.php?tab=add" class="menu-card color-red">
                <div class="card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="card-info">
                    <h3>Agregar Beneficiario</h3>
                    <p>Registrar nuevo cliente</p>
                </div>
            </a>

            <?php if ($es_admin): ?>
            <!-- Dashboard - Solo Administradores -->
            <a href="dashboard.php" class="menu-card color-indigo">
                <div class="card-icon">
                    <i class="fas fa-tachometer-alt"></i>
                </div>
                <div class="card-info">
                    <h3>Dashboard</h3>
                    <p>Estadísticas y resumen</p>
                </div>
            </a>

            <!-- Facturación - Solo Administradores -->
            <a href="facturacion.php" class="menu-card color-teal">
                <div class="card-icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="card-info">
                    <h3>Facturación</h3>
                    <p>Gestión de pagos y cajas</p>
                </div>
            </a>

            <!-- Reportes - Solo Administradores -->
            <a href="reportes.php" class="menu-card color-orange">
                <div class="card-icon">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="card-info">
                    <h3>Reportes</h3>
                    <p>Informes detallados</p>
                </div>
            </a>

            <!-- Configuración - Solo Administradores -->
            <a href="configuracion.php" class="menu-card color-gray">
                <div class="card-icon">
                    <i class="fas fa-cog"></i>
                </div>
                <div class="card-info">
                    <h3>Configuración</h3>
                    <p>Ajustes del sistema</p>
                </div>
            </a>
            <?php endif; ?>
        </div>
    </main>

    <script src="../recursos/scripts/panel_admin.js?v=2.0"></script>
    <script src="../recursos/scripts/inicio.js?v=1.0"></script>
</body>
</html>
