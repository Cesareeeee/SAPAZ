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

        // Auto-cerrar después de 7 segundos para success y error (tiempo visible real)
        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                closeModal();
            }, 7000);
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

                    // Verificar si ya tiene lectura este mes
                    if (data.fecha_anterior) {
                        const fechaAnterior = new Date(data.fecha_anterior);
                        const now = new Date();
                        if (fechaAnterior.getMonth() === now.getMonth() && fechaAnterior.getFullYear() === now.getFullYear()) {
                            // Ya tiene lectura este mes
                            showModal(
                                'Usuario con Lectura Reciente',
                                'Este usuario ya tiene un registro de lectura este mes. ¿Desea agregar otra lectura?',
                                'warning',
                                () => {
                                    // Continuar
                                    mostrarFormularioLectura();
                                },
                                () => {
                                    // Cancelar
                                    // No hacer nada, el formulario no se muestra
                                }
                            );
                            return; // Salir para no mostrar formulario automáticamente
                        }
                    }
                } else {
                    document.getElementById('lecturaAnterior').textContent = '0';
                    document.getElementById('fechaAnterior').textContent = 'Primera lectura';
                }

                // Mostrar formulario
                mostrarFormularioLectura();
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Error', 'Error al obtener datos', 'error');
            });
    }

    function mostrarFormularioLectura() {
        // Fecha actual
        const today = new Date();
        const fechaFormateada = today.toISOString().split('T')[0];
        document.getElementById('fechaLectura').value = fechaFormateada;

        // Calcular consumo inicial
        calcularConsumo();

        // Mostrar formulario
        readingForm.style.display = 'block';
        readingForm.scrollIntoView({ behavior: 'smooth' });
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

    // Función para detectar si es dispositivo móvil
    function isMobileDevice() {
        return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ||
            window.innerWidth <= 768;
    }

    // Mostrar botón de cámara (disponible en todos los dispositivos con cámara)
    const cameraBtn = document.getElementById('cameraBtn');
    // cameraBtn.style.display = 'flex'; // Temporalmente oculto

    // Evento del botón de cámara
    // cameraBtn.addEventListener('click', function () {
    //     console.log('Botón de cámara clickeado');
    //     startCameraScan();
    // });

    // Nueva funcionalidad: Lista de beneficiarios sin lectura

    const toggleBeneficiariosSection = document.getElementById('toggleBeneficiariosSection');
    const beneficiariosSection = document.getElementById('beneficiariosSection');
    const beneficiariosContainer = document.getElementById('beneficiariosContainer');
    const filtroTipoBenef = document.getElementById('filtroTipoBenef');
    const filtroValorBenef = document.getElementById('filtroValorBenef');
    const btnLimpiarFiltrosBenef = document.getElementById('btnLimpiarFiltrosBenef');
    const btnPrevBenef = document.getElementById('btnPrevBenef');
    const btnNextBenef = document.getElementById('btnNextBenef');
    const pageInfoBenef = document.getElementById('pageInfoBenef');

    let paginaActualBenef = 1;
    let totalPaginasBenef = 1;
    const limitePorPaginaBenef = 10;
    let filtrosBenef = { tipo: '', valor: '' };

    // Toggle sección de beneficiarios
    toggleBeneficiariosSection.addEventListener('click', function () {
        if (beneficiariosSection.style.display === 'none') {
            beneficiariosSection.style.display = 'block';
            this.innerHTML = '<i class="fas fa-times"></i> Ocultar Lista';
            cargarBeneficiarios();
            cargarFiltrosBenef();
        } else {
            beneficiariosSection.style.display = 'none';
            this.innerHTML = '<i class="fas fa-users"></i> Ver Beneficiarios Sin Lectura';
        }
    });

    // Cargar filtros
    function cargarFiltrosBenef() {
        // No cargar inicialmente, se carga al seleccionar tipo
    }

    // Evento cambio de tipo
    filtroTipoBenef.addEventListener('change', function () {
        const tipo = this.value;
        filtroValorBenef.disabled = tipo === '';
        filtroValorBenef.innerHTML = '<option value="">Seleccione...</option>';

        if (tipo === 'calle') {
            fetch('../controladores/lecturas.php?action=get_calles')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.calles.forEach(calle => {
                            const option = document.createElement('option');
                            option.value = calle;
                            option.textContent = calle;
                            filtroValorBenef.appendChild(option);
                        });
                    }
                });
        } else if (tipo === 'barrio') {
            fetch('../controladores/lecturas.php?action=get_barrios')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        data.barrios.forEach(barrio => {
                            const option = document.createElement('option');
                            option.value = barrio;
                            option.textContent = barrio;
                            filtroValorBenef.appendChild(option);
                        });
                    }
                });
        }

        // Aplicar filtro automáticamente
        filtrosBenef.tipo = tipo;
        filtrosBenef.valor = '';
        paginaActualBenef = 1;
        cargarBeneficiarios();
    });

    // Evento cambio de valor
    filtroValorBenef.addEventListener('change', function () {
        filtrosBenef.valor = this.value;
        paginaActualBenef = 1;
        cargarBeneficiarios();
    });

    // Limpiar filtros
    btnLimpiarFiltrosBenef.addEventListener('click', function () {
        filtroTipoBenef.value = '';
        filtroValorBenef.value = '';
        filtroValorBenef.disabled = true;
        filtroValorBenef.innerHTML = '<option value="">Seleccione tipo primero</option>';
        filtrosBenef.tipo = '';
        filtrosBenef.valor = '';
        paginaActualBenef = 1;
        cargarBeneficiarios();
    });

    // Navegación
    btnPrevBenef.addEventListener('click', function () {
        if (paginaActualBenef > 1) {
            paginaActualBenef--;
            cargarBeneficiarios();
        }
    });

    btnNextBenef.addEventListener('click', function () {
        if (paginaActualBenef < totalPaginasBenef) {
            paginaActualBenef++;
            cargarBeneficiarios();
        }
    });

    // Cargar beneficiarios
    function cargarBeneficiarios() {
        const params = new URLSearchParams({
            action: 'get_users_without_reading',
            pagina: paginaActualBenef,
            limite: limitePorPaginaBenef
        });

        if (filtrosBenef.tipo === 'calle') {
            params.append('calle', filtrosBenef.valor);
        } else if (filtrosBenef.tipo === 'barrio') {
            params.append('barrio', filtrosBenef.valor);
        }

        fetch(`../controladores/lecturas.php?${params}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarBeneficiarios(data.usuarios);
                    actualizarNavegacionBenef(data.total);
                } else {
                    beneficiariosContainer.innerHTML = '<p>No se pudieron cargar los beneficiarios</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                beneficiariosContainer.innerHTML = '<p>Error al cargar beneficiarios</p>';
            });
    }

    // Mostrar beneficiarios
    function mostrarBeneficiarios(usuarios) {
        if (usuarios.length === 0) {
            beneficiariosContainer.innerHTML = '<p>No hay beneficiarios sin lectura en este mes</p>';
            return;
        }

        let html = '';
        usuarios.forEach(usuario => {
            html += `
                <div class="beneficiario-card" data-id="${usuario.id_usuario}" data-nombre="${usuario.nombre}" data-medidor="${usuario.no_medidor}" data-calle="${usuario.calle}" data-barrio="${usuario.barrio}">
                    <div class="beneficiario-header">
                        <div class="beneficiario-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="beneficiario-info">
                            <h4>${usuario.nombre}</h4>
                            <p>Medidor: ${usuario.no_medidor}</p>
                        </div>
                    </div>
                    <div class="beneficiario-details">
                        <div class="beneficiario-detail">
                            <span class="beneficiario-label">Calle:</span>
                            <span class="beneficiario-value">${usuario.calle || 'N/A'}</span>
                        </div>
                        <div class="beneficiario-detail">
                            <span class="beneficiario-label">Barrio:</span>
                            <span class="beneficiario-value">${usuario.barrio || 'N/A'}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        beneficiariosContainer.innerHTML = html;

        // Agregar eventos a las cards
        document.querySelectorAll('.beneficiario-card').forEach(card => {
            card.addEventListener('click', function () {
                selectUser(this.dataset.id, this.dataset.nombre, this.dataset.medidor, this.dataset.calle + (this.dataset.barrio ? ', ' + this.dataset.barrio : ''));
                // Ocultar sección de beneficiarios y mostrar formulario
                beneficiariosSection.style.display = 'none';
                toggleBeneficiariosSection.innerHTML = '<i class="fas fa-users"></i> Ver Beneficiarios Sin Lectura';
            });
        });
    }

    // Actualizar navegación
    function actualizarNavegacionBenef(total) {
        totalPaginasBenef = Math.ceil(total / limitePorPaginaBenef);
        pageInfoBenef.textContent = `Página ${paginaActualBenef} de ${totalPaginasBenef}`;

        btnPrevBenef.classList.toggle('nav-btn-disabled', paginaActualBenef === 1);
        btnNextBenef.classList.toggle('nav-btn-disabled', paginaActualBenef === totalPaginasBenef);
    }

    // Función para iniciar escaneo con cámara
    function startCameraScan() {
        console.log('Iniciando escaneo con cámara');

        // Crear input file oculto para capturar imagen
        const fileInput = document.createElement('input');
        fileInput.type = 'file';
        fileInput.accept = 'image/*';
        fileInput.capture = 'environment'; // Para móviles, usar cámara trasera
        fileInput.style.display = 'none';

        fileInput.addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = new Image();
                    img.onload = function () {
                        // Crear canvas para procesar
                        const canvas = document.createElement('canvas');
                        const ctx = canvas.getContext('2d');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);

                        // Mostrar modal de procesamiento
                        showProcessingModal(canvas);
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Agregar al DOM y trigger click
        document.body.appendChild(fileInput);
        fileInput.click();

        // Limpiar después de un tiempo
        setTimeout(() => {
            if (fileInput.parentNode) {
                fileInput.parentNode.removeChild(fileInput);
            }
        }, 1000);
    }

    // Función para mostrar modal de procesamiento
    function showProcessingModal(canvas) {
        const processingModal = document.createElement('div');
        processingModal.className = 'camera-modal';
        processingModal.innerHTML = `
            <div class="camera-modal-content">
                <div class="camera-modal-header">
                    <h3>Procesando imagen...</h3>
                    <button class="camera-close-btn" id="processingCloseBtn"><i class="fas fa-times"></i></button>
                </div>
                <div class="camera-modal-body">
                    <div class="processing-preview">
                        <img id="previewImg" src="${canvas.toDataURL()}" style="max-width: 100%; max-height: 300px; border-radius: 10px;">
                    </div>
                    <div class="camera-status" id="processingStatus">Analizando la imagen...</div>
                </div>
            </div>
        `;

        document.body.appendChild(processingModal);

        const closeBtn = processingModal.querySelector('#processingCloseBtn');
        const statusDiv = processingModal.querySelector('#processingStatus');

        closeBtn.addEventListener('click', function () {
            if (processingModal.parentNode) {
                processingModal.parentNode.removeChild(processingModal);
            }
        });

        // Procesar OCR
        processOCR(canvas, statusDiv, processingModal);
    }

    // Función para procesar OCR
    function processOCR(canvas, statusDiv, modal) {
        statusDiv.textContent = 'Analizando la imagen con OCR...';

        Tesseract.recognize(
            canvas,
            'eng',
            {
                logger: m => console.log(m)
            }
        ).then(({ data: { text } }) => {
            console.log('Texto reconocido:', text);
            statusDiv.textContent = 'Procesamiento completado.';

            // Extraer números del texto (asumiendo que el número del medidor es una secuencia de dígitos)
            const numbers = text.match(/\d+/g);
            if (numbers && numbers.length > 0) {
                // Tomar el primer número encontrado (o el más largo si hay varios)
                const medidorNumber = numbers.reduce((a, b) => a.length > b.length ? a : b);
                searchInput.value = medidorNumber;
                searchInput.focus();
                // Trigger input event para búsqueda automática
                searchInput.dispatchEvent(new Event('input'));
                showModal('¡Número detectado!', `Se detectó el número: ${medidorNumber}`, 'success');
            } else {
                showModal('No se detectó número', 'No se pudo detectar un número en la imagen. Intenta nuevamente con una imagen más clara del medidor.', 'warning');
            }

            // Cerrar modal
            if (modal && modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        }).catch(error => {
            console.error('Error en OCR:', error);
            statusDiv.textContent = 'Error en el procesamiento.';
            showModal('Error', 'Error al procesar la imagen. Inténtalo de nuevo.', 'error');
            if (modal && modal.parentNode) {
                modal.parentNode.removeChild(modal);
            }
        });
    }
});