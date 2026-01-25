<?php
require_once '../controladores/beneficiarios.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Beneficiarios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=1.3332345223333333335">
    <link rel="stylesheet" href="../recursos/estilos/clientes.css?v=34223483">
</head>
<body>
   

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <!-- Clients Page -->
        <div id="beneficiariesPage" class="page-content">
            <h2 class="page-title">Padrón de Beneficiarios</h2>

            <!-- Tabs -->
            <div class="tabs">
                <button class="tab-button active" data-tab="list">Lista de Beneficiarios</button>
                <button class="tab-button" data-tab="add">Agregar Beneficiario</button>
            </div>

            <!-- Add Beneficiary Section -->
            <div id="addSection" class="tab-content">
                <div class="form-container">
                <form id="beneficiaryForm">
                    <div class="form-row"> 
                        <div class="form-group">
                            <label for="beneficiaryName">Nombre Completo</label>
                            <input type="text" class="form-control" id="beneficiaryName" name="beneficiaryName" placeholder="Nombre del beneficiario">
                        </div>
                        <div class="form-group">
                            <label for="contractNumber">Número de Contrato</label>
                            <input type="number" class="form-control" id="contractNumber" name="contractNumber" placeholder="Número de contrato">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="meterNumber">Número de Medidor</label>
                            <input type="number" class="form-control" id="meterNumber" name="meterNumber" placeholder="Número de medidor">
                        </div>
                        <div class="form-group">
                            <label for="streetAndNumber">Calle</label>
                            <select class="form-control" id="streetAndNumber" name="streetAndNumber">
                                <option value="">Selecciona una calle</option>
                            </select>
                        </div>
                    </div>

                    <input type="hidden" id="registrationDate" name="registrationDate">

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" id="saveButton" style="background-color: #0056b3; border-color: #0056b3; margin-right: 3px;">
                            <i class="fas fa-save"></i>
                            Guardar Beneficiario
                        </button>
                        <button type="button" class="btn btn-outline" id="cancelButton">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </button>
                    </div>
                </form>
                </div>
            </div>

            <!-- List Beneficiaries Section -->
            <div id="listSection" class="tab-content" style="display: none;">
                <div class="table-container">
                <div class="table-header">
                    <div class="table-title">Lista de Beneficiarios</div>
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar por nombre o medidor...">
                            <button class="clear-search" id="btnClearSearch" style="display: none;">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="streetFilter" style="color: #007bff; font-weight: bold; margin-right: 0.5rem;">Filtrar por calle:</label>
                            <select id="streetFilter" class="form-control" style="min-width: 200px;">
                                <option value="">Todas las calles</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="barrioFilter" style="color: #007bff; font-weight: bold; margin-right: 0.5rem;">Filtrar por barrio:</label>
                            <select id="barrioFilter" class="form-control" style="min-width: 200px;">
                                <option value="">Todos los barrios</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="cards-section">
                    <div class="beneficiary-header">
                        <div class="card-body">
                            <div class="card-item">
                                <div class="card-label"># Beneficiario</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label"><i class="fas fa-user"></i> Nombre</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label"><i class="fas fa-map-marker-alt"></i> Calle, Barrio</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Contrato</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label"><i class="fas fa-tachometer-alt"></i> Medidor</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Fecha Alta</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Estado</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Acciones</div>
                            </div>
                        </div>
                    </div>
                    <div class="cards-container" id="beneficiariesTableBody">
                        <!-- Beneficiarios se cargarán aquí -->
                    </div>
                    <div class="pagination">
                        <button id="prevPage" class="btn btn-outline" disabled><i class="fas fa-chevron-left"></i> Anterior</button>
                        <span id="pageInfo">Página 1</span>
                        <button id="nextPage" class="btn btn-outline">Siguiente <i class="fas fa-chevron-right"></i></button>
                    </div>
                </div>
                <div id="noSearchResults" style="display: none; text-align: center; padding: 2rem; color: var(--text-color);">
                    No se encontraron beneficiarios que coincidan con la búsqueda.
                </div>
                </div>
            </div>
        </div>
    </main>

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
                <h3>Editar Beneficiario</h3>
                <button class="edit-close-btn" id="editCloseBtn"><i class="fas fa-times"></i></button>
            </div>
            <form id="editForm" class="edit-modal-form">
                <!-- Información Inmutable o ID -->
                <div class="edit-info-section">
                    <div class="edit-info-item">
                        <span class="edit-info-label">ID Usuario:</span>
                        <span class="edit-info-value" id="editIdDisplay"></span>
                    </div>
                     <div class="edit-info-item">
                        <span class="edit-info-label">Fecha Alta:</span>
                        <span class="edit-info-value" id="editFechaDisplay"></span>
                    </div>
                </div>

                <!-- Campos Editables -->
                <div class="edit-editable-section">
                    <div class="edit-field-group full">
                         <!-- Previous Name Display Logic will be handled in JS -->
                        <div id="previousNameContainer" style="display: none; color: #9ca3af; font-size: 0.9em; margin-bottom: 5px;">
                            <i class="fas fa-history"></i> Beneficiario anterior: <span id="previousNameDisplay"></span>
                        </div>
                        <label for="editBeneficiaryName"><i class="fas fa-user"></i> Nombre Completo</label>
                        <input type="text" id="editBeneficiaryName" name="beneficiaryName" required class="form-control">
                    </div>

                    <div class="edit-field-group">
                        <label for="editContractNumber"><i class="fas fa-file-contract"></i> Número de Contrato</label>
                        <input type="number" id="editContractNumber" name="contractNumber" required class="form-control">
                    </div>

                    <div class="edit-field-group">
                        <label for="editMeterNumber"><i class="fas fa-tachometer-alt"></i> Número de Medidor</label>
                        <input type="number" id="editMeterNumber" name="meterNumber" required class="form-control">
                    </div>

                     <div class="edit-field-group">
                        <label for="editStreetAndNumber">Calle</label>
                         <select class="form-control" id="editStreetAndNumber" name="streetAndNumber" required>
                             <option value="">Selecciona una calle</option>
                         </select>
                    </div>
                    
                     <div class="edit-field-group">
                        <label for="editStatus">Estado</label>
                        <select id="editStatus" name="status">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="edit-modal-actions">
                    <button type="button" class="edit-btn edit-btn-cancel" id="editCancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="edit-btn edit-btn-save" id="saveEditButton">
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
                <h3><i class="fas fa-eye"></i> Detalles del Beneficiario</h3>
                <button type="button" class="btn btn-primary" id="viewLecturasBtn" style="margin-left: auto; margin-right: 10px;">
                    <i class="fas fa-list"></i> Ver historial de lecturas
                </button>
                <button class="view-close-btn" id="viewCloseBtn"><i class="fas fa-times"></i></button>
            </div>
            <div class="view-modal-form">
                <div class="view-info-section">
                    <div class="view-info-item">
                        <span class="view-info-label">Nombre:</span>
                        <span class="view-info-value" id="viewNombre"></span>
                        <div id="viewPreviousNameContainer" style="display: none; font-size: 0.85em; color: #6b7280; margin-top: 4px;">
                            <i class="fas fa-history"></i> Anterior: <span id="viewPreviousName"></span>
                        </div>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Contrato:</span>
                        <span class="view-info-value" id="viewContrato"></span>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Medidor:</span>
                        <span class="view-info-value" id="viewMedidor"></span>
                    </div>
                    <div class="view-info-item">
                        <span class="view-info-label">Estado:</span>
                        <span class="view-info-value" id="viewEstado"></span>
                    </div>
                    <div class="view-info-item full">
                        <span class="view-info-label">Dirección:</span>
                        <span class="view-info-value" id="viewDireccion"></span>
                    </div>
                </div>

                 <div class="view-editable-section">
                    <div class="view-field-group">
                        <label>ID Usuario</label>
                        <span class="view-display" id="viewId"></span>
                    </div>
                    <div class="view-field-group">
                        <label>Fecha Alta</label>
                        <span class="view-display" id="viewFecha"></span>
                    </div>
                 </div>
            </div>
        </div>
    </div>

    <!-- Lecturas Modal -->
    <div class="lecturas-modal-backdrop" id="lecturasModalBackdrop">
        <div class="lecturas-modal-container">
            <div class="lecturas-modal-header">
                <h3><i class="fas fa-chart-line"></i> Historial de Lecturas</h3>
                <button class="lecturas-close-btn" id="lecturasCloseBtn"><i class="fas fa-times"></i></button>
            </div>
            <div class="lecturas-modal-body">
                <div class="lecturas-info">
                    <div class="lecturas-user-info">
                        <i class="fas fa-user"></i> &nbsp;&nbsp; <span id="lecturasNombre"></span> &nbsp;&nbsp;&nbsp;&nbsp; | &nbsp;&nbsp;&nbsp;&nbsp; <i class="fas fa-tachometer-alt"></i> &nbsp;&nbsp; Medidor: &nbsp;&nbsp; <span id="lecturasMedidor" style="background-color: #87ceeb; color: black; padding: 0.25rem 0.5rem; border-radius: 4px;"></span>
                    </div>
                </div>
                <div class="lecturas-table-container" id="lecturasContainer">
                    <!-- Lecturas agrupadas por mes se cargarán aquí -->
                </div>
            </div>
        </div>
    </div>

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

    <script src="../recursos/scripts/panel_admin.js?v=1.3332420"></script>
    <script src="../recursos/scripts/validacion_beneficiarios.js?v=3464"></script>
</body>
</html>