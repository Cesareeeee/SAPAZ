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
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=1.30">
    <link rel="stylesheet" href="../recursos/estilos/clientes.css?v=1.333320">
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
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="streetFilter" style="color: #007bff; font-weight: bold; margin-right: 0.5rem;">Filtrar por calle:</label>
                            <select id="streetFilter" class="form-control" style="min-width: 200px;">
                                <option value="">Todas las calles</option>
                            </select>
                        </div>
                        <div class="search-box">
                            <i class="fas fa-search"></i>
                            <input type="text" id="searchInput" placeholder="Buscar por nombre o medidor...">
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
                                <div class="card-label">Nombre</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Calle</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Contrato</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Medidor</div>
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
                        <?php
                        require_once '../includes/conexion.php';
                        $sql = "SELECT us.*, d.calle FROM usuarios_servicio us JOIN domicilios d ON us.id_domicilio = d.id_domicilio ORDER BY us.fecha_alta DESC";
                        $result = $conn->query($sql);
                        if ($result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $estado = $row['activo'] ? 'Activo' : 'Inactivo';
                                $statusClass = $row['activo'] ? 'paid' : 'pending';
                                echo "<div data-id='{$row['id_usuario']}' class='card-wrapper beneficiary-row'>
                                        <div class='beneficiary-card'>
                                            <div class='card-body'>
                                                <div class='card-item'>
                                                    <div class='card-label'># Beneficiario</div>
                                                    <div class='card-value'>{$row['id_usuario']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Nombre</div>
                                                    <div class='card-value beneficiary-name' style='color: #000000;'>{$row['nombre']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Calle</div>
                                                    <div class='card-value'>{$row['calle']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Contrato</div>
                                                    <div class='card-value'>{$row['no_contrato']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Medidor</div>
                                                    <div class='card-value beneficiary-medidor' style='color: #000000;'>{$row['no_medidor']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Fecha Alta</div>
                                                    <div class='card-value'>{$row['fecha_alta']}</div>
                                                </div>
                                                <div class='card-item'>
                                                    <div class='card-label'>Estado</div>
                                                    <div class='card-value'><span class='status {$statusClass}'>{$estado}</span></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class='card-actions-external'>
                                            <button class='btn btn-outline edit-btn' data-id='{$row['id_usuario']}' style='margin-right: 2px;'><i class='fas fa-edit'></i></button>
                                            <button class='btn btn-outline delete-btn' data-id='{$row['id_usuario']}'><i class='fas fa-trash'></i></button>
                                        </div>
                                      </div>";
                            }
                        } else {
                            echo "<div id='noResultsRow' class='no-results'>No hay beneficiarios registrados</div>";
                        }
                        $conn->close();
                        ?>
                    </div>
                </div>
                <div id="noSearchResults" style="display: none; text-align: center; padding: 2rem; color: var(--text-color);">
                    No se encontraron beneficiarios que coincidan con la búsqueda.
                </div>
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
            <div class="notification-message">La operación se completó correctamente</div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="custom-modal-backdrop" id="editModalBackdrop">
        <div class="custom-modal">
            <div class="edit-modal-header" style="text-align: left; margin-bottom: 20px;">
                <h3 class="modal-title">Editar Beneficiario</h3>
                <button class="edit-close-btn" id="editCloseBtn" style="position: absolute; right: 20px; top: 20px; background: none; border: none; font-size: 1.5rem; cursor: pointer;"><i class="fas fa-times"></i></button>
            </div>
            <form id="editForm" class="edit-modal-form">
                <!-- Información del Usuario (Solo lectura) -->
                <div class="edit-info-section" style="margin-bottom: 15px; text-align: left;">
                    <div class="edit-info-item full">
                        <span class="edit-info-label" style="font-weight: bold; color: var(--primary-color);">Nombre Anterior:</span>
                        <span class="edit-info-value" id="previousName" style="display: block; margin-top: 5px; color: #555;"></span>
                    </div>
                </div>

                <!-- Campos Editables -->
                <div class="edit-editable-section" style="text-align: left;">
                    <input type="hidden" id="editId" name="id">
                    <div class="edit-field-group full" style="margin-bottom: 15px;">
                        <label for="editBeneficiaryName" style="display: block; margin-bottom: 5px;">Nombre Completo</label>
                        <input type="text" class="form-control" id="editBeneficiaryName" name="beneficiaryName" placeholder="Nombre del beneficiario" style="width: 100%;">
                    </div>
                    <div class="edit-field-group" style="margin-bottom: 15px;">
                        <label for="editContractNumber" style="display: block; margin-bottom: 5px;">Número de Contrato</label>
                        <input type="number" class="form-control" id="editContractNumber" name="contractNumber" placeholder="Número de contrato" style="width: 100%;">
                    </div>
                    <div class="edit-field-group" style="margin-bottom: 15px;">
                        <label for="editMeterNumber" style="display: block; margin-bottom: 5px;">Número de Medidor</label>
                        <input type="number" class="form-control" id="editMeterNumber" name="meterNumber" placeholder="Número de medidor" style="width: 100%;">
                    </div>
                    <div class="edit-field-group full" style="margin-bottom: 15px;">
                        <label for="editStreetAndNumber" style="display: block; margin-bottom: 5px;">Calle</label>
                        <select class="form-control" id="editStreetAndNumber" name="streetAndNumber" style="width: 100%;">
                            <option value="">Selecciona una calle</option>
                        </select>
                    </div>
                    <div class="edit-field-group full" style="margin-bottom: 20px;">
                        <label for="editStatus" style="display: block; margin-bottom: 5px;">Estado</label>
                        <select class="form-control" id="editStatus" name="status" style="width: 100%;">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="modal-actions">
                    <button type="button" class="modal-btn btn-cancel" id="editCancelBtn">
                        <i class="fas fa-times"></i> Cancelar
                    </button>
                    <button type="submit" class="modal-btn btn-confirm" id="saveEditButton">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js"></script>
    <script src="../recursos/scripts/validacion_beneficiarios.js?v=2.233433"></script>
</body>
</html>