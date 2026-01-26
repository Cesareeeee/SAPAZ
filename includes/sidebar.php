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
        <span><?php echo isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 'Usuario'; ?></span>
        <img src="../recursos/imagenes/SAPAZ.jpeg" alt="Usuario">
    </div>
</header>

<aside class="sidebar" id="sidebar">
    <ul>
        <li>
            <a href="lecturas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lecturas.php' ? 'active' : ''; ?>">
                <i class="fas fa-water"></i>
                <span>Agregar Nueva Lectura</span>
            </a>
        </li>
        <li>
            <a href="historial_lecturas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'historial_lecturas.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Historial de Lecturas</span>
            </a>
        </li>
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li>
            <a href="clientes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Lista de Beneficiarios</span>
            </a>
        </li>
        <li>
            <a href="facturacion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'facturacion.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
        </li>
        <li>
            <a href="reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
        </li>
        <li>
            <a href="configuracion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </li>
        <li>
            <a href="#" id="btnCerrarSesion">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</aside>