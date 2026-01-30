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
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.002">
    <link rel="stylesheet" href="../recursos/estilos/reportes.css?v=1.001">
</head>
<body>
  
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title">Centro de Reportes</h2>
            <p style="margin-bottom: 2rem; color: #64748b;">Genere y descargue reportes detallados en formato PDF.</p>

            <div class="reports-container">
                
                <!-- 1. Reporte de Usuarios -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="report-info">
                            <h3>Padrón de Usuarios</h3>
                            <p>Lista completa de todos los beneficiarios registrados y activos en el sistema.</p>
                        </div>
                    </div>
                    <button class="btn-generate" id="btnReporteUsuarios">
                        <i class="fas fa-file-pdf"></i>
                        Descargar Padrón
                    </button>
                </div>

                <!-- 2. Reporte de Lecturas -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-water"></i>
                        </div>
                        <div class="report-info">
                            <h3>Historial de Lecturas</h3>
                            <p>Registro detallado de consumos capturados en un periodo específico.</p>
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

                    <button class="btn-generate" id="btnReporteLecturas">
                        <i class="fas fa-file-pdf"></i>
                        Generar Reporte
                    </button>
                </div>

                <!-- 3. Reporte de Ingresos (Pagos) -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div class="report-info">
                            <h3>Reporte de Ingresos</h3>
                            <p>Resumen financiero de pagos recolectados por concepto de servicio de agua.</p>
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

                    <button class="btn-generate" id="btnReporteIngresos">
                        <i class="fas fa-file-pdf"></i>
                        Generar Corte de Caja
                    </button>
                </div>

                <!-- 4. Reporte de Adeudos -->
                <div class="report-card">
                    <div class="report-header">
                        <div class="report-icon-container">
                            <i class="fas fa-history"></i>
                        </div>
                        <div class="report-info">
                            <h3>Reporte de Adeudos</h3>
                            <p>Listado de usuarios con pagos pendientes o vencidos a la fecha actual.</p>
                        </div>
                    </div>
                    <button class="btn-generate" id="btnReporteAdeudos">
                        <i class="fas fa-file-pdf"></i>
                        Descargar Lista de Adeudos
                    </button>
                </div>

            </div>
        </div>
    </main>

    <script src="../recursos/scripts/panel_admin.js?v=2.002"></script>
    <script src="../recursos/scripts/reportes.js?v=1.001"></script>
</body>
</html>