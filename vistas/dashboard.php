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
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.002">
</head>
<body>
 

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Dashboard Page -->
        <div id="dashboardPage" class="page-content">
            <h2 class="page-title">Dashboard</h2>

            <!-- Dashboard Cards -->
            <div class="dashboard-cards">
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Total de Clientes</div>
                        <div class="card-icon blue">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <div class="card-value">2,456</div>
                    <div class="card-description">+5.2% desde el mes pasado</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Lecturas del Mes</div>
                        <div class="card-icon turquoise">
                            <i class="fas fa-tint"></i>
                        </div>
                    </div>
                    <div class="card-value">2,103</div>
                    <div class="card-description">85.6% de cobertura</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Facturas Pagadas</div>
                        <div class="card-icon primary">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="card-value">1,892</div>
                    <div class="card-description">90% de pago oportuno</div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div class="card-title">Consumo Promedio</div>
                        <div class="card-icon blue">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="card-value">24.5 m³</div>
                    <div class="card-description">-2.3% vs. mes anterior</div>
                </div>
            </div>

            <!-- Chart -->
            <div class="chart-container">
                <div class="chart-header">
                    <div class="chart-title">Consumo Mensual</div>
                    <div class="chart-options">
                        <button class="chart-option active">Año</button>
                        <button class="chart-option">Mes</button>
                        <button class="chart-option">Semana</button>
                    </div>
                </div>
                <div class="chart">
                    <canvas id="consumptionChart"></canvas>
                </div>
            </div>

            <!-- Recent Readings Table -->
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Lecturas Recientes</div>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Buscar lecturas...">
                    </div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Medidor</th>
                            <th>Lectura Actual</th>
                            <th>Lectura Anterior</th>
                            <th>Consumo</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>#1023</td>
                            <td>Juan Pérez</td>
                            <td>M-1023</td>
                            <td>1,245 m³</td>
                            <td>1,220 m³</td>
                            <td>25 m³</td>
                            <td>15/06/2023</td>
                            <td><span class="status paid">Pagado</span></td>
                            <td>
                                <button class="btn btn-outline">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#1024</td>
                            <td>María García</td>
                            <td>M-1024</td>
                            <td>980 m³</td>
                            <td>950 m³</td>
                            <td>30 m³</td>
                            <td>15/06/2023</td>
                            <td><span class="status pending">Pendiente</span></td>
                            <td>
                                <button class="btn btn-outline">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#1025</td>
                            <td>Carlos López</td>
                            <td>M-1025</td>
                            <td>1,530 m³</td>
                            <td>1,500 m³</td>
                            <td>30 m³</td>
                            <td>14/06/2023</td>
                            <td><span class="status paid">Pagado</span></td>
                            <td>
                                <button class="btn btn-outline">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>#1026</td>
                            <td>Ana Martínez</td>
                            <td>M-1026</td>
                            <td>875 m³</td>
                            <td>850 m³</td>
                            <td>25 m³</td>
                            <td>14/06/2023</td>
                            <td><span class="status overdue">Vencido</span></td>
                            <td>
                                <button class="btn btn-outline">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
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
            <div class="notification-message">La operación se completó correctamente</div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal" id="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div class="modal-title">Detalles de la Lectura</div>
                <button class="modal-close" id="modalClose">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>ID de Lectura</label>
                    <input type="text" class="form-control" value="#1023" readonly>
                </div>
                <div class="form-group">
                    <label>Cliente</label>
                    <input type="text" class="form-control" value="Juan Pérez" readonly>
                </div>
                <div class="form-group">
                    <label>Medidor</label>
                    <input type="text" class="form-control" value="M-1023" readonly>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Lectura Actual</label>
                        <input type="text" class="form-control" value="1,245 m³" readonly>
                    </div>
                    <div class="form-group">
                        <label>Lectura Anterior</label>
                        <input type="text" class="form-control" value="1,220 m³" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label>Consumo</label>
                    <input type="text" class="form-control" value="25 m³" readonly>
                </div>
                <div class="form-group">
                    <label>Fecha</label>
                    <input type="text" class="form-control" value="15/06/2023" readonly>
                </div>
                <div class="form-group">
                    <label>Estado</label>
                    <input type="text" class="form-control" value="Pagado" readonly>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" id="modalCancel">Cerrar</button>
                <button class="btn btn-primary">Imprimir</button>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js?v=2.002"></script>
</body>
</html>