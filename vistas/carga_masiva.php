<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Carga Masiva de Beneficiarios</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.0327">
    <link rel="stylesheet" href="../recursos/estilos/carga_masiva.css?v=2.0327">
</head>
<body>
    
    <div class="main-content" style="margin-left: 0; width: 100%;">
        
        <!-- Header Simple -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px; color: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
            <div style="max-width: 1400px; margin: 0 auto; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; font-size: 1.5rem;"><i class="fas fa-water"></i> SAPAZ</h2>
                    <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 0.9rem;">Sistema de Agua Potable</p>
                </div>
                <div>
                    <a href="inicio.php" style="background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s;">
                        <i class="fas fa-home"></i> Volver al Inicio
                    </a>
                </div>
            </div>
        </div>

        <div class="content-wrapper">
            <div class="page-header">
                <h1><i class="fas fa-users-cog"></i> Carga Masiva de Beneficiarios</h1>
                <p class="subtitle">Agrega múltiples beneficiarios de la misma calle rápidamente</p>
            </div>

            <div class="carga-masiva-container">
                <!-- Sección de Selección de Calle -->
                <div class="calle-selector-card">
                    <h3><i class="fas fa-road"></i> Seleccionar Calle</h3>
                    <div class="form-group">
                        <label for="calleSelect">Calle:</label>
                        <select id="calleSelect" class="form-control">
                            <option value="">-- Seleccione una calle --</option>
                        </select>
                    </div>
                    <div class="info-box">
                        <i class="fas fa-info-circle"></i>
                        <span>Todos los beneficiarios se agregarán a la calle seleccionada</span>
                    </div>
                </div>

                <!-- Tabla de Beneficiarios -->
                <div class="tabla-beneficiarios-card">
                    <div class="card-header">
                        <h3><i class="fas fa-table"></i> Beneficiarios a Agregar</h3>
                        <button id="agregarFilaBtn" class="btn-agregar-fila">
                            <i class="fas fa-plus"></i> Agregar Fila
                        </button>
                    </div>

                    <div class="tabla-wrapper">
                        <table id="tablaBeneficiarios">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th style="width: 150px;">Nº Contrato <span class="opcional">(1-4 dígitos)</span></th>
                                    <th>Nombre Completo <span class="requerido">*</span></th>
                                    <th style="width: 180px;">Nº Medidor <span class="opcional">(8 dígitos)</span></th>
                                    <th style="width: 80px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tablaBeneficiariosBody">
                                <!-- Las filas se agregarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>

                    <div class="tabla-footer">
                        <div class="contador">
                            <i class="fas fa-users"></i>
                            <span>Total de beneficiarios: <strong id="totalBeneficiarios">0</strong></span>
                        </div>
                        <div class="acciones">
                            <button id="limpiarTodoBtn" class="btn-secundario">
                                <i class="fas fa-eraser"></i> Limpiar Todo
                            </button>
                            <button id="guardarTodoBtn" class="btn-primario">
                                <i class="fas fa-save"></i> Guardar Todos
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Instrucciones -->
                <div class="instrucciones-card">
                    <h4><i class="fas fa-lightbulb"></i> Instrucciones de Uso</h4>
                    <ol>
                        <li><strong>Selecciona la calle</strong> donde se encuentran los beneficiarios</li>
                        <li><strong>Haz clic en "Agregar Fila"</strong> para añadir un nuevo beneficiario</li>
                        <li>Completa los datos en el orden: <strong>Contrato → Nombre → Medidor</strong></li>
                        <li>El <strong>nombre es obligatorio</strong>, contrato y medidor son opcionales</li>
                        <li>Presiona <strong>Tab</strong> para moverte rápidamente entre campos</li>
                        <li>Haz clic en <strong>"Guardar Todos"</strong> cuando termines</li>
                    </ol>
                    <div class="validaciones-info">
                        <h5><i class="fas fa-check-circle"></i> Validaciones Automáticas:</h5>
                        <ul>
                            <li>✓ Número de contrato: 1-4 dígitos (opcional)</li>
                            <li>✓ Número de medidor: exactamente 8 dígitos (opcional)</li>
                            <li>✓ Detección de duplicados en tiempo real</li>
                            <li>✓ Auto-capitalización de nombres</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Resultados -->
    <div id="resultadosModal" class="modal-overlay" style="display: none;">
        <div class="modal-content resultados-modal">
            <div class="modal-header">
                <h3 id="resultadosTitulo"><i class="fas fa-check-circle"></i> Resultados de la Carga</h3>
                <button class="close-modal" onclick="cerrarModalResultados()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body" id="resultadosContenido">
                <!-- Contenido dinámico -->
            </div>
            <div class="modal-footer">
                <button class="btn-primario" onclick="cerrarModalResultados()">Aceptar</button>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/panel_admin.js?v=2.0327"></script>
    <script src="../recursos/scripts/spellcheck.js?v=3.0327"></script>
    <script src="../recursos/scripts/carga_masiva.js?v=2.2327"></script>
</body>
</html>
