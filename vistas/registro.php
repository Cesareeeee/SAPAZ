<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Registro</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/auth.css?v=1.0">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <img src="../recursos/imagenes/SAPAZ.jpeg" alt="SAPAZ Logo">
                </div>
                <h1 class="auth-title">Crear Cuenta</h1>
                <p class="auth-subtitle">SISTEMA DE AGUA POTABLE Y ALCANTARILLADO DE ZECALACOAYAN</p>
            </div>

            <form id="registerForm" class="auth-form">
                <div class="input-group">
                    <input type="text" name="nombre_completo" class="auth-input" placeholder="Nombre Completo" required>
                    <i class="fas fa-id-card input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="text" name="usuario" class="auth-input" placeholder="Nombre de Usuario" required autocomplete="username">
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" id="password" name="password" class="auth-input" placeholder="Contraseña" required autocomplete="new-password">
                    <i class="fas fa-lock input-icon"></i>
                </div>

                <div class="input-group">
                    <input type="password" id="confirmPassword" class="auth-input" placeholder="Confirmar Contraseña" required autocomplete="new-password">
                    <i class="fas fa-check-circle input-icon"></i>
                </div>

                <div class="role-selector">
                    <div class="role-option">
                        <input type="radio" name="rol" id="rol_lecturista" value="lecturista" class="role-input" checked>
                        <label for="rol_lecturista" class="role-label">
                            <i class="fas fa-clipboard-list"></i>
                            <span>Lecturista</span>
                        </label>
                    </div>
                    <div class="role-option">
                        <input type="radio" name="rol" id="rol_admin" value="admin" class="role-input">
                        <label for="rol_admin" class="role-label">
                            <i class="fas fa-user-shield"></i>
                            <span>Admin</span>
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn-auth">
                    Registrarse <i class="fas fa-user-check"></i>
                </button>
            </form>

            <div class="auth-footer">
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    ¿Ya tienes una cuenta? 
                    <a href="login.php" class="auth-link">Inicia Sesión</a>
                </p>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/auth.js?v=1.0"></script>
</body>
</html>
