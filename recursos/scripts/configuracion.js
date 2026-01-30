
document.addEventListener('DOMContentLoaded', function () {
    const formInfo = document.getElementById('formInfo');
    const formPassword = document.getElementById('formPassword');
    const loadingOverlay = document.getElementById('loadingOverlay');
    const btnShowPasswordForm = document.getElementById('btnShowPasswordForm');
    const passwordFormContainer = document.getElementById('passwordFormContainer');

    // --- Funciones de Utilidad ---

    // Toggle Loading Overlay
    const toggleLoading = (show, message = 'Procesando...') => {
        const textElement = loadingOverlay.querySelector('.loading-text');
        if (textElement) textElement.textContent = message;

        if (show) {
            loadingOverlay.classList.add('active');
        } else {
            loadingOverlay.classList.remove('active');
        }
    };

    // Mostrar Alertas
    const mostrarAlerta = (titulo, mensaje, tipo) => {
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) existingAlert.remove();

        let iconClass = 'fa-info-circle';
        if (tipo === 'success') iconClass = 'fa-check-circle';
        if (tipo === 'error') iconClass = 'fa-times-circle';
        if (tipo === 'warning') iconClass = 'fa-exclamation-triangle';

        const alertHTML = `
            <div class="custom-alert show">
                <div class="custom-alert-content ${tipo}">
                    <button class="custom-alert-close" onclick="this.closest('.custom-alert').remove()">
                        <i class="fas fa-times"></i>
                    </button>
                    <div class="custom-alert-icon">
                        <i class="fas ${iconClass}"></i>
                    </div>
                    <div class="custom-alert-body">
                        <h3>${titulo}</h3>
                        <p>${mensaje}</p>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHTML);

        if (tipo === 'success') {
            setTimeout(() => {
                const alert = document.querySelector('.custom-alert');
                if (alert) {
                    alert.classList.remove('show');
                    setTimeout(() => alert.remove(), 300);
                }
            }, 3000);
        }
    };

    // Validación Visual (Borde Rojo)
    const validarCampo = (input) => {
        if (!input.value.trim()) {
            input.classList.add('error');
            return false;
        } else {
            input.classList.remove('error');
            return true;
        }
    };

    // Remover error al escribir
    document.querySelectorAll('.config-input').forEach(input => {
        input.addEventListener('input', () => {
            if (input.value.trim()) input.classList.remove('error');
        });
    });

    // --- Carga Inicial de Datos ---
    const cargarDatos = async () => {
        toggleLoading(true, 'Cargando información...');

        try {
            const response = await fetch('../controladores/configuracion.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ accion: 'obtener_datos' })
            });

            const data = await response.json();

            if (data.exito) {
                document.getElementById('nombre').value = data.datos.nombre || '';
                document.getElementById('usuario').value = data.datos.usuario || '';

                const rolInput = document.getElementById('rol');
                if (rolInput) rolInput.value = data.datos.rol || '';

                // Actualizar avatar
                const avatar = document.getElementById('userAvatar');
                if (avatar && data.datos.nombre) {
                    const initials = data.datos.nombre.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                    avatar.textContent = initials;
                }
            } else {
                mostrarAlerta('Error', data.mensaje || 'No se pudieron cargar los datos', 'error');
            }
        } catch (error) {
            console.error(error);
            mostrarAlerta('Error', 'Error de conexión', 'error');
        } finally {
            toggleLoading(false);
        }
    };

    cargarDatos();

    // --- Manejadores de Eventos ---

    // Toggle Formulario de Contraseña
    if (btnShowPasswordForm) {
        btnShowPasswordForm.addEventListener('click', () => {
            passwordFormContainer.classList.toggle('visible');
            const isVisible = passwordFormContainer.classList.contains('visible');
            btnShowPasswordForm.innerHTML = isVisible ?
                '<i class="fas fa-chevron-up"></i> Cancelar Cambio de Contraseña' :
                '<i class="fas fa-key"></i> Cambiar Contraseña';
        });
    }

    // Submit Información Personal
    if (formInfo) {
        formInfo.addEventListener('submit', async (e) => {
            e.preventDefault();

            const nombre = document.getElementById('nombre');
            const usuario = document.getElementById('usuario');

            // Validar
            let isValid = true;
            if (!validarCampo(nombre)) isValid = false;
            if (!validarCampo(usuario)) isValid = false;

            if (!isValid) {
                mostrarAlerta('Campos Requeridos', 'Por favor complete todos los campos marcados en rojo.', 'warning');
                return;
            }

            toggleLoading(true, 'Guardando información...');

            try {
                const response = await fetch('../controladores/configuracion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        accion: 'actualizar_info',
                        nombre: nombre.value,
                        usuario: usuario.value
                    })
                });

                const result = await response.json();

                if (result.exito) {
                    mostrarAlerta('¡Éxito!', result.mensaje, 'success');
                    // Actualizar nombre en header
                    const headerName = document.querySelector('.full-name');
                    if (headerName) headerName.textContent = nombre.value;
                    cargarDatos(); // Recargar para asegurar
                } else {
                    mostrarAlerta('Error', result.mensaje, 'error');
                }
            } catch (error) {
                console.error(error);
                mostrarAlerta('Error', 'Error al guardar cambios', 'error');
            } finally {
                toggleLoading(false);
            }
        });
    }

    // Submit Contraseña
    if (formPassword) {
        formPassword.addEventListener('submit', async (e) => {
            e.preventDefault();

            const actual = document.getElementById('contrasena_actual');
            const nueva = document.getElementById('nueva_contrasena');
            const confirmar = document.getElementById('confirmar_contrasena');

            // Validar campos vacíos
            let isValid = true;
            if (!validarCampo(actual)) isValid = false;
            if (!validarCampo(nueva)) isValid = false;
            if (!validarCampo(confirmar)) isValid = false;

            if (!isValid) {
                mostrarAlerta('Campos Requeridos', 'Por favor complete todos los campos de contraseña.', 'warning');
                return;
            }

            // Validar coincidencia
            if (nueva.value !== confirmar.value) {
                mostrarAlerta('Error', 'Las nuevas contraseñas no coinciden.', 'error');
                nueva.classList.add('error');
                confirmar.classList.add('error');
                return;
            }

            toggleLoading(true, 'Actualizando contraseña...');

            try {
                const response = await fetch('../controladores/configuracion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        accion: 'actualizar_password',
                        contrasena_actual: actual.value,
                        nueva_contrasena: nueva.value,
                        confirmar_contrasena: confirmar.value
                    })
                });

                const result = await response.json();

                if (result.exito) {
                    mostrarAlerta('¡Éxito!', result.mensaje, 'success');
                    formPassword.reset();
                    passwordFormContainer.classList.remove('visible');
                    btnShowPasswordForm.innerHTML = '<i class="fas fa-key"></i> Cambiar Contraseña';
                } else {
                    mostrarAlerta('Error', result.mensaje, 'error');
                    if (result.mensaje.includes('actual')) {
                        actual.classList.add('error');
                    }
                }
            } catch (error) {
                console.error(error);
                mostrarAlerta('Error', 'Error al actualizar contraseña', 'error');
            } finally {
                toggleLoading(false);
            }
        });
    }
});

// Función Global para Toggle Password
window.togglePasswordVisibility = function (inputId) {
    const input = document.getElementById(inputId);
    const icon = input.nextElementSibling; // El icono 'i' siguiente

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
};
