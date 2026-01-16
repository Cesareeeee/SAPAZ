<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Configuración</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css">
</head>
<body>
    <!-- Header -->
    <header>
        <div class="logo">
            <button class="menu-toggle" id="menuToggle">
                <i class="fas fa-bars"></i>
            </button>
            <i class="fas fa-tint"></i>
            <h1>S.A.P.A.Z</h1>
            <span class="full-name">SISTEMA DE AGUA POTABLE Y ALCANTARILLADO DE ZECALACOAYAN</span>
        </div>
        <div class="user-info">
            <span>Administrador</span>
            <img src="../recursos/imagenes/SAPAZ.jpeg" alt="Usuario">
        </div>
    </header>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <ul>
            <li>
                <a href="lecturas.php">
                    <i class="fas fa-water"></i>
                    <span>Lecturas</span>
                </a>
            </li>
            <li>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="clientes.php">
                    <i class="fas fa-users"></i>
                    <span>Beneficiarios</span>
                </a>
            </li>
            <li>
                <a href="facturacion.php">
                    <i class="fas fa-file-invoice-dollar"></i>
                    <span>Facturación</span>
                </a>
            </li>
            <li>
                <a href="reportes.php">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reportes</span>
                </a>
            </li>
            <li>
                <a href="configuracion.php" class="active">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title">Configuración</h2>
            <p>Contenido de configuración próximamente.</p>
        </div>
    </main>

    <script src="../recursos/scripts/panel_admin.js"></script>
</body>
</html>