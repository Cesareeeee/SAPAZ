<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAPAZ - Iniciar Sesión</title>
    <link rel="icon" href="../recursos/imagenes/SAPAZ.jpeg" type="image/jpeg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../recursos/estilos/auth.css?v=2.0">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <img src="../recursos/imagenes/SAPAZ.jpeg" alt="SAPAZ Logo">
                </div>
                <h1 class="auth-title">Bienvenido a SAPAZ</h1>
                <p class="auth-subtitle">SISTEMA DE AGUA POTABLE Y ALCANTARILLADO DE ZECALACOAYAN</p>
                <p class="auth-subtitle" style="font-size: 0.85rem; margin-top: 5px; color: #7FD1C9;">INICIAR SESIÓN</p>
            </div>

            <form id="loginForm" class="auth-form">
                <div class="input-group">
                    <input type="text" name="usuario" class="auth-input" placeholder="Nombre de Usuario" required autocomplete="username">
                    <i class="fas fa-user input-icon"></i>
                </div>
                
                <div class="input-group">
                    <input type="password" name="password" class="auth-input" placeholder="Contraseña" required autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                </div>

                <div class="remember-me-container">
                    <label class="remember-me-label">
                        <input type="checkbox" name="remember_me" id="rememberMe" class="remember-me-checkbox">
                        <span class="remember-me-text">
                            <i class="fas fa-shield-alt"></i>
                            Mantener sesión iniciada
                        </span>
                    </label>
                </div>

                <button type="submit" class="btn-auth">
                    Ingresar <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            <div class="auth-footer">
                <p style="color: var(--text-secondary); font-size: 0.9rem;">
                    ¿No tienes una cuenta? 
                    <a href="registro.php" class="auth-link">Regístrate aquí</a>
                </p>
            </div>
        </div>
    </div>

    <script src="../recursos/scripts/auth.js?v=2.0"></script>
</body>
</html>
