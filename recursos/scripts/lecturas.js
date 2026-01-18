// lecturas.js

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const readingForm = document.getElementById('readingForm');
    const lecturaForm = document.getElementById('lecturaForm');
    const cancelBtn = document.getElementById('cancelBtn');
    const clearSearch = document.getElementById('clearSearch');

    // Custom Modal Elements
    const modalBackdrop = document.getElementById('customModalBackdrop');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalActions = document.getElementById('modalActions');

    let searchTimeout;

    // Función para mostrar modal personalizado
    function showModal(title, message, type = 'info', onConfirm = null, onCancel = null) {
        // Configurar Icono
        let iconHtml = '';
        let iconClass = '';

        switch (type) {
            case 'success':
                iconClass = 'success';
                iconHtml = '<i class="fas fa-check"></i>';
                break;
            case 'error':
                iconClass = 'error';
                iconHtml = '<i class="fas fa-times"></i>';
                break;
            case 'warning':
                iconClass = 'warning';
                iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
                break;
            default:
                iconClass = '';
                iconHtml = '<i class="fas fa-info"></i>';
        }

        modalIcon.className = 'modal-icon ' + iconClass;
        modalIcon.innerHTML = iconHtml;

        // Configurar Contenido
        modalTitle.textContent = title;
        modalMessage.innerHTML = message;

        // Configurar Botones
        modalActions.innerHTML = '';

        if (type === 'warning') { // Para confirmaciones
            const confirmBtn = document.createElement('button');
            confirmBtn.className = 'modal-btn btn-confirm';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Continuar';
            confirmBtn.onclick = () => {
                closeModal();
                if (onConfirm) onConfirm();
            };

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'modal-btn btn-cancel';
            cancelBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
            cancelBtn.onclick = () => {
                closeModal();
                if (onCancel) onCancel();
            };

            modalActions.appendChild(cancelBtn);
            modalActions.appendChild(confirmBtn);
        } else if (type === 'success') {
            const okBtn = document.createElement('button');
            okBtn.className = 'modal-btn btn-success-modal';
            okBtn.innerHTML = '<i class="fas fa-check"></i> Aceptar';
            okBtn.onclick = () => {
                closeModal();
                if (onConfirm) onConfirm();
            };
            modalActions.appendChild(okBtn);
        } else { // Error o Info
            const okBtn = document.createElement('button');
            okBtn.className = 'modal-btn ' + (type === 'error' ? 'btn-error-modal' : 'btn-confirm');
            okBtn.innerHTML = '<i class="fas fa-check"></i> Entendido';
            okBtn.onclick = () => {
                closeModal();
                if (onConfirm) onConfirm();
            };
            modalActions.appendChild(okBtn);
        }

        // Mostrar
        modalBackdrop.classList.add('show');
        modalBackdrop.style.display = 'flex';

        // Auto-cerrar después de 4 segundos para success y error
        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                closeModal();
            }, 10000);
        }
    }

    function closeModal() {
        modalBackdrop.classList.remove('show');
        setTimeout(() => {
            modalBackdrop.style.display = 'none';
        }, 300);
    }

    // Búsqueda con debounce
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            searchResults.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch(`../controladores/lecturas.php?action=search&query=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displaySearchResults(data.usuarios);
                    } else {
                        searchResults.innerHTML = '<p>Error en la búsqueda</p>';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    searchResults.innerHTML = '<p>Error de conexión</p>';
                });
        }, 300);
    });

    // Mostrar resultados de búsqueda
    function displaySearchResults(usuarios) {
        if (usuarios.length === 0) {
            searchResults.innerHTML = '<p>No se encontraron resultados</p>';
            return;
        }

        let html = '<ul class="results-list">';
        usuarios.forEach(usuario => {
            html += `<li class="result-item" data-id="${usuario.id_usuario}" data-nombre="${usuario.nombre}" data-medidor="${usuario.no_medidor}" data-calle="${usuario.calle}">
                        <div class="result-info">
                            <strong>${usuario.nombre}</strong><br>
                            <small>Medidor: ${usuario.no_medidor}</small><br>
                            <small>Calle: ${usuario.calle}</small>
                        </div>
                     </li>`;
        });
        html += '</ul>';
        searchResults.innerHTML = html;

        // Agregar eventos a los items
        document.querySelectorAll('.result-item').forEach(item => {
            item.addEventListener('click', function () {
                selectUser(this.dataset.id, this.dataset.nombre, this.dataset.medidor, this.dataset.calle);
            });
        });
    }

    // Seleccionar usuario
    function selectUser(id, nombre, medidor, calle) {
        document.getElementById('selectedUserId').value = id;
        document.getElementById('clienteNombre').textContent = nombre;
        document.getElementById('clienteCalle').textContent = `Calle: ${calle}`;
        document.getElementById('numeroMedidor').textContent = medidor;

        // Mostrar solo el usuario seleccionado
        let html = '<ul class="results-list">';
        html += `<li class="result-item selected" data-id="${id}" data-nombre="${nombre}" data-medidor="${medidor}" data-calle="${calle}">
                    <div class="result-info">
                        <strong>${nombre}</strong><br>
                        <small>Medidor: ${medidor}</small><br>
                        <small>Calle: ${calle}</small>
                    </div>
                 </li>`;
        html += '</ul>';
        searchResults.innerHTML = html;

        // Obtener última lectura
        fetch(`../controladores/lecturas.php?action=get_last_reading&id_usuario=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('lecturaAnterior').textContent = data.lectura_anterior;
                    document.getElementById('fechaAnterior').textContent = data.fecha_anterior || 'Primera lectura';
                } else {
                    document.getElementById('lecturaAnterior').textContent = '0';
                    document.getElementById('fechaAnterior').textContent = 'Primera lectura';
                }

                // Fecha actual
                const today = new Date();
                const fechaFormateada = today.toISOString().split('T')[0];
                document.getElementById('fechaLectura').value = fechaFormateada;

                // Calcular consumo inicial
                calcularConsumo();

                // Mostrar formulario
                readingForm.style.display = 'block';
                readingForm.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Error', 'Error al obtener datos', 'error');
            });
    }

    // Elementos
    const lecturaActualInput = document.getElementById('lecturaActual');
    const fechaLecturaInput = document.getElementById('fechaLectura');
    const consumoCalculado = document.getElementById('consumoCalculado');
    const toggleObservaciones = document.getElementById('toggleObservaciones');
    const observacionesContainer = document.getElementById('observacionesContainer');
    const lecturaActualError = document.getElementById('lecturaActualError');
    const fechaLecturaError = document.getElementById('fechaLecturaError');
    const submitBtn = document.getElementById('submitBtn');

    // Toggle observaciones
    toggleObservaciones.addEventListener('click', function () {
        if (observacionesContainer.style.display === 'none') {
            observacionesContainer.style.display = 'block';
            this.innerHTML = '<i class="fas fa-minus"></i> Ocultar Observaciones';
        } else {
            observacionesContainer.style.display = 'none';
            this.innerHTML = '<i class="fas fa-plus"></i> Agregar Observaciones (Opcional)';
        }
    });

    // Calcular consumo
    function calcularConsumo() {
        if (lecturaActualInput.value.trim() === '') {
            consumoCalculado.textContent = '0.00 m³';
            consumoCalculado.style.color = '#16a34a';
            lecturaActualError.textContent = '';
            lecturaActualInput.classList.remove('error');
            return;
        }

        const actual = parseFloat(lecturaActualInput.value) || 0;
        const anterior = parseFloat(document.getElementById('lecturaAnterior').textContent) || 0;
        const consumo = actual - anterior;

        if (consumo < 0) {
            consumoCalculado.textContent = consumo.toFixed(2) + ' m³ (Retroceso)';
            consumoCalculado.style.color = '#dc3545';
            lecturaActualError.textContent = 'La lectura actual no puede ser menor que la anterior.';
            lecturaActualInput.classList.add('error');
        } else {
            let text = consumo.toFixed(2) + ' m³';
            if (consumo > 30) {
                text += ' - ¡Consumo alto! Más de 30 m³';
                consumoCalculado.style.color = '#ff6b35'; // Naranja para alto consumo
            } else {
                consumoCalculado.style.color = '#16a34a';
            }
            consumoCalculado.textContent = text;
            lecturaActualError.textContent = '';
            lecturaActualInput.classList.remove('error');
        }
    }

    lecturaActualInput.addEventListener('input', function () {
        calcularConsumo();
        if (this.value.trim() !== '') {
            this.classList.remove('error');
        }
    });

    // Validación en blur
    lecturaActualInput.addEventListener('blur', function () {
        if (this.value.trim() === '') {
            this.classList.add('error');
            lecturaActualError.textContent = 'Se necesita llenar el campo.';
        } else {
            this.classList.remove('error');
            lecturaActualError.textContent = '';
        }
    });

    fechaLecturaInput.addEventListener('blur', function () {
        if (this.value.trim() === '') {
            this.classList.add('error');
            fechaLecturaError.textContent = 'Se necesita llenar el campo.';
        } else {
            this.classList.remove('error');
            fechaLecturaError.textContent = '';
        }
    });

    // Enviar formulario
    lecturaForm.addEventListener('submit', function (e) {
        e.preventDefault();

        // Validar campos requeridos
        let hasError = false;
        if (lecturaActualInput.value.trim() === '') {
            lecturaActualInput.classList.add('error');
            lecturaActualError.textContent = 'Se necesita llenar el campo.';
            hasError = true;
        }
        if (fechaLecturaInput.value.trim() === '') {
            fechaLecturaInput.classList.add('error');
            fechaLecturaError.textContent = 'Se necesita llenar el campo.';
            hasError = true;
        }

        if (hasError) {
            showModal('Atención', 'Por favor, complete todos los campos requeridos', 'error');
            return;
        }

        // Calcular consumo para verificar
        const actual = parseFloat(lecturaActualInput.value) || 0;
        const anterior = parseFloat(document.getElementById('lecturaAnterior').textContent) || 0;
        const consumo = actual - anterior;
        const consumoFormatted = consumo.toFixed(2);

        // Función para guardar
        const proceedToSave = () => {
            // Agregar observaciones automáticas
            let observacionesAuto = '';
            if (consumo < 0) {
                observacionesAuto = 'El contador del medidor retrocedió. ';
            }
            if (consumo > 30) {
                observacionesAuto += 'Consumo superior a 30 metros cúbicos. ';
            }
            const observacionesExistentes = document.getElementById('observaciones').value.trim();
            if (observacionesExistentes) {
                observacionesAuto += observacionesExistentes;
            }
            document.getElementById('observaciones').value = observacionesAuto;

            // Mostrar loading
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';

            const formData = new FormData(lecturaForm);

            fetch('../controladores/lecturas.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    // Ocultar loading
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Lectura';

                    if (data.success) {
                        let msg = 'Lectura guardada correctamente.<br><br>';
                        msg += 'Consumo Registrado: <span style="color: #22c55e; font-weight: 800; font-size: 1.2rem;">' + consumoFormatted + ' m³</span>';

                        showModal('¡Guardado con éxito!', msg, 'success');
                        // Limpiar formulario
                        lecturaForm.reset();
                        readingForm.style.display = 'none';
                        searchInput.value = '';
                        searchResults.innerHTML = '';
                        // Limpiar spans
                        document.getElementById('clienteNombre').textContent = '';
                        document.getElementById('clienteCalle').textContent = '';
                        document.getElementById('numeroMedidor').textContent = '';
                        document.getElementById('lecturaAnterior').textContent = '';
                        document.getElementById('fechaAnterior').textContent = '';
                        document.getElementById('fechaLectura').value = '';
                        document.getElementById('consumoCalculado').textContent = '0.00 m³';
                        document.getElementById('consumoCalculado').style.color = '#16a34a';
                        // Limpiar errores
                        lecturaActualError.textContent = '';
                        fechaLecturaError.textContent = '';
                        // Ocultar observaciones
                        observacionesContainer.style.display = 'none';
                        toggleObservaciones.innerHTML = '<i class="fas fa-plus"></i> Agregar Observaciones (Opcional)';
                    } else {
                        showModal('Error al guardar', data.message || 'Error al guardar', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-save"></i> Guardar Lectura';
                    showModal('Error de conexión', 'No se pudo conectar con el servidor', 'error');
                });
        };

        // Verificar condiciones especiales
        if (consumo < 0) {
            showModal(
                '¡Advertencia de Consumo!',
                'El contador del medidor ha retrocedido.<br>Consumo: <span style="color: #dc3545; font-weight: bold;">' + consumoFormatted + ' m³</span><br><br>¿Desea continuar guardando la lectura?',
                'warning',
                proceedToSave
            );
        } else if (consumo > 30) {
            showModal(
                '¡Alto Consumo Detectado!',
                'El consumo supera los 30 metros cúbicos.<br>Consumo: <span style="color: #ff6b35; font-weight: bold;">' + consumoFormatted + ' m³</span><br><br>¿Desea continuar guardando la lectura?',
                'warning',
                proceedToSave
            );
        } else {
            showModal(
                'Confirmar Lectura',
                'La lectura y el consumo calculado parecen correctos.<br>Consumo: <span style="color: #16a34a; font-weight: bold;">' + consumoFormatted + ' m³</span><br><br>¿Desea guardar la lectura?',
                'warning',
                proceedToSave
            );
        }
    });

    // Limpiar búsqueda
    clearSearch.addEventListener('click', function () {
        searchInput.value = '';
        searchResults.innerHTML = '';
        lecturaForm.reset();
        readingForm.style.display = 'none';
        // Limpiar spans
        document.getElementById('clienteNombre').textContent = '';
        document.getElementById('clienteCalle').textContent = '';
        document.getElementById('numeroMedidor').textContent = '';
        document.getElementById('lecturaAnterior').textContent = '';
        document.getElementById('fechaAnterior').textContent = '';
        document.getElementById('fechaLectura').value = '';
        document.getElementById('consumoCalculado').textContent = '0.00 m³';
        document.getElementById('consumoCalculado').style.color = '#16a34a';
        // Limpiar errores
        lecturaActualError.textContent = '';
        fechaLecturaError.textContent = '';
        // Ocultar observaciones
        observacionesContainer.style.display = 'none';
        toggleObservaciones.innerHTML = '<i class="fas fa-plus"></i> Agregar Observaciones (Opcional)';
        searchInput.focus();
    });

    // Cancelar
    cancelBtn.addEventListener('click', function () {
        lecturaForm.reset();
        readingForm.style.display = 'none';
        // Limpiar spans
        document.getElementById('clienteNombre').textContent = '';
        document.getElementById('clienteCalle').textContent = '';
        document.getElementById('numeroMedidor').textContent = '';
        document.getElementById('lecturaAnterior').textContent = '';
        document.getElementById('fechaAnterior').textContent = '';
        document.getElementById('fechaLectura').value = '';
        document.getElementById('consumoCalculado').textContent = '0.00 m³';
        document.getElementById('consumoCalculado').style.color = '#16a34a';
        // Limpiar errores
        lecturaActualError.textContent = '';
        fechaLecturaError.textContent = '';
        // Ocultar observaciones
        observacionesContainer.style.display = 'none';
        toggleObservaciones.innerHTML = '<i class="fas fa-plus"></i> Agregar Observaciones (Opcional)';
    });
});