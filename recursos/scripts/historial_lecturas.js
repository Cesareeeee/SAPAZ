// historial_lecturas.js - v2.05000

document.addEventListener('DOMContentLoaded', function () {
    const historialContainer = document.getElementById('historialContainer');
    const btnPrev = document.getElementById('btnPrev');
    const btnNext = document.getElementById('btnNext');
    const pageInfo = document.getElementById('pageInfo');
    const loadingOverlay = document.getElementById('loadingOverlay');

    // Modal elements
    const modalBackdrop = document.getElementById('customModalBackdrop');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalActions = document.getElementById('modalActions');

    // Edit modal elements
    const editModalBackdrop = document.getElementById('editModalBackdrop');
    const editForm = document.getElementById('editForm');
    const editCloseBtn = document.getElementById('editCloseBtn');
    const editCancelBtn = document.getElementById('editCancelBtn');
    const editFecha = document.getElementById('editFecha');
    const editLecturaAnterior = document.getElementById('editLecturaAnterior');
    const editLecturaActual = document.getElementById('editLecturaActual');
    const editConsumo = document.getElementById('editConsumo');
    const editNombre = document.getElementById('editNombre');
    const editNoMedidor = document.getElementById('editNoMedidor');
    const editDireccion = document.getElementById('editDireccion');
    const editObservaciones = document.getElementById('editObservaciones');

    // View modal elements
    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const viewCloseBtn = document.getElementById('viewCloseBtn');
    const viewNombre = document.getElementById('viewNombre');
    const viewNoMedidor = document.getElementById('viewNoMedidor');
    const viewDireccion = document.getElementById('viewDireccion');
    const viewIdLectura = document.getElementById('viewIdLectura');
    const viewFecha = document.getElementById('viewFecha');
    const viewLecturaAnterior = document.getElementById('viewLecturaAnterior');
    const viewLecturaActual = document.getElementById('viewLecturaActual');
    const viewConsumo = document.getElementById('viewConsumo');
    const viewRegistradoPor = document.getElementById('viewRegistradoPor');
    const viewObservaciones = document.getElementById('viewObservaciones');
    const viewAgregado = document.getElementById('viewAgregado');

    // Filtros elements
    const inputBusqueda = document.getElementById('inputBusqueda');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const btnConsumoAlto = document.getElementById('btnConsumoAlto');
    const btnConsumoAlterado = document.getElementById('btnConsumoAlterado');
    const filtroMes = document.getElementById('filtroMes');
    const filtroAnio = document.getElementById('filtroAnio');
    const filtroCalle = document.getElementById('filtroCalle');
    const filtroBarrio = document.getElementById('filtroBarrio');
    const toggleFecha = document.getElementById('toggleFecha');
    const fechaContent = document.getElementById('fechaContent');
    const toggleUbicacion = document.getElementById('toggleUbicacion');
    const ubicacionContent = document.getElementById('ubicacionContent');
    const btnLimpiarFiltros = document.getElementById('btnLimpiarFiltros');
    const filtrosActivos = document.getElementById('filtrosActivos');
    const filtrosActivosLista = document.getElementById('filtrosActivosLista');

    let paginaActual = 1;
    let totalPaginas = 1;
    const limitePorPagina = 12;
    let timeoutBusqueda = null;
    let autoRefreshInterval = null;
    let autoRefreshActive = true;
    let isLoading = false;

    // Estado de filtros
    let filtrosEstado = {
        busqueda: '',
        consumoTipo: '',
        mes: '',
        anio: '',
        calle: '',
        barrio: '',
        orden: 'desc'
    };

    // Inicializar selectores de fecha
    function inicializarFechas() {
        // Poblar años (últimos 5 años + año actual + próximo año)
        const anioActual = new Date().getFullYear();
        for (let i = anioActual + 1; i >= anioActual - 5; i--) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = i;
            filtroAnio.appendChild(option);
        }
    }

    // Inicializar selectores de ubicación
    function inicializarUbicacion() {
        // Poblar calles
        fetch('../controladores/historial_lecturas.php?action=get_calles')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.calles.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle.calle;
                        option.textContent = calle.calle;
                        filtroCalle.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error cargando calles:', error));

        // Poblar barrios
        fetch('../controladores/historial_lecturas.php?action=get_barrios')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.barrios.forEach(barrio => {
                        const option = document.createElement('option');
                        option.value = barrio.barrio;
                        option.textContent = barrio.barrio;
                        filtroBarrio.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error cargando barrios:', error));
    }

    // Cargar historial con filtros
    function cargarHistorial(pagina = 1) {
        paginaActual = pagina;
        mostrarLoading(true);

        const params = new URLSearchParams({
            action: 'get_history',
            pagina: pagina,
            limite: limitePorPagina
        });

        // Agregar filtros activos
        if (filtrosEstado.busqueda) {
            params.append('busqueda_usuario', filtrosEstado.busqueda);
        }
        if (filtrosEstado.consumoTipo) {
            params.append('consumo_tipo', filtrosEstado.consumoTipo);
        }
        if (filtrosEstado.mes) {
            params.append('mes', filtrosEstado.mes);
        }
        if (filtrosEstado.anio) {
            params.append('anio', filtrosEstado.anio);
        }
        if (filtrosEstado.calle) {
            params.append('calle', filtrosEstado.calle);
        }
        if (filtrosEstado.barrio) {
            params.append('barrio', filtrosEstado.barrio);
        }
        params.append('orden', filtrosEstado.orden);

        fetch(`../controladores/historial_lecturas.php?${params}`)
            .then(response => response.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    totalPaginas = data.total_paginas;
                    mostrarHistorial(data.lecturas);
                    actualizarNavegacion();
                } else {
                    historialContainer.innerHTML = '<div class="no-results">Error al cargar datos</div>';
                }
            })
            .catch(error => {
                mostrarLoading(false);
                console.error('Error:', error);
                historialContainer.innerHTML = '<div class="no-results">Error de conexión</div>';
            });
    }

    // Mostrar historial en cards
    function mostrarHistorial(lecturas) {
        if (lecturas.length === 0) {
            historialContainer.innerHTML = '<div class="no-results"><i class="fas fa-search"></i><br>No se encontraron lecturas con los filtros aplicados</div>';
            return;
        }

        let html = '';
        lecturas.forEach(lectura => {
            const fecha = new Date(lectura.fecha_lectura).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            const consumoClass = lectura.consumo_m3 < 0 ? 'consumo-negativo' : lectura.consumo_m3 > 30 ? 'consumo-alto' : 'consumo-normal';

            html += `
                <div class="card-wrapper">
                    <div class="lectura-card">
                        <div class="card-header">
                            <div class="card-date">${fecha}</div>
                            <div class="card-user"><span class="user-name">${lectura.nombre}</span> - <span class="user-meter">Medidor: ${lectura.no_medidor}</span></div>
                        </div>
                        <div class="card-body">
                            <div class="card-item">
                                <div class="card-label">Lectura Anterior</div>
                                <div class="card-value">${parseFloat(lectura.lectura_anterior).toFixed(2)} m³</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Lectura Actual</div>
                                <div class="card-value">${parseFloat(lectura.lectura_actual).toFixed(2)} m³</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Consumo</div>
                                <div class="card-value ${consumoClass}">${parseFloat(lectura.consumo_m3).toFixed(2)} m³</div>
                            </div>
                            <div class="card-item">
                                <div class="card-label">Dirección</div>
                                <div class="card-value">${lectura.calle || 'N/A'}${lectura.barrio ? ', ' + lectura.barrio : ''}</div>
                            </div>
                        </div>
                        ${lectura.observaciones ? `<div class="card-observaciones" style="background-color: #e3f2fd; border: 1px solid #2196f3; padding: 10px; border-radius: 4px; margin-top: 5px;"><strong style="color: #1976d2; font-weight: bold;">OBSERVACIONES:</strong> <span style="color: #dc2626; font-size: 1.1em;">${lectura.observaciones}</span></div>` : ''}
                    </div>
                    <div class="card-actions-external">
                        <button class="btn-view" data-id="${lectura.id_lectura}" title="Ver detalles"><i class="fas fa-eye"></i></button>
                        <button class="btn-edit" data-id="${lectura.id_lectura}" title="Editar"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete" data-id="${lectura.id_lectura}" title="Eliminar"><i class="fas fa-trash"></i></button>
                    </div>
                </div>
            `;
        });
        historialContainer.innerHTML = html;
    }

    // Actualizar navegación
    function actualizarNavegacion() {
        pageInfo.textContent = `Página ${paginaActual}`;

        if (paginaActual <= 1) {
            btnPrev.classList.add('nav-btn-disabled');
        } else {
            btnPrev.classList.remove('nav-btn-disabled');
        }

        if (paginaActual >= totalPaginas) {
            btnNext.classList.add('nav-btn-disabled');
        } else {
            btnNext.classList.remove('nav-btn-disabled');
        }
    }

    // Actualizar indicador de filtros activos
    function actualizarFiltrosActivos() {
        const tags = [];

        if (filtrosEstado.busqueda) {
            tags.push({ texto: `Búsqueda: "${filtrosEstado.busqueda}"`, tipo: 'busqueda' });
        }
        if (filtrosEstado.consumoTipo === 'alto') {
            tags.push({ texto: 'Consumo Alto', tipo: 'consumoTipo' });
        }
        if (filtrosEstado.consumoTipo === 'negativo') {
            tags.push({ texto: 'Medidor Alterado', tipo: 'consumoTipo' });
        }
        if (filtrosEstado.mes) {
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            tags.push({ texto: `Mes: ${meses[parseInt(filtrosEstado.mes) - 1]}`, tipo: 'mes' });
        }
        if (filtrosEstado.anio) {
            tags.push({ texto: `Año: ${filtrosEstado.anio}`, tipo: 'anio' });
        }
        if (filtrosEstado.calle) {
            tags.push({ texto: `Calle: ${filtrosEstado.calle}`, tipo: 'calle' });
        }
        if (filtrosEstado.barrio) {
            tags.push({ texto: `Barrio: ${filtrosEstado.barrio}`, tipo: 'barrio' });
        }
        if (filtrosEstado.orden === 'desc') {
            tags.push({ texto: 'Recientes primero', tipo: 'orden' });
        }

        if (tags.length > 0) {
            filtrosActivosLista.innerHTML = tags.map(tag =>
                `<span class="filtro-tag">${tag.texto} <i class="fas fa-times" data-tipo="${tag.tipo}"></i></span>`
            ).join('');
            filtrosActivos.style.display = 'block';
        } else {
            filtrosActivos.style.display = 'none';
        }
    }

    // Event listeners para filtros
    inputBusqueda.addEventListener('input', function () {
        const valor = this.value.trim();

        if (valor.length > 0) {
            btnClearSearch.style.display = 'flex';
        } else {
            btnClearSearch.style.display = 'none';
        }

        filtrosEstado.busqueda = valor;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    btnClearSearch.addEventListener('click', function () {
        inputBusqueda.value = '';
        filtrosEstado.busqueda = '';
        btnClearSearch.style.display = 'none';
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    btnConsumoAlto.addEventListener('click', function () {
        if (filtrosEstado.consumoTipo === 'alto') {
            filtrosEstado.consumoTipo = '';
            this.classList.remove('active');
        } else {
            filtrosEstado.consumoTipo = 'alto';
            this.classList.add('active');
            btnConsumoAlterado.classList.remove('active');
        }
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    btnConsumoAlterado.addEventListener('click', function () {
        if (filtrosEstado.consumoTipo === 'negativo') {
            filtrosEstado.consumoTipo = '';
            this.classList.remove('active');
        } else {
            filtrosEstado.consumoTipo = 'negativo';
            this.classList.add('active');
            btnConsumoAlto.classList.remove('active');
        }
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    filtroMes.addEventListener('change', function () {
        filtrosEstado.mes = this.value;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    filtroAnio.addEventListener('change', function () {
        filtrosEstado.anio = this.value;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    filtroCalle.addEventListener('change', function () {
        filtrosEstado.calle = this.value;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    filtroBarrio.addEventListener('change', function () {
        filtrosEstado.barrio = this.value;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    const filtroOrden = document.getElementById('filtroOrden');
    filtroOrden.addEventListener('change', function () {
        filtrosEstado.orden = this.value;
        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    // Toggle buttons
    toggleFecha.addEventListener('click', function () {
        const isOpen = fechaContent.style.display !== 'none';
        fechaContent.style.display = isOpen ? 'none' : 'grid';
        this.classList.toggle('active');
    });

    toggleUbicacion.addEventListener('click', function () {
        const isOpen = ubicacionContent.style.display !== 'none';
        ubicacionContent.style.display = isOpen ? 'none' : 'grid';
        this.classList.toggle('active');
    });

    btnLimpiarFiltros.addEventListener('click', function () {
        // Limpiar todos los filtros
        filtrosEstado = {
            busqueda: '',
            consumoTipo: '',
            mes: '',
            anio: '',
            calle: '',
            barrio: '',
            orden: 'asc'
        };

        inputBusqueda.value = '';
        btnClearSearch.style.display = 'none';
        btnConsumoAlto.classList.remove('active');
        btnConsumoAlterado.classList.remove('active');
        filtroMes.value = '';
        filtroAnio.value = '';
        filtroCalle.value = '';
        filtroBarrio.value = '';
        filtroOrden.value = 'desc';

        actualizarFiltrosActivos();
        cargarHistorial(1);
    });

    // Event delegation para eliminar filtros individuales
    filtrosActivosLista.addEventListener('click', function (e) {
        if (e.target.classList.contains('fa-times')) {
            const tipo = e.target.dataset.tipo;

            switch (tipo) {
                case 'busqueda':
                    inputBusqueda.value = '';
                    filtrosEstado.busqueda = '';
                    btnClearSearch.style.display = 'none';
                    break;
                case 'consumoTipo':
                    filtrosEstado.consumoTipo = '';
                    btnConsumoAlto.classList.remove('active');
                    btnConsumoAlterado.classList.remove('active');
                    break;
                case 'mes':
                    filtrosEstado.mes = '';
                    filtroMes.value = '';
                    break;
                case 'anio':
                    filtrosEstado.anio = '';
                    filtroAnio.value = '';
                    break;
                case 'calle':
                    filtrosEstado.calle = '';
                    filtroCalle.value = '';
                    break;
                case 'barrio':
                    filtrosEstado.barrio = '';
                    filtroBarrio.value = '';
                    break;
                case 'orden':
                    filtrosEstado.orden = 'desc';
                    filtroOrden.value = 'desc';
                    break;
            }

            actualizarFiltrosActivos();
            cargarHistorial(1);
        }
    });

    // Event delegation para botones de acción
    historialContainer.addEventListener('click', function (e) {
        const cardWrapper = e.target.closest('.card-wrapper');
        if (cardWrapper) {
            if (e.target.closest('.btn-edit')) {
                const id = e.target.closest('.btn-edit').dataset.id;
                mostrarModalEdicion(id);
            } else if (e.target.closest('.btn-delete')) {
                const id = e.target.closest('.btn-delete').dataset.id;
                confirmarEliminacion(id);
            } else {
                // Cualquier otro clic en la card, mostrar vista
                const btnView = cardWrapper.querySelector('.btn-view');
                if (btnView) {
                    const id = btnView.dataset.id;
                    mostrarModalVista(id);
                }
            }
        }
    });

    // Eventos para modal de vista
    viewCloseBtn.addEventListener('click', () => {
        viewModalBackdrop.style.display = 'none';
        reanudarAutoRefresh();
    });

    viewModalBackdrop.addEventListener('click', (e) => {
        if (e.target === viewModalBackdrop) {
            viewModalBackdrop.style.display = 'none';
            reanudarAutoRefresh();
        }
    });

    // Mostrar modal de vista
    function mostrarModalVista(id) {
        fetch(`../controladores/historial_lecturas.php?action=get_lectura&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const lectura = data.lectura;
                    const fecha = new Date(lectura.fecha_lectura).toLocaleDateString('es-ES');
                    const consumoClass = lectura.consumo_m3 < 0 ? 'consumo-negativo' : lectura.consumo_m3 > 30 ? 'consumo-alto' : 'consumo-normal';

                    viewNombre.textContent = lectura.nombre;
                    viewNoMedidor.textContent = lectura.no_medidor;
                    viewDireccion.textContent = `${lectura.calle || 'N/A'}${lectura.barrio ? ', ' + lectura.barrio : ''}`;
                    viewConsumo.textContent = `${parseFloat(lectura.consumo_m3).toFixed(2)} m³`;
                    viewConsumo.className = `view-info-value ${consumoClass}`;
                    viewIdLectura.textContent = lectura.id_lectura;
                    viewFecha.textContent = fecha;
                    viewLecturaAnterior.textContent = `${parseFloat(lectura.lectura_anterior).toFixed(2)} m³`;
                    viewLecturaActual.textContent = `${parseFloat(lectura.lectura_actual).toFixed(2)} m³`;
                    viewObservaciones.textContent = lectura.observaciones || 'Sin observaciones';
                    viewAgregado.textContent = new Date(lectura.created_at).toLocaleString('es-ES');

                    // Mostrar el nombre del usuario que registró la lectura
                    viewRegistradoPor.textContent = lectura.registrado_por || 'No disponible';

                    pausarAutoRefresh();
                    viewModalBackdrop.style.display = 'flex';
                } else {
                    showModal('Error', 'No se pudo cargar la información de la lectura', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Error', 'Error de conexión', 'error');
            });
    }

    // Mostrar/ocultar loading
    function mostrarLoading(mostrar) {
        isLoading = mostrar;
        loadingOverlay.style.display = mostrar ? 'flex' : 'none';
    }

    // Controlar auto-refresh
    function pausarAutoRefresh() {
        autoRefreshActive = false;
    }

    function reanudarAutoRefresh() {
        autoRefreshActive = true;
    }

    // Función para mostrar modal personalizado
    function showModal(title, message, type = 'info', onConfirm = null, onCancel = null) {
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
        modalTitle.textContent = title;
        modalMessage.innerHTML = message;
        modalActions.innerHTML = '';

        if (type === 'warning') {
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
        } else {
            const okBtn = document.createElement('button');
            okBtn.className = 'modal-btn ' + (type === 'error' ? 'btn-error-modal' : 'btn-confirm');
            okBtn.innerHTML = '<i class="fas fa-check"></i> Entendido';
            okBtn.onclick = () => {
                closeModal();
                if (onConfirm) onConfirm();
            };
            modalActions.appendChild(okBtn);
        }

        pausarAutoRefresh();
        modalBackdrop.classList.add('show');
        modalBackdrop.style.display = 'flex';

        if (type === 'success' || type === 'error') {
            setTimeout(() => {
                closeModal();
            }, 5000);
        }
    }

    function closeModal() {
        modalBackdrop.classList.remove('show');
        setTimeout(() => {
            modalBackdrop.style.display = 'none';
            reanudarAutoRefresh();
        }, 300);
    }

    function closeEditModal() {
        editModalBackdrop.classList.remove('show');
        setTimeout(() => {
            editModalBackdrop.style.display = 'none';
            reanudarAutoRefresh();
        }, 300);
    }

    // Eventos de navegación
    btnPrev.addEventListener('click', function () {
        if (paginaActual > 1) {
            cargarHistorial(paginaActual - 1);
        }
    });

    btnNext.addEventListener('click', function () {
        if (paginaActual < totalPaginas) {
            cargarHistorial(paginaActual + 1);
        }
    });

    // Event delegation for edit and delete buttons
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-edit')) {
            const id = e.target.closest('.btn-edit').dataset.id;
            editarLectura(id);
        }
        if (e.target.closest('.btn-delete')) {
            const id = e.target.closest('.btn-delete').dataset.id;
            eliminarLectura(id);
        }
    });

    // Edit modal events
    editCloseBtn.addEventListener('click', closeEditModal);
    editCancelBtn.addEventListener('click', closeEditModal);

    // Calcular consumo automáticamente
    editLecturaActual.addEventListener('input', function () {
        const anterior = parseFloat(editLecturaAnterior.value) || 0;
        const actual = parseFloat(this.value) || 0;
        const consumo = actual - anterior;
        editConsumo.value = consumo.toFixed(2);
    });

    // Form submit
    editForm.addEventListener('submit', function (e) {
        e.preventDefault();
        const id = editForm.dataset.id;
        const fecha = editFecha.value;
        const lecturaActual = parseFloat(editLecturaActual.value);
        const consumo = parseFloat(editConsumo.value);
        const nombre = editNombre.textContent;
        const observaciones = editObservaciones.value;

        const mensajeConfirmacion = `
            <div style="text-align: left; padding: 0.5rem;">
                <p><strong>Beneficiario:</strong> ${nombre}</p>
                <p><strong>Fecha:</strong> ${new Date(fecha).toLocaleDateString('es-ES')}</p>
                <p><strong>Lectura Actual:</strong> ${lecturaActual.toFixed(2)} m³</p>
                <p><strong>Consumo:</strong> ${consumo.toFixed(2)} m³</p>
                ${observaciones ? `<p><strong>Observaciones:</strong> ${observaciones}</p>` : ''}
            </div>
        `;

        closeEditModal();
        showModal(
            '¿Confirmar Cambios?',
            mensajeConfirmacion,
            'warning',
            () => {
                mostrarLoading(true);

                fetch('../controladores/historial_lecturas.php?action=update_lectura', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        id_lectura: id,
                        fecha_lectura: fecha,
                        lectura_actual: lecturaActual,
                        observaciones: observaciones
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        mostrarLoading(false);
                        if (data.success) {
                            showModal('Éxito', 'Lectura actualizada correctamente', 'success');
                            cargarHistorial(paginaActual);
                        } else {
                            showModal('Error', data.message || 'Error al actualizar', 'error');
                        }
                    })
                    .catch(error => {
                        mostrarLoading(false);
                        console.error('Error:', error);
                        showModal('Error', 'Error de conexión al guardar', 'error');
                    });
            }
        );
    });

    // Función para editar lectura
    function editarLectura(id) {
        mostrarLoading(true);
        fetch(`../controladores/historial_lecturas.php?action=get_lectura&id=${id}`)
            .then(response => response.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    const lectura = data.lectura;
                    editFecha.value = lectura.fecha_lectura.split(' ')[0];
                    editLecturaAnterior.value = parseFloat(lectura.lectura_anterior).toFixed(2);
                    editLecturaActual.value = parseFloat(lectura.lectura_actual).toFixed(2);
                    editConsumo.value = parseFloat(lectura.consumo_m3).toFixed(2);
                    editNombre.textContent = lectura.nombre;
                    editNoMedidor.textContent = lectura.no_medidor;
                    editDireccion.textContent = `${lectura.calle || 'N/A'}${lectura.barrio ? ', ' + lectura.barrio : ''}`;
                    editObservaciones.value = lectura.observaciones || '';

                    editForm.dataset.id = id;

                    pausarAutoRefresh();
                    editModalBackdrop.classList.add('show');
                    editModalBackdrop.style.display = 'flex';
                } else {
                    showModal('Error', 'No se pudieron cargar los datos', 'error');
                }
            })
            .catch(error => {
                mostrarLoading(false);
                console.error('Error:', error);
                showModal('Error', 'Error de conexión', 'error');
            });
    }

    // Función para eliminar lectura
    function eliminarLectura(id) {
        showModal('Confirmar Eliminación', '¿Estás seguro de eliminar esta lectura?', 'warning', () => {
            mostrarLoading(true);

            fetch('../controladores/historial_lecturas.php?action=delete_lectura', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id_lectura: id })
            })
                .then(response => response.json())
                .then(data => {
                    mostrarLoading(false);
                    if (data.success) {
                        showModal('Éxito', 'Lectura eliminada correctamente', 'success');
                        cargarHistorial(paginaActual);
                    } else {
                        showModal('Error', data.message || 'Error al eliminar', 'error');
                    }
                })
                .catch(error => {
                    mostrarLoading(false);
                    console.error('Error:', error);
                    showModal('Error', 'Error de conexión', 'error');
                });
        });
    }

    // Inicializar
    inicializarFechas();
    inicializarUbicacion();
    cargarHistorial();

    // Auto-refresh cada 15 segundos
    autoRefreshInterval = setInterval(() => {
        if (autoRefreshActive && !isLoading) {
            cargarHistorial(paginaActual);
        }
    }, 15000);
});