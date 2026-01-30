document.addEventListener('DOMContentLoaded', () => {
    // Determine if we are on login or register page
    const loginForm = document.getElementById('loginForm');
    const registerForm = document.getElementById('registerForm');

    // Password Toggle Functionality
    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('passwordInput');

    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('fa-eye');
            togglePassword.classList.toggle('fa-eye-slash');
        });
    }

    // Create Loading Overlay Element if not exists
    if (!document.querySelector('.loading-overlay')) {
        const overlay = document.createElement('div');
        overlay.className = 'loading-overlay';
        overlay.innerHTML = `
            <div class="spinner-large"></div>
            <div class="loading-text">Cargando...</div>
        `;
        document.body.appendChild(overlay);
    }

    // Notification Handler
    const showNotification = (message, type = 'success') => {
        // Remove existing notifications first
        const existing = document.querySelector('.notification');
        if (existing) existing.remove();

        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" style="color: ${type === 'success' ? '#10b981' : '#ef4444'}; font-size: 3rem;"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => notification.classList.add('active'), 10);

        // Remove after 3s
        setTimeout(() => {
            notification.classList.remove('active');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    };

    const showLoading = (text) => {
        const overlay = document.querySelector('.loading-overlay');
        overlay.querySelector('.loading-text').textContent = text;
        overlay.classList.add('active');
    };

    const clearErrors = (form) => {
        const inputs = form.querySelectorAll('.auth-input');
        inputs.forEach(input => input.classList.remove('error'));
    };

    // Helper to validate empty fields client-side
    const validateEmpty = (form) => {
        let isValid = true;
        const inputs = form.querySelectorAll('input:required');
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('error');
                isValid = false;
            } else {
                input.classList.remove('error');
            }
        });
        return isValid;
    };

    if (loginForm) {
        // Clear error on input
        loginForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => input.classList.remove('error'));
        });

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!validateEmpty(loginForm)) {
                showNotification('Por favor complete todos los campos', 'error');
                return;
            }

            const btn = loginForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            btn.disabled = true;

            const formData = new FormData(loginForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'login';

            // Add remember me checkbox value
            const rememberMeCheckbox = document.getElementById('rememberMe');
            data.remember_me = rememberMeCheckbox && rememberMeCheckbox.checked ? 'true' : 'false';

            try {
                const response = await fetch('../controladores/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    showLoading('Iniciando Sesión...');
                    showNotification(result.message, 'success');
                    setTimeout(() => {
                        window.location.href = result.redirect;
                    }, 1500);
                } else {
                    showNotification(result.message, 'error');
                    // Highlight inputs on error
                    loginForm.querySelectorAll('.auth-input').forEach(i => i.classList.add('error'));
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                showNotification('Error de conexión', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }

    if (registerForm) {
        // Clear error on input
        registerForm.querySelectorAll('input').forEach(input => {
            input.addEventListener('input', () => input.classList.remove('error'));
        });

        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!validateEmpty(registerForm)) {
                showNotification('Por favor complete todos los campos', 'error');
                return;
            }

            const btn = registerForm.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;

            // Password match validation
            const p1 = document.getElementById('password');
            const p2 = document.getElementById('confirmPassword');
            if (p1.value !== p2.value) {
                showNotification('Las contraseñas no coinciden', 'error');
                p1.classList.add('error');
                p2.classList.add('error');
                return;
            }

            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
            btn.disabled = true;

            const formData = new FormData(registerForm);
            const data = Object.fromEntries(formData.entries());
            data.action = 'register';

            try {
                const response = await fetch('../controladores/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    showNotification(result.message, 'success');
                    // Show Full Screen Loading Overlay
                    showLoading('Registro Exitoso, Redirigiendo...');
                    setTimeout(() => {
                        window.location.href = 'login.php';
                    }, 2000);
                } else {
                    showNotification(result.message, 'error');
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            } catch (error) {
                console.error(error);
                showNotification('Error de conexión', 'error');
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        });
    }
});
