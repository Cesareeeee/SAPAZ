<?php 
require_once '../includes/validar_sesion.php';
// require_once '../includes/validar_admin.php'; // Permitir acceso a Lecturistas también
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Configuración de Perfil</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Fuentes Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/panel_admin.css?v=2.002">
    <link rel="stylesheet" href="../recursos/estilos/configuracion.css?v=2.005">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../includes/sidebar.php'; ?>

    <!-- Main Content -->
    <main class="main-content" id="mainContent">
        <div class="page-content">
            
            <div class="config-container">
                <div class="config-header">
                    <div class="config-avatar" id="userAvatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <h2>Mi Perfil</h2>
                    <p>Administre su información personal y seguridad</p>
                </div>

                <!-- Formulario de Información Personal -->
                <form id="formInfo">
                    <div class="form-section">
                        <div class="section-title">
                            <i class="fas fa-id-card"></i>
                            <span>Información Personal</span>
                        </div>
                        
                        <div class="input-group">
                            <label for="nombre">Nombre Completo</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-circle icon-prefix"></i>
                                <input type="text" id="nombre" name="nombre" class="config-input" placeholder="Ingrese su nombre completo">
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="usuario">Nombre de Usuario</label>
                            <div class="input-wrapper">
                                <i class="fas fa-at icon-prefix"></i>
                                <input type="text" id="usuario" name="usuario" class="config-input" placeholder="Ingrese su usuario">
                            </div>
                        </div>

                        <div class="input-group">
                            <label for="rol">Rol del Sistema</label>
                            <div class="input-wrapper">
                                <i class="fas fa-user-tag icon-prefix"></i>
                                <input type="text" id="rol" class="config-input" readonly style="background-color: #f1f5f9; cursor: not-allowed; color: #64748b;">
                            </div>
                        </div>

                        <div class="button-container">
                            <button type="submit" class="btn-guardar">
                                <i class="fas fa-save"></i>
                                Guardar Información
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Sección de Seguridad -->
                <div class="form-section">
                    <div class="section-title">
                        <i class="fas fa-lock"></i>
                        <span>Seguridad y Contraseña</span>
                    </div>

                    <button type="button" class="btn-toggle-password-form" id="btnShowPasswordForm">
                        <i class="fas fa-key"></i>
                        Cambiar Contraseña
                    </button>

                    <form id="formPassword">
                        <div id="passwordFormContainer">
                            <div class="input-group" style="margin-top: 1.5rem;">
                                <label for="contrasena_actual">Contraseña Actual</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock icon-prefix"></i>
                                    <input type="password" id="contrasena_actual" name="contrasena_actual" class="config-input" placeholder="Ingrese su contraseña actual">
                                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('contrasena_actual')"></i>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="nueva_contrasena">Nueva Contraseña</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-lock-open icon-prefix"></i>
                                    <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="config-input" placeholder="Ingrese su nueva contraseña">
                                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('nueva_contrasena')"></i>
                                </div>
                            </div>

                            <div class="input-group">
                                <label for="confirmar_contrasena">Confirmar Nueva Contraseña</label>
                                <div class="input-wrapper">
                                    <i class="fas fa-check-double icon-prefix"></i>
                                    <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="config-input" placeholder="Repita la nueva contraseña">
                                    <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility('confirmar_contrasena')"></i>
                                </div>
                            </div>

                            <div class="button-container">
                                <button type="submit" class="btn-guardar">
                                    <i class="fas fa-shield-alt"></i>
                                    Actualizar Contraseña
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

            </div>

        </div>
    </main>

    <!-- Overlay de Carga -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loader"></div>
        <div class="loading-text">Procesando...</div>
    </div>

    <script src="../recursos/scripts/panel_admin.js?v=2.002"></script>
    <script src="../recursos/scripts/configuracion.js?v=2.005"></script>
</body>
</html>