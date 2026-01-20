<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Historial de Lecturas</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=1.0331442434">
    <link rel="stylesheet" href="../recursos/estilos/historial_lecturas.css?v=1.0134439007">
</head>
<body>
    <!-- Header and Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title">Historial de Lecturas</h2>

            <!-- Filtros de Búsqueda -->
            <div class="filtros-container">
                <div class="filtros-header">
                    <h3><i class="fas fa-filter"></i> Filtros de Búsqueda</h3>
                    <button class="btn-limpiar-filtros" id="btnLimpiarFiltros">
                        <i class="fas fa-eraser"></i> Limpiar Filtros
                    </button>
                </div>

                <div class="filtros-row">
                    <!-- Búsqueda por Nombre/Medidor -->
                    <div class="filtro-busqueda">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input
                                type="text"
                                id="inputBusqueda"
                                placeholder="Buscar por nombre o número de medidor..."
                                autocomplete="off"
                            >
                            <button class="clear-search" id="btnClearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Filtros Rápidos (Botones) -->
                    <div class="filtros-rapidos">
                        <button class="filtro-btn" id="btnConsumoAlto" data-filtro="alto">
                            <i class="fas fa-exclamation-triangle"></i>
                            <span>Consumo Alto</span>
                            <small>(> 30 m³)</small>
                        </button>
                        <button class="filtro-btn" id="btnConsumoAlterado" data-filtro="negativo">
                            <i class="fas fa-exclamation-circle"></i>
                            <span>Medidor Alterado</span>
                            <small>(Consumo negativo)</small>
                        </button>
                    </div>

                    <!-- Filtro de Orden -->
                    <div class="filtro-orden">
                        <select id="filtroOrden">
                            <option value="desc" selected>Recientes primero</option>
                            <option value="asc">Antiguas primero</option>
                        </select>
                    </div>
                </div>

                <div class="filtros-advanced">
                    <!-- Filtros de Fecha -->
                    <div class="filtros-fecha">
                        <button class="filtro-toggle-btn" id="toggleFecha">
                            <i class="fas fa-calendar-alt"></i> Fecha
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </button>
                        <div class="filtro-content" id="fechaContent" style="display: none;">
                            <div class="fecha-group">
                                <label for="filtroMes">
                                    <i class="fas fa-calendar-alt"></i> Mes
                                </label>
                                <select id="filtroMes">
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
                            </div>
                            <div class="fecha-group">
                                <label for="filtroAnio">
                                    <i class="fas fa-calendar"></i> Año
                                </label>
                                <select id="filtroAnio">
                                    <option value="">Todos los años</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros de Ubicación -->
                    <div class="filtros-ubicacion">
                        <button class="filtro-toggle-btn" id="toggleUbicacion">
                            <i class="fas fa-map-marker-alt"></i> Ubicación
                            <i class="fas fa-chevron-down toggle-icon"></i>
                        </button>
                        <div class="filtro-content" id="ubicacionContent" style="display: none;">
                            <div class="fecha-group">
                                <label for="filtroCalle">
                                    <i class="fas fa-road"></i> Calle
                                </label>
                                <select id="filtroCalle">
                                    <option value="">Todas las calles</option>
                                </select>
                            </div>
                            <div class="fecha-group">
                                <label for="filtroBarrio">
                                    <i class="fas fa-map-marker-alt"></i> Barrio
                                </label>
                                <select id="filtroBarrio">
                                    <option value="">Todos los barrios</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Indicador de Filtros Activos -->
                <div class="filtros-activos" id="filtrosActivos" style="display: none;">
                    <span class="filtros-activos-label">Filtros activos:</span>
                    <div class="filtros-activos-lista" id="filtrosActivosLista"></div>
                </div>
            </div>

            <!-- Cards de Historial -->
            <div class="cards-section">
                <div class="cards-container" id="historialContainer">
                    <!-- Cards se cargarán aquí -->
                </div>

                <!-- Navegación -->
                <div class="navigation" id="navigation">
                    <button id="btnPrev" class="nav-btn nav-btn-disabled">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </button>
                    <span id="pageInfo" class="page-info">Página 1</span>
                    <button id="btnNext" class="nav-btn">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
        <p>Cargando...</p>
    </div>

    <!-- Custom Modal -->
    <div class="custom-modal-backdrop" id="customModalBackdrop">
        <div class="custom-modal">
            <div class="modal-icon" id="modalIcon"></div>
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-message" id="modalMessage"></div>
            <div class="modal-actions" id="modalActions"></div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="edit-modal-backdrop" id="editModalBackdrop">
        <div class="edit-modal-container">
            <div class="edit-modal-header">
                <h3>Editar Lectura</h3>
                <button class="edit-close-btn" id="editCloseBtn"><i class="fas fa-times"></i></button>
            </div>
            <form id="editForm" class="edit-modal-form">
                <!-- Información del Usuario (Solo lectura) -->
                <div class="edit-info-section">
                    <div class="edit-info-item">
                        <span class="edit-info-label">Beneficiario:</span>
                        <span class="edit-info-value" id="editNombre"></span>
                    </div>
                    <div class="edit-info-item">
                        <span class="edit-info-label">Medidor:</span>
                        <span class="edit-info-value" id="editNoMedidor"></span>
                    </div>
                    <div class="edit-info-item full">
                        <span class="edit-info-label">Dirección:</span>
                        <span class="edit-info-value" id="editDireccion"></span>
                    </div>
                </div>

                <!-- Campos Editables -->
                <div class="edit-editable-section">
                    <div class="edit-field-group">
                        <label for="editFecha">Fecha de Lectura</label>
                        <input type="date" id="editFecha" required>
                    </div>
                    <div class="edit-field-group">
                        <label for="editLecturaAnterior">Lectura Anterior</label>
                        <input type="number" id="editLecturaAnterior" step="0.01" readonly>
                    </div>
                    <div class="edit-field-group editable-highlight">
                        <label for="editLecturaActual">Lectura Actual (m³)</label>
                        <input type="number" id="editLecturaActual" step="0.01" required>
                    </div>
                    <div class="edit-field-group">
                        <label for="editConsumo">Consumo (m³)</label>
                        <input type="number" id="editConsumo" step="0.01" readonly>
                    </div>
                    <div class="edit-field-group full editable-highlight">
                        <label for="editObservaciones">Observaciones</label>
                        <textarea id="editObservaciones" rows="2"></textarea>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="edit-modal-actions">
                    <button type="button" class="edit-btn edit-btn-cancel" id="editCancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="edit-btn edit-btn-save">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="view-modal-backdrop" id="viewModalBackdrop">
        <div class="view-modal-container">
            <div class="view-modal-header">
                <h3><i class="fas fa-eye"></i> Detalles de la Lectura</h3>
                <button class="view-close-btn" id="viewCloseBtn"><i class="fas fa-times"></i></button>
            </div>
            <div class="view-modal-form">
                <!-- Información del Usuario (Solo lectura) -->
                <div class="view-info-section">
                    <div class="view-info-item">
                        <span class="view-info-label">Beneficiario:</span>
                        <span class="view-info-value" id="viewNombre"></span>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Medidor:</span>
                        <span class="view-info-value" id="viewNoMedidor"></span>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Registrado por:</span>
                        <span class="view-info-value" id="viewRegistradoPor">Administrador</span>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Consumo:</span>
                        <span class="view-info-value" id="viewConsumo"></span>
                    </div>
                    <div class="view-info-item full">
                        <span class="view-info-label">Dirección:</span>
                        <span class="view-info-value" id="viewDireccion"></span>
                    </div>
                </div>

                <!-- Campos de Lectura (Solo lectura) -->
                <div class="view-editable-section">
                    <div class="view-field-group">
                        <label>ID de Lectura</label>
                        <span class="view-display" id="viewIdLectura"></span>
                    </div>
                    <div class="view-field-group">
                        <label>Fecha de Lectura</label>
                        <span class="view-display" id="viewFecha"></span>
                    </div>
                    <div class="view-field-group">
                        <label>Lectura Anterior</label>
                        <span class="view-display" id="viewLecturaAnterior"></span>
                    </div>
                    <div class="view-field-group">
                        <label>Lectura Actual (m³)</label>
                        <span class="view-display" id="viewLecturaActual"></span>
                    </div>
                    <div class="view-field-group full" style="background-color: #e3f2fd; border: 1px solid #2196f3; padding: 10px; border-radius: 4px;">
                        <label style="font-weight: bold; color: #1976d2;">Observaciones</label>
                        <span class="view-display" id="viewObservaciones"></span>
                    </div>
                    <div class="view-field-group full" style="color: #999; font-size: 0.9em;">
                        <label>Agregado el</label>
                        <span class="view-display" id="viewAgregado"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js?v=12.0343944103"></script>
    <script src="../recursos/scripts/historial_lecturas.js?v=2.0491044334"></script>
</body>
</html>