<?php 
require_once '../includes/validar_sesion.php';
require_once '../includes/validar_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Centro de Reportes</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.0022">
    <link rel="stylesheet" href="../recursos/estilos/reportes.css?v=1.2001">
</head>
<body>
  
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title">Centro de Reportes</h2>
            <p style="margin-bottom: 2rem; color: #64748b;">Aquí puede consultar y descargar la información del sistema.</p>

            <div class="reports-container">
                
                <!-- 1. Reporte de Usuarios -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="report-info">
                            <h3>Lista de Usuarios</h3>
                            <p>Ver todas las personas que tienen servicio de agua.</p>
                        </div>
                    </div>

                    <div class="report-filters" style="margin-bottom: 1.5rem;">
                        <div class="date-group">
                            <div class="date-control" style="flex: 1;">
                                <label>Filtrar por:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-filter"></i>
                                    <select id="filtroTipoUsuarios" class="date-input" style="cursor: pointer;">
                                        <option value="">Todos</option>
                                        <option value="calle">Calle</option>
                                        <option value="barrio">Barrio</option>
                                    </select>
                                </div>
                            </div>
                            <div class="date-control" id="containerFiltroValor" style="flex: 1; display: none;">
                                <label id="labelFiltroValor">Seleccionar:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <select id="filtroValorUsuarios" class="date-input" style="cursor: pointer;">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons" style="display: flex; gap: 10px;">
                        <button class="btn-generate" id="btnReporteUsuarios">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn-generate" id="btnReporteUsuariosExcel" style="background: #217346;">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                </div>

                <!-- 2. Reporte de Lecturas -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-water"></i>
                        </div>
                        <div class="report-info">
                            <h3>Lecturas de Medidores</h3>
                            <p>Ver cuánto marcaron los medidores en las fechas que elija.</p>
                        </div>
                    </div>
                    
                    <div class="report-filters">
                        <div class="date-group">
                            <div class="date-control">
                                <label>Fecha Inicio:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="date" id="inicioLecturas" class="date-input">
                                </div>
                            </div>
                            <div class="date-control">
                                <label>Fecha Fin:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="date" id="finLecturas" class="date-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons" style="display: flex; gap: 10px;">
                        <button class="btn-generate" id="btnReporteLecturas">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn-generate" id="btnReporteLecturasExcel" style="background: #217346;">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                </div>

                <!-- 3. Reporte de Ingresos (Pagos) -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="report-info">
                            <h3>Dinero Recibido (Corte)</h3>
                            <p>Ver cuánto dinero entró por pagos de recibos.</p>
                        </div>
                    </div>
                    
                    <div class="report-filters">
                        <div class="date-group">
                            <div class="date-control">
                                <label>Fecha Inicio:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="date" id="inicioIngresos" class="date-input">
                                </div>
                            </div>
                            <div class="date-control">
                                <label>Fecha Fin:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-calendar-alt"></i>
                                    <input type="date" id="finIngresos" class="date-input">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons" style="display: flex; gap: 10px;">
                        <button class="btn-generate" id="btnReporteIngresos">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn-generate" id="btnReporteIngresosExcel" style="background: #217346;">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                </div>

                <!-- 4. Reporte de Adeudos -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="report-info">
                            <h3>Personas que Deben</h3>
                            <p>Lista de personas que tienen recibos sin pagar.</p>
                        </div>
                    </div>
                    <div class="action-buttons" style="display: flex; gap: 10px;">
                        <button class="btn-generate" id="btnReporteAdeudos">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn-generate" id="btnReporteAdeudosExcel" style="background: #217346;">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                        
                    </div>
                </div>

                <!-- 5. Historial Global -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-book-open"></i>
                        </div>
                        <div class="report-info">
                            <h3>Historial Completo (Todos)</h3>
                            <p>Toda la información de lo que ha pasado con todos los usuarios (pagos y lecturas).</p>
                            <p style="color: #ef4444; font-weight: 500; font-size: 0.9rem; margin-top: 5px;">
                                <i class="fas fa-exclamation-triangle"></i> Usar solo si es muy necesario
                            </p>
                        </div>
                    </div>

                    <div class="report-filters" style="margin-bottom: 1.5rem;">
                        <div class="date-group">
                            <div class="date-control" style="flex: 1;">
                                <label>Filtrar por:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-filter"></i>
                                    <select id="filtroTipoHistorial" class="date-input" style="cursor: pointer;">
                                        <option value="">Todos</option>
                                        <option value="calle">Calle</option>
                                        <option value="barrio">Barrio</option>
                                    </select>
                                </div>
                            </div>
                            <div class="date-control" id="containerFiltroValorHistorial" style="flex: 1; display: none;">
                                <label id="labelFiltroValorHistorial">Seleccionar:</label>
                                <div class="date-input-wrapper">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <select id="filtroValorHistorial" class="date-input" style="cursor: pointer;">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="action-buttons" style="display: flex; gap: 10px;">
                        <button class="btn-generate" id="btnReporteHistorial">
                            <i class="fas fa-file-pdf"></i>
                            PDF
                        </button>
                        <button class="btn-generate" id="btnReporteHistorialExcel" style="background: #217346;">
                            <i class="fas fa-file-excel"></i>
                            Excel
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <script src="../recursos/scripts/panel_admin.js?v=2.0222202"></script>
    <script src="../recursos/scripts/reportes.js?v=1.02222201"></script>
</body>
</html>