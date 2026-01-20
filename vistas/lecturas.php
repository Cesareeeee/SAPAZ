<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Lecturas</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=1.1">
    <link rel="stylesheet" href="../recursos/estilos/lecturas.css?v=<?php echo time(); ?>1">
</head>
<body>
    <!-- Header and Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            <h2 class="page-title" style="margin-bottom: 1rem;">Agregar Nueva Lectura</h2>

            <!-- Buscador -->
            <div class="search-section">
                <div class="form-container">
                    <h3>Buscador de Medidor</h3>
                    <div class="form-group">
                        <label for="searchInput">Ingrese abajo el nombre del Beneficiario o Numero de medidor. Por favor</label>
                        <div class="input-group">
                            <input type="text" id="searchInput" class="form-control" placeholder=" 🔍 Ingrese nombre o número de medidor">
                            <button type="button" id="clearSearch" class="btn-clear" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <button type="button" id="cameraBtn" class="btn-camera" title="Usar cámara" style="display: none;">
                            <i class="fas fa-camera"></i> Usar Cámara
                        </button>
                    </div>
                    <div id="searchResults" class="search-results"></div>
                </div>
            </div>

            <!-- Botón para lista de beneficiarios -->
            <div style="text-align: center; margin: 1rem 0;">
                <button type="button" id="toggleBeneficiariosSection" class="btn btn-outline-secondary">
                    <i class="fas fa-users"></i> Ver Beneficiarios Sin Lectura
                </button>
            </div>

            <!-- Formulario de Lectura -->
            <div class="reading-form" id="readingForm" style="display: none;">
                <div class="form-container">
                    <h3><i class="fas fa-clipboard-list"></i> Agregar Nueva Lectura</h3>
                    <form id="lecturaForm">
                        <input type="hidden" id="selectedUserId" name="id_usuario">
                        <div class="user-info-section">
                            <div class="info-card">
                                <i class="fas fa-user info-icon"></i>
                                <div class="info-content">
                                    <label class="info-label">Beneficiario</label>
                                    <span id="clienteNombre" class="info-value"></span>
                                    <span id="clienteCalle" class="info-subvalue"></span>
                                </div>
                            </div>
                            <div class="info-card">
                                <i class="fas fa-tachometer-alt info-icon"></i>
                                <div class="info-content">
                                    <label class="info-label">Número de Medidor</label>
                                    <span id="numeroMedidor" class="info-value"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-history"></i> Lectura del Mes Anterior</label>
                                <span id="lecturaAnterior" class="info-display"></span>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Fecha Anterior</label>
                                <span id="fechaAnterior" class="info-display"></span>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="lecturaActual"><i class="fas fa-water"></i> <span style="color: red;">Por favor Ingrese Aqui la Lectura Actual *</span></label>
                                <input type="number" id="lecturaActual" name="lectura_actual" class="form-control" step="0.01" placeholder="ej. 122.5" required>
                                <span id="lecturaActualError" class="error-message"></span>
                            </div>
                            <div class="form-group">
                                <label for="fechaLectura"><i class="fas fa-calendar-day"></i> Fecha de Lectura *</label>
                                <input type="date" id="fechaLectura" name="fecha_lectura" class="form-control" required>
                                <span id="fechaLecturaError" class="error-message"></span>
                            </div>
                        </div>
                        <div class="consumption-section">
                            <div class="consumption-card">
                                <i class="fas fa-calculator info-icon"></i>
                                <div class="info-content">
                                    <label class="info-label">Metros Cúbicos Consumidos Este Mes</label>
                                    <span id="consumoCalculado" class="info-value">0.00 m³</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="button" id="toggleObservaciones" class="btn btn-outline-secondary">
                                <i class="fas fa-plus"></i> Agregar Observaciones (Opcional)
                            </button>
                            <div id="observacionesContainer" class="observaciones-container" style="display: none;">
                                <label for="observaciones"><i class="fas fa-comment"></i> Observaciones</label>
                                <textarea id="observaciones" name="observaciones" class="form-control" rows="3" placeholder="Ingrese observaciones si es necesario"></textarea>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="submitBtn" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Lectura</button>
                            <button type="button" class="btn btn-outline" id="cancelBtn"><i class="fas fa-times"></i> Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Lista de Beneficiarios Sin Lectura -->
            <div class="beneficiarios-section" id="beneficiariosSection" style="display: none;">
                <div class="form-container">
                    <h3><i class="fas fa-users"></i> Beneficiarios Sin Lectura del Mes Actual</h3>

                    <!-- Filtros -->
                    <div class="filtros-container">
                        <div class="filtros-row">
                            <div class="filtro-group">
                                <label for="filtroTipoBenef">Filtrar por:</label>
                                <select id="filtroTipoBenef">
                                    <option value="">Sin filtro</option>
                                    <option value="calle">Calle</option>
                                    <option value="barrio">Barrio</option>
                                </select>
                            </div>
                            <div class="filtro-group">
                                <label for="filtroValorBenef"><i class="fas fa-filter"></i> Valor</label>
                                <select id="filtroValorBenef" disabled>
                                    <option value="">Seleccione tipo primero</option>
                                </select>
                            </div>
                            <button type="button" id="btnLimpiarFiltrosBenef" class="btn btn-outline"><i class="fas fa-eraser"></i> Limpiar</button>
                        </div>
                    </div>

                    <!-- Cards de Beneficiarios -->
                    <div class="beneficiarios-container" id="beneficiariosContainer">
                        <!-- Cards se cargarán aquí -->
                    </div>

                    <!-- Navegación -->
                    <div class="navigation" id="navigationBenef">
                        <button id="btnPrevBenef" class="nav-btn nav-btn-disabled">
                            <i class="fas fa-chevron-left"></i> Anterior
                        </button>
                        <span id="pageInfoBenef" class="page-info">Página 1</span>
                        <button id="btnNextBenef" class="nav-btn">
                            Siguiente <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Notification -->
    <!-- Custom Modal -->
    <div class="custom-modal-backdrop" id="customModalBackdrop">
        <div class="custom-modal">
            <div class="modal-icon" id="modalIcon"></div>
            <div class="modal-title" id="modalTitle"></div>
            <div class="modal-message" id="modalMessage"></div>
            <div class="modal-actions" id="modalActions"></div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js?v=1.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/tesseract.js@4/dist/tesseract.min.js"></script>
    <script src="../recursos/scripts/lecturas.js?v=232239"></script>
</body>
</html>