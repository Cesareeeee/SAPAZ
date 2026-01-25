<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Facturación Premium</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=14.330">
    <link rel="stylesheet" href="../recursos/estilos/facturacion.css?v=4.2">
</head>
<body>
  
    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
<!-- ... (contenido intermedio sin cambios para esta llamada) ... -->
    <script src="../recursos/scripts/panel_admin.js"></script>
    <script src="../recursos/scripts/facturacion.js?v=4.0"></script>
</body>
</html>
        <div class="facturacion-container">
            
            <!-- Configuración de Tarifa (Arriba de todo) -->
            <div class="rate-config-card">
                <div class="rate-config-header">
                    <div class="rate-config-title-section">
                        <i class="fas fa-dollar-sign rate-icon"></i>
                        <div>
                            <h3 class="rate-config-title">Tarifa por Metro Cúbico</h3>
                            <p class="rate-config-subtitle">Configura el precio base para el cálculo de facturas</p>
                        </div>
                    </div>
                    <button id="btnEditRate" class="btn-edit-rate">
                        <i class="fas fa-edit"></i> Editar Tarifa
                    </button>
                </div>
                <div class="rate-config-content" id="rateDisplaySection">
                    <div class="rate-display">
                        <span class="rate-label">Precio actual:</span>
                        <span class="rate-value" id="rateDisplay">$10.00</span>
                        <span class="rate-unit">por m³</span>
                    </div>
                </div>
                <div class="rate-config-edit" id="rateEditSection" style="display: none;">
                    <div class="rate-edit-group">
                        <label class="rate-edit-label">Nueva tarifa por m³:</label>
                        <div class="rate-input-wrapper">
                            <span class="currency-symbol">$</span>
                            <input type="number" id="ratePerM3" class="rate-edit-input" placeholder="10.00" step="0.01" min="0" value="10.00">
                            <span class="rate-edit-unit">/ m³</span>
                        </div>
                    </div>
                    <div class="rate-edit-actions">
                        <button id="btnCancelRate" class="btn-cancel-rate">
                            <i class="fas fa-times"></i> Cancelar
                        </button>
                        <button id="btnSaveRate" class="btn-save-rate">
                            <i class="fas fa-check"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </div>

            <!-- Left: Generator -->
            <div class="generator-panel card">
                <div class="panel-header">
                    <h2 class="panel-title"><i class="fas fa-file-invoice-dollar"></i> Generar Factura</h2>
                </div>

                <div class="form-group">
                    <label class="form-label">Buscar Usuario</label>
                    <div class="search-wrapper">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" id="userSearchInput" class="search-input" placeholder="Nombre, Contrato o Medidor...">
                        <button id="btnClearSearch" class="btn-clear-search" style="display: none;" title="Limpiar búsqueda">
                            <i class="fas fa-times"></i>
                        </button>
                        <div id="searchLoader" class="search-loader" style="display: none;">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div id="userSearchResults" class="user-results"></div>
                    </div>
                </div>

                <div class="invoice-form">
                    <div class="form-group">
                        <span class="form-label">Cliente Seleccionado</span>
                        <div class="form-value" id="clientName">-</div>
                    </div>
                    <div class="form-group">
                        <span class="form-label">Contrato</span>
                        <div class="form-value" id="clientContract">-</div>
                    </div>

                    <div style="border-top: 2px dashed #e5e7eb; margin: 0.5rem 0;"></div>

                    <!-- Lecturas Pendientes -->
                    <div id="pendingReadingsSection" style="display: none;">
                        <div class="form-group">
                            <span class="form-label">Lecturas Pendientes de Pago</span>
                            <div id="pendingReadingsList" class="pending-readings-list">
                                <!-- Se llenarán dinámicamente -->
                            </div>
                        </div>
                    </div>

                    <!-- Información de lectura seleccionada -->
                    <div id="selectedReadingSection" style="display: none;">
                        <div class="form-group">
                            <span class="form-label">Periodo de Lectura</span>
                            <div class="form-value" id="readingPeriod">-</div>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Consumo Registrado</span>
                            <div class="form-value" id="consumption">-</div>
                        </div>
                        <div class="form-group">
                            <span class="form-label">Lectura Actual</span>
                            <div class="form-value" id="currentReading">-</div>
                        </div>
                        <div id="observationsSection" class="form-group" style="display: none;">
                            <span class="form-label">Observaciones</span>
                            <div class="form-value observations-value" id="observations">-</div>
                        </div>

                        <div class="amount-display">
                            <span class="amount-label">Total a Pagar</span>
                            <div class="amount-value" id="totalAmount">$0.00</div>
                        </div>

                        <button id="btnGenerate" class="btn-generate" disabled>
                            <i class="fas fa-magic"></i> Generar Factura
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right: History -->
            <div class="history-panel card">
                <div class="history-header">
                    <h2 class="panel-title"><i class="fas fa-history"></i> Historial de Facturas</h2>
                    <div class="history-filters">
                        <select id="filterMonth" class="filter-select">
                            <option value="">Todos los meses</option>
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
                        <select id="filterYear" class="filter-select">
                            <option value="">Todos los años</option>
                        </select>
                    </div>
                </div>

                <div class="invoices-list" id="invoicesList">
                    <!-- Loaded via JS -->
                    <div style="text-align: center; color: #9ca3af; padding: 2rem;">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
                </div>

                <!-- Paginación -->
                <div class="pagination-controls" id="paginationControls" style="display: none;">
                    <button id="btnPrevPage" class="btn-pagination" disabled>
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <span id="pageInfo" class="page-info">Página 1</span>
                    <button id="btnNextPage" class="btn-pagination">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>

        </div>
    </main>

    <!-- Modal de Visualización de Ticket -->
    <div id="ticketModal" class="ticket-modal" style="display: none;">
        <div class="ticket-modal-overlay"></div>
        <div class="ticket-modal-content">
            <div class="ticket-modal-header">
                <h3>Vista Previa del Ticket</h3>
                <button class="btn-close-ticket" id="btnCloseTicket"><i class="fas fa-times"></i></button>
            </div>
            <div class="ticket-preview-area" id="ticketPreviewArea">
                <!-- Aquí se inyectar el HTML del ticket -->
            </div>
            <div class="ticket-modal-actions">
                <button class="btn-ticket-cancel" id="btnCancelTicket">Cerrar</button>
                <button class="btn-ticket-print" id="btnPrintTicket">
                    <i class="fas fa-print"></i> Imprimir Ticket
                </button>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js"></script>
    <script src="../recursos/scripts/facturacion.js?v=4.2"></script>
</body>
</html>