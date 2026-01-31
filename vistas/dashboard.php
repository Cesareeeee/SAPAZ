<?php 
require_once '../includes/validar_sesion.php';
require_once '../includes/validar_admin.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Dashboard</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.002">
    <link rel="stylesheet" href="../recursos/estilos/dashboard.css?v=1.0">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
 

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Dashboard Page -->

        <div id="dashboardPage" class="page-content">
            <h2 class="page-title">Dashboard General</h2>

            <!-- Dashboard Cards Grid -->
            <div class="dashboard-cards">
                <!-- Total Usuarios -->
                <div class="card card-animate">
                    <div class="card-icon-container">
                        <div class="card-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-content-center">
                        <div class="card-value-big" id="totalClientes">--</div>
                        <div class="card-label">Usuarios Activos</div>
                    </div>
                </div>

                <!-- Lecturas del Mes -->
                <div class="card card-animate delay-1">
                    <div class="card-icon-container">
                        <div class="card-icon turquoise">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                    <div class="card-content-center">
                        <div class="card-value-big" id="lecturasMes">--</div>
                        <div class="card-label">Lecturas Este Mes</div>
                    </div>
                </div>

                <!-- Ingresos del Mes -->
                <div class="card card-animate delay-2">
                    <div class="card-icon-container">
                        <div class="card-icon green">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                    </div>
                    <div class="card-content-center">
                        <div class="card-value-big" id="montoCobrado">$0</div>
                        <div class="card-label">Cobrado Este Mes</div>
                    </div>
                </div>

                <!-- Deuda Histórica -->
                <div class="card card-animate delay-3">
                    <div class="card-icon-container">
                        <div class="card-icon red">
                            <i class="fas fa-exclamation-circle"></i>
                        </div>
                    </div>
                    <div class="card-content-center">
                        <div class="card-value-big" id="deudaHistorica">$0</div>
                        <div class="card-label">Deuda Total Acumulada</div>
                    </div>
                </div>
            </div>

            <!-- Income Chart Section -->
            <div class="chart-section card-animate delay-4">
                <div class="chart-header-row">
                    <div class="chart-title-group">
                        <h3 class="section-title">Análisis de Ingresos</h3>
                        <p class="section-subtitle" id="incomeChartTitle">Ingresos Mensuales</p>
                    </div>
                    <div class="chart-filters">
                        <select id="yearFilter" class="filter-select">
                            <?php 
                            $currentYear = date('Y');
                            for($i = $currentYear; $i >= $currentYear - 4; $i--) {
                                echo "<option value='$i'>$i</option>";
                            }
                            ?>
                        </select>
                        <select id="monthFilter" class="filter-select">
                            <option value="all">Todo el Año</option>
                            <option value="1">Enero</option>
                            <option value="2">Febrero</option>
                            <option value="3">Marzo</option>
                            <option value="4">Abril</option>
                            <option value="5">Mayo</option>
                            <option value="6">Junio</option>
                            <option value="7">Julio</option>
                            <option value="8">Agosto</option>
                            <option value="9">Septiembre</option>
                            <option value="10">Octubre</option>
                            <option value="11">Noviembre</option>
                            <option value="12">Diciembre</option>
                        </select>
                        <select id="dayFilter" class="filter-select" style="display: none;">
                            <option value="all">Todo el Mes</option>
                            <!-- Days populated by JS -->
                        </select>
                        <button id="applyFiltersBtn" class="btn-filter">
                            <i class="fas fa-filter"></i> Filtrar
                        </button>
                    </div>
                </div>
                <div class="chart-wrapper big-chart">
                    <canvas id="incomeChart"></canvas>
                </div>
            </div>

            <div class="grid-2-col">
                <!-- Consumption Chart -->
                <div class="chart-container card-animate delay-5">
                    <div class="chart-header">
                        <div class="chart-title">Consumo de Agua (m³)</div>
                    </div>
                    <div class="chart-wrapper">
                        <canvas id="consumptionChart"></canvas>
                    </div>
                </div>

                <!-- Recent Readings Table -->
                <div class="table-container card-animate delay-5">
                    <div class="table-header">
                        <div class="table-title">Últimas Lecturas</div>
                        <div class="actions">
                            <a href="lecturas.php" class="btn-sm">Ver todas</a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Cta/Medidor</th>
                                    <th>Cliente</th>
                                    <th>Consumo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody id="recentReadingsTable">
                                <tr>
                                    <td colspan="4" class="text-center">Cargando datos...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="../recursos/scripts/panel_admin.js?v=2.002"></script>
    <script src="../recursos/scripts/dashboard.js?v=1.0"></script>
</body>
</html>
