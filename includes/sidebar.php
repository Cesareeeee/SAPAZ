<style>
/* Estilos Personalizados para User Info en Header */
.user-info {
    display: flex !important;
    align-items: center !important;
    gap: 12px !important;
    padding: 6px 15px !important;
    background: rgba(255, 255, 255, 0.1) !important;
    border-radius: 50px !important;
    border: 1px solid rgba(255, 255, 255, 0.15) !important;
    transition: all 0.3s ease !important;
    cursor: pointer;
}

.user-info:hover {
    background: rgba(255, 255, 255, 0.2) !important;
    border-color: rgba(255, 255, 255, 0.3) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.user-details {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    line-height: 1.2;
}

.welcome-msg {
    font-size: 0.75rem;
    color: rgba(255, 255, 255, 0.85);
    font-weight: 400;
    letter-spacing: 0.5px;
    font-family: 'Segoe UI', sans-serif;
}

.user-name {
    font-size: 0.95rem;
    font-weight: 600;
    color: #ffffff;
    text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    font-family: 'Segoe UI', sans-serif;
}

.user-avatar-link {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.user-info .user-avatar {
    width: 42px !important;
    height: 42px !important;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid var(--accent-color) !important;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

.user-info:hover .user-avatar {
    transform: scale(1.1);
    border-color: #fff !important;
}
</style>
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
        <div class="user-details">
            <span class="welcome-msg">Bienvenido,</span>
            <span class="user-name"><?php echo isset($_SESSION['nombre_completo']) ? $_SESSION['nombre_completo'] : 'Usuario'; ?></span>
        </div>
        <a href="configuracion.php" title="Ir a Configuración" class="user-avatar-link">
            <img src="../recursos/imagenes/SAPAZ.jpeg" alt="Configuración" class="user-avatar">
        </a>
    </div>
</header>

<?php
// Obtener el rol del usuario de la sesión
$rol_usuario = isset($_SESSION['rol']) ? $_SESSION['rol'] : '';
$es_admin = ($rol_usuario === 'ADMIN');
?>

<aside class="sidebar" id="sidebar">
    <ul>
        <!-- Agregar Nueva Lectura - Todos los usuarios -->
        <li>
            <a href="lecturas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'lecturas.php' ? 'active' : ''; ?>">
                <i class="fas fa-water"></i>
                <span>Agregar Nueva Lectura</span>
            </a>
        </li>
        
        <!-- Historial de Lecturas - Todos los usuarios -->
        <li>
            <a href="historial_lecturas.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'historial_lecturas.php' ? 'active' : ''; ?>">
                <i class="fas fa-history"></i>
                <span>Historial de Lecturas</span>
            </a>
        </li>
        
        <!-- Lista de Beneficiarios - Todos los usuarios -->
        <li>
            <a href="clientes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i>
                <span>Lista de Beneficiarios</span>
            </a>
        </li>
        
        <?php if ($es_admin): ?>
        <!-- Dashboard - Solo Administradores -->
        <li>
            <a href="dashboard.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>
        
        <!-- Facturación - Solo Administradores -->
        <li>
            <a href="facturacion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'facturacion.php' ? 'active' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>Facturación</span>
            </a>
        </li>
        
        <!-- Reportes - Solo Administradores -->
        <li>
            <a href="reportes.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
                <i class="fas fa-chart-bar"></i>
                <span>Reportes</span>
            </a>
        </li>
        
        <?php endif; ?>

        <!-- Configuración - Todos los usuarios -->
        <li>
            <a href="configuracion.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'configuracion.php' ? 'active' : ''; ?>">
                <i class="fas fa-cog"></i>
                <span>Configuración</span>
            </a>
        </li>
        
        <!-- Cerrar Sesión - Todos los usuarios -->
        <li>
            <a href="#" id="btnCerrarSesion">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</aside>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const logo = document.querySelector('.logo');
        if (logo) {
            logo.style.cursor = 'pointer';
            logo.addEventListener('click', (e) => {
                // If the click is purely on the logo container or its text children
                // and NOT on the menu toggle button
                if (!e.target.closest('#menuToggle')) {
                    window.location.href = '../vistas/inicio.php';
                }
            });
        }
    });
</script>