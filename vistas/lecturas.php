<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Lecturas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css">
    <link rel="stylesheet" href="../recursos/estilos/lecturas.css?v=1.2">
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
                <a href="lecturas.php" class="active">
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
                <a href="configuracion.php">
                    <i class="fas fa-cog"></i>
                    <span>Configuración</span>
                </a>
            </li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title">Gestión de Lecturas</h2>

            <!-- Buscador -->
            <div class="search-section">
                <div class="form-container">
                    <h3>Buscar Medidor</h3>
                    <div class="form-group">
                        <label for="searchInput">Buscar por nombre o número de medidor</label>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder="Ingrese nombre o número de medidor">
                            <button type="button" id="clearSearch" class="btn-clear" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>

            <!-- Formulario de Lectura -->
            <div class="reading-form" id="readingForm" style="display: none;">
                <div class="form-container">
                    <h3>Registrar Nueva Lectura</h3>
                    <form id="lecturaForm">
                        <input type="hidden" id="selectedUserId" name="id_usuario">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Cliente</label>
                                <input type="text" id="clienteNombre" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label>Número de Medidor</label>
                                <input type="text" id="numeroMedidor" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Lectura Anterior</label>
                                <input type="text" id="lecturaAnterior" class="form-control" readonly>
                            </div>
                            <div class="form-group">
                                <label>Fecha Anterior</label>
                                <input type="text" id="fechaAnterior" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Lectura Actual</label>
                                <input type="number" id="lecturaActual" name="lectura_actual" class="form-control" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Fecha de Lectura</label>
                                <input type="text" id="fechaLectura" class="form-control" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Observaciones</label>
                            <textarea id="observaciones" name="observaciones" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Guardar Lectura</button>
                            <button type="button" class="btn btn-outline" id="cancelBtn">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification -->
    <div class="notification" id="notification">
        <div class="notification-icon success">
            <i class="fas fa-check"></i>
        </div>
        <div class="notification-content">
            <div class="notification-title">Éxito</div>
            <div class="notification-message">Operación completada</div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js"></script>
    <script src="../recursos/scripts/lecturas.js"></script>
</body>
</html>