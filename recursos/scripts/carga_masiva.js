// Carga Masiva de Beneficiarios - JavaScript
document.addEventListener('DOMContentLoaded', function () {

    const calleSelect = document.getElementById('calleSelect');
    const tablaBeneficiariosBody = document.getElementById('tablaBeneficiariosBody');
    const agregarFilaBtn = document.getElementById('agregarFilaBtn');
    const limpiarTodoBtn = document.getElementById('limpiarTodoBtn');
    const guardarTodoBtn = document.getElementById('guardarTodoBtn');
    const totalBeneficiarios = document.getElementById('totalBeneficiarios');

    let filaCounter = 0;
    let timeouts = {}; // Para almacenar timeouts de validación

    // Cargar calles desde la base de datos
    cargarCalles();

    // Agregar 3 filas iniciales
    for (let i = 0; i < 3; i++) {
        agregarFila();
    }

    // Event Listeners
    agregarFilaBtn.addEventListener('click', agregarFila);
    limpiarTodoBtn.addEventListener('click', limpiarTodo);
    guardarTodoBtn.addEventListener('click', guardarTodo);

    // Función para cargar calles
    function cargarCalles() {
        fetch('../controladores/beneficiarios.php?action=get_calles')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.calles) {
                    data.calles.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle;
                        option.textContent = calle;
                        calleSelect.appendChild(option);
                    });
                }
            })
            .catch(err => console.error('Error al cargar calles:', err));
    }

    // Función para agregar una fila
    function agregarFila() {
        filaCounter++;
        const fila = document.createElement('tr');
        fila.dataset.fila = filaCounter;

        fila.innerHTML = `
            <td class="numero-fila">${filaCounter}</td>
            <td>
                <input type="number" 
                       class="input-contrato" 
                       placeholder="Ej: 123" 
                       maxlength="4"
                       min="1"
                       max="9999"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       data-fila="${filaCounter}">
                <div class="mensaje-validacion"></div>
            </td>
            <td>
                <input type="text" 
                       class="input-nombre" 
                       placeholder="Nombre completo del beneficiario" 
                       data-fila="${filaCounter}"
                       spellcheck="true"
                       lang="es">
                <div class="mensaje-validacion"></div>
            </td>
            <td>
                <input type="number" 
                       class="input-medidor" 
                       placeholder="Ej: 12345678" 
                       maxlength="8"
                       min="10000000"
                       max="99999999"
                       inputmode="numeric"
                       pattern="[0-9]*"
                       data-fila="${filaCounter}">
                <div class="mensaje-validacion"></div>
            </td>
            <td>
                <button class="btn-eliminar-fila" onclick="eliminarFila(${filaCounter})">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;

        tablaBeneficiariosBody.appendChild(fila);
        actualizarContador();

        // Agregar validaciones a los inputs
        const inputContrato = fila.querySelector('.input-contrato');
        const inputNombre = fila.querySelector('.input-nombre');
        const inputMedidor = fila.querySelector('.input-medidor');

        // Validación de contrato
        inputContrato.addEventListener('input', function () {
            validarContrato(this);
        });

        // Validación de nombre (spellcheck.js maneja la auto-capitalización)
        inputNombre.addEventListener('input', function () {
            validarNombre(this);
        });

        // Validación de medidor
        inputMedidor.addEventListener('input', function () {
            validarMedidor(this);
        });

        // Enfocar el primer campo de la nueva fila
        inputContrato.focus();
    }

    // Función para validar contrato
    function validarContrato(input) {
        const fila = input.dataset.fila;
        const valor = input.value.trim();

        // Limpiar timeout anterior
        if (timeouts[`contrato-${fila}`]) {
            clearTimeout(timeouts[`contrato-${fila}`]);
        }

        // Limpiar mensajes
        limpiarMensaje(input);

        if (valor === '') {
            input.classList.remove('error', 'warning', 'success');
            return;
        }

        // Validar que solo sean números
        if (!/^\d+$/.test(valor)) {
            mostrarError(input, 'Solo números');
            return;
        }

        // Validar longitud (1-4 dígitos)
        if (valor.length > 4) {
            mostrarError(input, 'Máximo 4 dígitos');
            return;
        }

        // Verificar duplicados después de 500ms
        timeouts[`contrato-${fila}`] = setTimeout(() => {
            fetch(`../controladores/beneficiarios.php?action=check_duplicate&tipo=contrato&valor=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.exists) {
                        mostrarAdvertencia(input, `Ya existe para: ${data.beneficiario}`);
                    } else {
                        mostrarExito(input);
                    }
                })
                .catch(() => {
                    mostrarExito(input);
                });
        }, 500);
    }

    // Función para validar nombre
    function validarNombre(input) {
        const valor = input.value.trim();

        limpiarMensaje(input);

        if (valor === '') {
            input.classList.remove('error', 'warning', 'success');
            return;
        }

        if (valor.length < 3) {
            mostrarError(input, 'Mínimo 3 caracteres');
            return;
        }

        mostrarExito(input);
    }

    // Función para validar medidor
    function validarMedidor(input) {
        const fila = input.dataset.fila;
        const valor = input.value.trim();

        // Limpiar timeout anterior
        if (timeouts[`medidor-${fila}`]) {
            clearTimeout(timeouts[`medidor-${fila}`]);
        }

        // Limpiar mensajes
        limpiarMensaje(input);

        if (valor === '') {
            input.classList.remove('error', 'warning', 'success');
            return;
        }

        // Validar que solo sean números
        if (!/^\d+$/.test(valor)) {
            mostrarError(input, 'Solo números');
            return;
        }

        // Validar longitud (exactamente 8 dígitos)
        if (valor.length < 8) {
            mostrarError(input, 'Debe tener 8 dígitos');
            return;
        }

        if (valor.length > 8) {
            mostrarError(input, 'Máximo 8 dígitos');
            return;
        }

        // Verificar duplicados después de 500ms
        timeouts[`medidor-${fila}`] = setTimeout(() => {
            fetch(`../controladores/beneficiarios.php?action=check_duplicate&tipo=medidor&valor=${encodeURIComponent(valor)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.exists) {
                        mostrarAdvertencia(input, `Ya existe para: ${data.beneficiario}`);
                    } else {
                        mostrarExito(input);
                    }
                })
                .catch(() => {
                    mostrarExito(input);
                });
        }, 500);
    }

    // Funciones de mensajes
    function mostrarError(input, mensaje) {
        input.classList.remove('warning', 'success');
        input.classList.add('error');
        const mensajeDiv = input.nextElementSibling;
        if (mensajeDiv && mensajeDiv.classList.contains('mensaje-validacion')) {
            mensajeDiv.textContent = '❌ ' + mensaje;
            mensajeDiv.style.color = '#e74c3c';
            mensajeDiv.style.fontSize = '0.85rem';
            mensajeDiv.style.marginTop = '4px';
        }
    }

    function mostrarAdvertencia(input, mensaje) {
        input.classList.remove('error', 'success');
        input.classList.add('warning');
        const mensajeDiv = input.nextElementSibling;
        if (mensajeDiv && mensajeDiv.classList.contains('mensaje-validacion')) {
            mensajeDiv.textContent = '⚠️ ' + mensaje;
            mensajeDiv.style.color = '#f39c12';
            mensajeDiv.style.fontSize = '0.85rem';
            mensajeDiv.style.marginTop = '4px';
        }
    }

    function mostrarExito(input) {
        input.classList.remove('error', 'warning');
        input.classList.add('success');
        limpiarMensaje(input);
    }

    function limpiarMensaje(input) {
        const mensajeDiv = input.nextElementSibling;
        if (mensajeDiv && mensajeDiv.classList.contains('mensaje-validacion')) {
            mensajeDiv.textContent = '';
        }
    }

    // Función para eliminar fila (global para onclick)
    window.eliminarFila = function (filaId) {
        const fila = document.querySelector(`tr[data-fila="${filaId}"]`);
        if (fila) {
            fila.remove();
            actualizarContador();
            renumerarFilas();
        }
    };

    // Función para renumerar filas
    function renumerarFilas() {
        const filas = tablaBeneficiariosBody.querySelectorAll('tr');
        filas.forEach((fila, index) => {
            const numeroCell = fila.querySelector('.numero-fila');
            if (numeroCell) {
                numeroCell.textContent = index + 1;
            }
        });
    }

    // Función para actualizar contador
    function actualizarContador() {
        const filas = tablaBeneficiariosBody.querySelectorAll('tr');
        totalBeneficiarios.textContent = filas.length;
    }

    // Función para limpiar todo
    function limpiarTodo() {
        if (confirm('¿Estás seguro de que deseas limpiar todas las filas?')) {
            tablaBeneficiariosBody.innerHTML = '';
            filaCounter = 0;
            actualizarContador();

            // Agregar 3 filas nuevas
            for (let i = 0; i < 3; i++) {
                agregarFila();
            }
        }
    }

    // Función para validar duplicados internos entre beneficiarios
    function validarDuplicadosInternos(beneficiarios) {
        const duplicados = [];
        const contratoMap = new Map(); // {contrato: [indices]}
        const medidorMap = new Map();  // {medidor: [indices]}

        // Mapear contratos y medidores
        beneficiarios.forEach((ben, index) => {
            const filaNum = index + 1;

            // Verificar contratos
            if (ben.contrato && ben.contrato.trim() !== '') {
                const contrato = ben.contrato.trim();
                if (!contratoMap.has(contrato)) {
                    contratoMap.set(contrato, []);
                }
                contratoMap.get(contrato).push({ index: filaNum, nombre: ben.nombre });
            }

            // Verificar medidores
            if (ben.medidor && ben.medidor.trim() !== '') {
                const medidor = ben.medidor.trim();
                if (!medidorMap.has(medidor)) {
                    medidorMap.set(medidor, []);
                }
                medidorMap.get(medidor).push({ index: filaNum, nombre: ben.nombre });
            }
        });

        // Detectar duplicados de contrato
        contratoMap.forEach((beneficiarios, contrato) => {
            if (beneficiarios.length > 1) {
                const filas = beneficiarios.map(b => `Fila ${b.index} (${b.nombre})`).join(' y ');
                duplicados.push(`🔴 Contrato "${contrato}" repetido en: ${filas}`);
            }
        });

        // Detectar duplicados de medidor
        medidorMap.forEach((beneficiarios, medidor) => {
            if (beneficiarios.length > 1) {
                const filas = beneficiarios.map(b => `Fila ${b.index} (${b.nombre})`).join(' y ');
                duplicados.push(`🔴 Medidor "${medidor}" repetido en: ${filas}`);
            }
        });

        return duplicados;
    }

    // Función para guardar todo
    function guardarTodo() {
        const calle = calleSelect.value;

        if (!calle) {
            alert('Por favor selecciona una calle');
            calleSelect.focus();
            return;
        }

        const filas = tablaBeneficiariosBody.querySelectorAll('tr');

        if (filas.length === 0) {
            alert('No hay beneficiarios para guardar');
            return;
        }

        const beneficiarios = [];
        let hayErrores = false;

        filas.forEach((fila, index) => {
            const contrato = fila.querySelector('.input-contrato').value.trim();
            const nombre = fila.querySelector('.input-nombre').value.trim();
            const medidor = fila.querySelector('.input-medidor').value.trim();

            // Validar que al menos tenga nombre
            if (!nombre) {
                alert(`Fila ${index + 1}: El nombre es obligatorio`);
                hayErrores = true;
                return;
            }

            // Verificar si hay inputs con error
            const inputsConError = fila.querySelectorAll('input.error');
            if (inputsConError.length > 0) {
                alert(`Fila ${index + 1}: Hay campos con errores. Por favor corrígelos antes de guardar.`);
                hayErrores = true;
                return;
            }

            beneficiarios.push({
                contrato: contrato,
                nombre: nombre,
                medidor: medidor
            });
        });

        if (hayErrores) {
            return;
        }

        if (beneficiarios.length === 0) {
            alert('No hay beneficiarios válidos para guardar');
            return;
        }

        // Validar duplicados internos (entre los beneficiarios a guardar)
        const duplicadosInternos = validarDuplicadosInternos(beneficiarios);
        if (duplicadosInternos.length > 0) {
            let mensaje = '⚠️ Se encontraron duplicados entre los beneficiarios:\n\n';
            duplicadosInternos.forEach(dup => {
                mensaje += `${dup}\n`;
            });
            mensaje += '\n¿Deseas continuar de todas formas?';

            if (!confirm(mensaje)) {
                return;
            }
        }

        // Confirmar guardado
        if (!confirm(`¿Guardar ${beneficiarios.length} beneficiario(s) en la calle "${calle}"?`)) {
            return;
        }

        // Mostrar loading
        guardarTodoBtn.disabled = true;
        guardarTodoBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

        // Enviar al servidor
        const formData = new FormData();
        formData.append('action', 'guardar_masivo');
        formData.append('calle', calle);
        formData.append('beneficiarios', JSON.stringify(beneficiarios));

        fetch('../controladores/carga_masiva_controller.php', {
            method: 'POST',
            body: formData
        })
            .then(r => r.json())
            .then(data => {
                guardarTodoBtn.disabled = false;
                guardarTodoBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Todos';

                mostrarResultados(data);

                if (data.success && data.guardados > 0) {
                    // Limpiar tabla
                    tablaBeneficiariosBody.innerHTML = '';
                    filaCounter = 0;

                    // Agregar 3 filas nuevas
                    for (let i = 0; i < 3; i++) {
                        agregarFila();
                    }

                    actualizarContador();
                }
            })
            .catch(err => {
                guardarTodoBtn.disabled = false;
                guardarTodoBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Todos';
                alert('Error al guardar: ' + err.message);
            });
    }

    // Función para mostrar resultados
    function mostrarResultados(data) {
        const modal = document.getElementById('resultadosModal');
        const titulo = document.getElementById('resultadosTitulo');
        const contenido = document.getElementById('resultadosContenido');

        let html = `
            <div class="resultado-resumen">
                <div class="stat">
                    <span class="stat-label">Total procesados:</span>
                    <span class="stat-value">${data.total}</span>
                </div>
                <div class="stat">
                    <span class="stat-label">Guardados exitosamente:</span>
                    <span class="stat-value success">${data.guardados}</span>
                </div>
        `;

        if (data.errores && data.errores.length > 0) {
            html += `
                <div class="stat">
                    <span class="stat-label">Errores:</span>
                    <span class="stat-value error">${data.errores.length}</span>
                </div>
            `;
        }

        if (data.advertencias && data.advertencias.length > 0) {
            html += `
                <div class="stat">
                    <span class="stat-label">Advertencias:</span>
                    <span class="stat-value warning">${data.advertencias.length}</span>
                </div>
            `;
        }

        html += `</div>`;

        // Mostrar errores
        if (data.errores && data.errores.length > 0) {
            html += `
                <div class="errores-lista">
                    <h4><i class="fas fa-exclamation-circle"></i> Errores:</h4>
                    <ul>
            `;
            data.errores.forEach(error => {
                html += `<li>${error}</li>`;
            });
            html += `</ul></div>`;
        }

        // Mostrar advertencias
        if (data.advertencias && data.advertencias.length > 0) {
            html += `
                <div class="advertencias-lista">
                    <h4><i class="fas fa-exclamation-triangle"></i> Advertencias:</h4>
                    <ul>
            `;
            data.advertencias.forEach(adv => {
                html += `<li>${adv}</li>`;
            });
            html += `</ul></div>`;
        }

        contenido.innerHTML = html;

        if (data.success) {
            titulo.innerHTML = '<i class="fas fa-check-circle"></i> Guardado Exitoso';
        } else {
            titulo.innerHTML = '<i class="fas fa-times-circle"></i> Error al Guardar';
        }

        modal.style.display = 'flex';
    }

    // Función para cerrar modal (global)
    window.cerrarModalResultados = function () {
        document.getElementById('resultadosModal').style.display = 'none';
    };
});
