      // Función de Alerta Personalizada (Toast Style)
function mostrarAlerta(titulo, mensaje, tipo = 'success', autoCerrar = true) {
    if (tipo === 'error') autoCerrar = false;

    // Crear elemento de alerta
    const alerta = document.createElement('div');
    alerta.className = 'custom-alert';
    alerta.innerHTML = `
        <div class="custom-alert-content ${tipo}">
            <div class="custom-alert-icon">
                <i class="fas ${tipo === 'success' ? 'fa-check-circle' : tipo === 'error' ? 'fa-times-circle' : tipo === 'loading' ? 'fa-spinner fa-spin' : 'fa-exclamation-triangle'}"></i>
            </div>
            <div class="custom-alert-body">
                <h3>${titulo}</h3>
                <p>${mensaje}</p>
            </div>
            <button class="custom-alert-close">&times;</button>
        </div>
    `;
    document.body.appendChild(alerta);

    // Mostrar con animación
    setTimeout(() => alerta.classList.add('show'), 10);

    // Cerrar al hacer clic en el botón
    alerta.querySelector('.custom-alert-close').addEventListener('click', () => {
        alerta.classList.remove('show');
        setTimeout(() => { if (document.body.contains(alerta)) document.body.removeChild(alerta); }, 300);
    });

    // Cerrar automáticamente
    if (autoCerrar) {
        setTimeout(() => {
            if (document.body.contains(alerta)) {
                alerta.classList.remove('show');
                setTimeout(() => { if (document.body.contains(alerta)) document.body.removeChild(alerta); }, 300);
            }
        }, 10000); // 10 seconds
    }
    return alerta;
}

// Validación de Beneficiarios
document.addEventListener('DOMContentLoaded', function () {
    // --- Variables Globales y Elementos ---
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    const searchInput = document.getElementById('searchInput');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const streetFilter = document.getElementById('streetFilter');
    const barrioFilter = document.getElementById('barrioFilter');
    const beneficiariesContainer = document.getElementById('beneficiariesTableBody');
    const loadingOverlay = document.createElement('div');
    let currentPage = 1;
    let totalPages = 1;
    let currentSearchTerm = ''; // Variable para almacenar el término de búsqueda actual
    let currentCalle = ''; // Variable para almacenar el filtro de calle actual
    let currentBarrio = ''; // Variable para almacenar el filtro de barrio actual
    let searchTimeout = null; // Timeout para debounce de búsqueda
    loadingOverlay.className = 'loading-overlay';
    loadingOverlay.id = 'loadingOverlay';
    loadingOverlay.innerHTML = '<div class="spinner"></div><p>Cargando...</p>';
    if (!document.getElementById('loadingOverlay')) document.body.appendChild(loadingOverlay);

    // Modals
    const modalBackdrop = document.getElementById('customModalBackdrop');
    const modalIcon = document.getElementById('modalIcon');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const modalActions = document.getElementById('modalActions');

    const editModalBackdrop = document.getElementById('editModalBackdrop');
    const editForm = document.getElementById('editForm');
    const editCloseBtn = document.getElementById('editCloseBtn');
    const editCancelBtn = document.getElementById('editCancelBtn');

    const viewModalBackdrop = document.getElementById('viewModalBackdrop');
    const viewCloseBtn = document.getElementById('viewCloseBtn');

    const lecturasModalBackdrop = document.getElementById('lecturasModalBackdrop');
    const lecturasCloseBtn = document.getElementById('lecturasCloseBtn');
    const viewLecturasBtn = document.getElementById('viewLecturasBtn');

    // --- Load Beneficiaries Function ---
    function loadBeneficiaries(page = 1, searchTerm = '', calle = '', barrio = '') {
        mostrarLoading(true);
        // Usar el endpoint de búsqueda que busca en toda la base de datos
        let url = `../controladores/beneficiarios.php?action=search_beneficiarios&page=${page}&search=${encodeURIComponent(searchTerm)}`;
        if (calle) url += `&calle=${encodeURIComponent(calle)}`;
        if (barrio) url += `&barrio=${encodeURIComponent(barrio)}`;
        fetch(url)
            .then(r => r.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    const container = document.getElementById('beneficiariesTableBody');
                    container.innerHTML = '';
                    data.beneficiarios.forEach(row => {
                        const estado = row.activo ? 'Activo' : 'Inactivo';
                        const statusClass = row.activo ? 'paid' : 'pending';
                        const html = `<div data-id="${row.id_usuario}" data-barrio="${row.barrio}" class="card-wrapper beneficiary-row">
                        <div class="beneficiary-card">
                            <div class="card-body">
                                <div class="card-item">
                                    <div class="card-label">Contrato</div>
                                    <div class="card-value card-contrato">${row.no_contrato}</div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label"><i class="fas fa-user"></i> Nombre</div>
                                    <div class="card-value beneficiary-name">${row.nombre}</div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label"><i class="fas fa-tachometer-alt"></i> Medidor</div>
                                    <div class="card-value beneficiary-medidor">${row.no_medidor}</div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label"><i class="fas fa-map-marker-alt"></i> Calle, Barrio</div>
                                    <div class="card-value">${row.calle}, ${row.barrio}</div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label">Fecha Alta</div>
                                    <div class="card-value">${row.fecha_alta}</div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label">Estado</div>
                                    <div class="card-value"><span class="status ${statusClass}">${estado}</span></div>
                                </div>
                                <div class="card-item">
                                    <div class="card-label"># Beneficiario</div>
                                    <div class="card-value">${row.id_usuario}</div>
                                </div>
                            </div>
                        </div>
                        <div class="card-actions-external">
                            <button class="btn-view" data-id="${row.id_usuario}" title="Ver detalles"><i class="fas fa-eye"></i></button>
                            <button class="btn-edit" data-id="${row.id_usuario}" title="Editar"><i class="fas fa-edit"></i></button>
                            <button class="btn-delete" data-id="${row.id_usuario}" title="Eliminar"><i class="fas fa-trash"></i></button>
                        </div>
                    </div>`;
                        container.insertAdjacentHTML('beforeend', html);
                    });
                    currentPage = data.page;
                    totalPages = Math.ceil(data.total / data.limit);
                    let pageInfoText = '';
                    if (searchTerm || calle || barrio) {
                        pageInfoText = `Resultados: ${data.total} encontrados - Página ${currentPage} de ${totalPages}`;
                    } else {
                        pageInfoText = totalPages > 0 ? `Página ${currentPage} de ${totalPages}` : 'No hay beneficiarios';
                    }
                    document.getElementById('pageInfo').textContent = pageInfoText;
                    document.getElementById('prevPage').disabled = currentPage <= 1;
                    document.getElementById('nextPage').disabled = currentPage >= totalPages;
                    document.querySelector('.pagination').style.display = totalPages > 0 ? 'flex' : 'none';
                    if (data.beneficiarios.length === 0) {
                        let noResultsMsg = 'No se encontraron beneficiarios';
                        if (searchTerm) noResultsMsg += ' que coincidan con la búsqueda';
                        if (calle) noResultsMsg += ` en la calle ${calle}`;
                        if (barrio) noResultsMsg += ` en el barrio ${barrio}`;
                        container.innerHTML = `<div class="no-results">${noResultsMsg}</div>`;
                    }
                } else {
                    showModal('Error', 'No se pudieron cargar los beneficiarios', 'error');
                }
            })
            .catch(err => {
                mostrarLoading(false);
                console.error('Error loading beneficiaries:', err);
                // showModal('Error', 'Error de conexión', 'error'); // Suppressed per user request
            });
    }

    // --- Tab Switching ---
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            tabContents.forEach(content => content.style.display = 'none');
            const tab = button.getAttribute('data-tab');
            document.getElementById(tab + 'Section').style.display = 'block';
            localStorage.setItem('activeTab', tab);
            if (tab === 'list') {
                // Resetear búsqueda y filtros al cambiar de pestaña
                currentSearchTerm = '';
                currentCalle = '';
                currentBarrio = '';
                if (searchInput) searchInput.value = '';
                if (streetFilter) streetFilter.value = '';
                if (barrioFilter) barrioFilter.value = '';
                if (btnClearSearch) btnClearSearch.style.display = 'none';
                loadBeneficiaries(currentPage, currentSearchTerm, currentCalle, currentBarrio);
            }
        });
    });

    // Check for URL parameter first
    const urlParams = new URLSearchParams(window.location.search);
    const tabParam = urlParams.get('tab');

    let activeTab = 'list';

    if (tabParam && document.querySelector(`[data-tab="${tabParam}"]`)) {
        activeTab = tabParam;
    } else {
        activeTab = localStorage.getItem('activeTab') || 'list';
    }

    if (document.querySelector(`[data-tab="${activeTab}"]`)) {
        document.querySelector(`[data-tab="${activeTab}"]`).click();
    } else {
        document.querySelector('[data-tab="list"]').click();
    }

    // --- Search & Filter Logic ---
    // Nueva función de búsqueda que busca en toda la base de datos
    function performSearch() {
        const searchValue = searchInput ? searchInput.value.trim() : '';

        if (btnClearSearch) btnClearSearch.style.display = searchValue.length > 0 ? 'flex' : 'none';

        // Actualizar el término de búsqueda actual
        currentSearchTerm = searchValue;

        // Resetear a la página 1 cuando se hace una nueva búsqueda
        currentPage = 1;

        // Cargar beneficiarios con el término de búsqueda y filtros actuales
        loadBeneficiaries(currentPage, currentSearchTerm, currentCalle, currentBarrio);

        // Actualizar el título
        updateTitle();
    }

    // Función para actualizar el título según los filtros activos
    function updateTitle() {
        const titleElement = document.querySelector('.table-title');
        if (!titleElement) return;

        let title = 'Lista de Beneficiarios';
        const filters = [];

        if (currentSearchTerm) filters.push(`Búsqueda: "${currentSearchTerm}"`);
        if (currentCalle) filters.push(`Calle: ${currentCalle}`);
        if (currentBarrio) filters.push(`Barrio: ${currentBarrio}`);

        if (filters.length > 0) {
            title = filters.join(' | ');
        }

        titleElement.textContent = title;
    }

    // Función de filtro por calle que busca en toda la base de datos
    function filterByStreet() {
        const streetValue = streetFilter ? streetFilter.value : '';

        // Actualizar filtro actual
        currentCalle = streetValue;
        currentBarrio = ''; // Limpiar filtro de barrio
        if (barrioFilter) barrioFilter.value = '';

        // Resetear a la página 1
        currentPage = 1;

        // Cargar beneficiarios con el filtro de calle
        loadBeneficiaries(currentPage, currentSearchTerm, currentCalle, currentBarrio);

        // Actualizar el título
        updateTitle();
    }

    // Función de filtro por barrio que busca en toda la base de datos
    function filterByBarrio() {
        const barrioValue = barrioFilter ? barrioFilter.value : '';

        // Actualizar filtro actual
        currentBarrio = barrioValue;
        currentCalle = ''; // Limpiar filtro de calle
        if (streetFilter) streetFilter.value = '';

        // Resetear a la página 1
        currentPage = 1;

        // Cargar beneficiarios con el filtro de barrio
        loadBeneficiaries(currentPage, currentSearchTerm, currentCalle, currentBarrio);

        // Actualizar el título
        updateTitle();
    }

    // Event listener para búsqueda con debounce (esperar 300ms después de que el usuario deje de escribir)
    if (searchInput) {
        searchInput.addEventListener('input', () => {
            // Limpiar timeout anterior
            if (searchTimeout) clearTimeout(searchTimeout);

            // Mostrar indicador de carga en el input
            searchInput.classList.add('searching');

            // Establecer nuevo timeout
            searchTimeout = setTimeout(() => {
                performSearch();
                searchInput.classList.remove('searching');
            }, 300); // Esperar 300ms después de que el usuario deje de escribir
        });
    }

    if (streetFilter) streetFilter.addEventListener('change', filterByStreet);
    if (barrioFilter) barrioFilter.addEventListener('change', filterByBarrio);
    if (btnClearSearch) btnClearSearch.addEventListener('click', () => {
        searchInput.value = '';
        currentSearchTerm = '';
        performSearch();
    });

    // Pagination - ahora respeta el término de búsqueda y filtros
    document.getElementById('prevPage').addEventListener('click', () => { if (currentPage > 1) loadBeneficiaries(currentPage - 1, currentSearchTerm, currentCalle, currentBarrio); });
    document.getElementById('nextPage').addEventListener('click', () => { if (currentPage < totalPages) loadBeneficiaries(currentPage + 1, currentSearchTerm, currentCalle, currentBarrio); });

    // --- Modal Functions ---
    // Wrapper to unify style: Use Toast for info/success/error, Modal for Warning/Confirm
    function showModal(title, message, type = 'info', onConfirm = null, onCancel = null) {
        if (type === 'success' || type === 'error' || type === 'info') {
            mostrarAlerta(title, message, type, true);
            return;
        }

        // Warning/Confirm Modal (Center)
        let iconHtml = '<i class="fas fa-exclamation-triangle"></i>';
        modalIcon.className = 'modal-icon warning';
        modalIcon.innerHTML = iconHtml;
        modalTitle.textContent = title;
        modalMessage.innerHTML = message;
        modalActions.innerHTML = '';

        if (type === 'warning') {
            const confirmBtn = document.createElement('button');
            confirmBtn.className = 'modal-btn btn-confirm';
            confirmBtn.innerHTML = '<i class="fas fa-check"></i> Continuar';
            confirmBtn.onclick = () => { closeModal(); if (onConfirm) onConfirm(); };

            const cancelBtn = document.createElement('button');
            cancelBtn.className = 'modal-btn btn-cancel';
            cancelBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
            cancelBtn.onclick = () => { closeModal(); if (onCancel) onCancel(); };

            modalActions.appendChild(cancelBtn);
            modalActions.appendChild(confirmBtn);
        } else {
            // Fallback
            const okBtn = document.createElement('button');
            okBtn.className = 'modal-btn btn-confirm';
            okBtn.textContent = 'Aceptar';
            okBtn.onclick = closeModal;
            modalActions.appendChild(okBtn);
        }

        modalBackdrop.classList.add('show');
        modalBackdrop.style.display = 'flex';
    }

    function closeModal() {
        modalBackdrop.classList.remove('show');
        setTimeout(() => modalBackdrop.style.display = 'none', 300);
    }

    function closeEditModal() {
        editModalBackdrop.classList.remove('show');
        setTimeout(() => editModalBackdrop.style.display = 'none', 300);
    }
    function closeViewModal() { viewModalBackdrop.style.display = 'none'; }
    function closeLecturasModal() { lecturasModalBackdrop.style.display = 'none'; }

    if (editCloseBtn) editCloseBtn.addEventListener('click', closeEditModal);
    if (editCancelBtn) editCancelBtn.addEventListener('click', closeEditModal);
    if (viewCloseBtn) viewCloseBtn.addEventListener('click', closeViewModal);
    if (lecturasCloseBtn) lecturasCloseBtn.addEventListener('click', closeLecturasModal);
    if (viewLecturasBtn) viewLecturasBtn.addEventListener('click', (e) => { e.preventDefault(); mostrarModalLecturas(document.getElementById('viewId').textContent); });
    window.addEventListener('click', (e) => { if (e.target === viewModalBackdrop) closeViewModal(); });
    window.addEventListener('click', (e) => { if (e.target === lecturasModalBackdrop) closeLecturasModal(); });

    function mostrarLoading(mostrar) {
        loadingOverlay.style.display = mostrar ? 'flex' : 'none';
    }

    // --- Action Buttons ---
    beneficiariesContainer.addEventListener('click', function (e) {
        const btnEdit = e.target.closest('.btn-edit');
        const btnDelete = e.target.closest('.btn-delete');
        const btnView = e.target.closest('.btn-view');
        const card = e.target.closest('.beneficiary-card');

        if (btnEdit) mostrarModalEdicion(btnEdit.dataset.id);
        else if (btnDelete) confirmarEliminacion(btnDelete.dataset.id);
        else if (btnView) mostrarModalVista(btnView.dataset.id);
        else if (card && !btnEdit && !btnDelete && !btnView) {
            // Click on card itself, open lecturas
            const id = card.closest('.beneficiary-row').dataset.id;
            mostrarModalLecturas(id);
        }
    });

    // --- View Modal Logic ---
    function mostrarModalVista(id) {
        mostrarLoading(true);
        fetch(`../controladores/beneficiarios.php?action=get_usuario&id=${id}`)
            .then(r => r.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    const u = data.usuario;
                    document.getElementById('viewNombre').textContent = u.nombre;
                    document.getElementById('viewContrato').textContent = u.no_contrato;
                    document.getElementById('viewMedidor').textContent = u.no_medidor;
                    document.getElementById('viewEstado').textContent = u.activo == 1 ? 'Activo' : 'Inactivo';
                    document.getElementById('viewEstado').className = `view-info-value ${u.activo == 1 ? 'status paid' : 'status pending'}`;
                    document.getElementById('viewDireccion').textContent = `${u.calle}, ${u.barrio}`;
                    document.getElementById('viewId').textContent = u.id_usuario;
                    document.getElementById('viewFecha').textContent = u.fecha_alta;

                    // Previous Name View
                    const viewPrevContainer = document.getElementById('viewPreviousNameContainer');
                    const viewPrevDisplay = document.getElementById('viewPreviousName');
                    if (viewPrevContainer && viewPrevDisplay) {
                        if (u.nombre_anterior && u.nombre_anterior !== u.nombre) {
                            viewPrevDisplay.textContent = u.nombre_anterior;
                            viewPrevContainer.style.display = 'block';
                        } else {
                            viewPrevContainer.style.display = 'none';
                        }
                    }

                    viewModalBackdrop.style.display = 'flex';
                } else showModal('Error', 'No se pudo cargar la información', 'error');
            })
            .catch(() => { mostrarLoading(false); showModal('Error', 'Error de conexión', 'error'); });
    }

    // --- Lecturas Modal Logic ---
    function mostrarModalLecturas(id) {
        // Clear previous data immediately
        document.getElementById('lecturasNombre').textContent = 'Cargando...';
        document.getElementById('lecturasMedidor').textContent = '...';
        document.getElementById('lecturasContainer').innerHTML = '';

        mostrarLoading(true);
        fetch(`../controladores/lecturas.php?action=get_lecturas_usuario&id_usuario=${id}`)
            .then(r => r.json())
            .then(data => {
                mostrarLoading(false);
                if (data.success) {
                    // Set user info from server response
                    if (data.usuario) {
                        document.getElementById('lecturasNombre').textContent = data.usuario.nombre;
                        document.getElementById('lecturasMedidor').textContent = data.usuario.no_medidor;
                    } else {
                        // Fallback if user data is missing for some reason
                        document.getElementById('lecturasNombre').textContent = 'Usuario Desconocido';
                        document.getElementById('lecturasMedidor').textContent = 'N/A';
                    }

                    // Populate grouped readings
                    const container = document.getElementById('lecturasContainer');
                    container.innerHTML = '';
                    const lecturasKeys = Object.keys(data.lecturas);
                    if (lecturasKeys.length === 0) {
                        container.innerHTML = '<div style="text-align: center; padding: 2rem; color: #64748b;">No hay lecturas registradas</div>';
                    } else {
                        lecturasKeys.forEach(mesAnio => {
                            const mesSection = document.createElement('div');
                            mesSection.className = 'lecturas-mes-section';

                            const readingsHtml = data.lecturas[mesAnio].map(lectura => {
                                const estado = lectura.estado_pago || 'Sin Factura';
                                let statusClass = 'neutral';
                                let icon = 'fa-file';

                                if (estado === 'Pagado') {
                                    statusClass = 'paid';
                                    icon = 'fa-check-circle';
                                } else if (estado === 'Pendiente') {
                                    statusClass = 'warning';
                                    icon = 'fa-clock';
                                } else if (estado === 'Cancelado') {
                                    statusClass = 'canceled';
                                    icon = 'fa-ban';
                                }

                                let acciones = '';

                                // Botón Pagar - Solo si no está pagado
                                if (estado !== 'Pagado') {
                                    acciones += `<button class="card-action-btn btn-pagar" data-id="${lectura.id_lectura}" title="Ir a pagar esta lectura" style="margin-right: 0.5rem; font-size: 0.8rem; padding: 0.4rem 0.8rem;"><i class="fas fa-credit-card"></i> Pagar</button>`;
                                }

                                // Botón Editar Estado - Siempre disponible
                                acciones += `<button class="card-action-btn edit-status-btn" data-id="${lectura.id_lectura}" data-estado="${estado}" title="Editar estado de pago" style="font-size: 0.8rem; padding: 0.4rem 0.8rem;"><i class="fas fa-edit"></i> Editar Estado</button>`;

                                return `
                                    <tr>
                                        <td>${lectura.fecha_lectura}</td>
                                        <td>${lectura.lectura_anterior}</td>
                                        <td>${lectura.lectura_actual}</td>
                                        <td style="font-weight: bold; color: #000000;">${lectura.consumo_m3} m³</td>
                                        <td>${lectura.observaciones ? `<span style="color: #ff0000; font-weight: bold; background-color: #87ceeb; padding: 0.25rem 0.5rem; border-radius: 4px;">${lectura.observaciones}</span>` : '<span style="color: #9ca3af; font-style: italic;">Sin observaciones</span>'}</td>
                                        <td>
                                            <span class="status-badge ${statusClass}">
                                                <i class="fas ${icon}"></i> ${estado}
                                            </span>
                                        </td>
                                        <td>${acciones}</td>
                                    </tr>
                                `;
                            }).join('');

                            mesSection.innerHTML = `
                                <h4 class="lecturas-mes-title"><i class="fas fa-calendar-alt"></i> ${mesAnio}</h4>
                                <div class="table-responsive">
                                    <table class="lecturas-table">
                                        <thead>
                                            <tr>
                                                <th>Fecha</th>
                                                <th>L. Anterior</th>
                                                <th>L. Actual</th>
                                                <th>Consumo</th>
                                                <th>Observaciones</th>
                                                <th>Estado Pago</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            ${readingsHtml}
                                        </tbody>
                                    </table>
                                </div>
                            `;
                            container.appendChild(mesSection);
                        });
                    }

                    // Agregar listeners para el botón Pagar
                    document.querySelectorAll('.btn-pagar').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const idLectura = this.dataset.id;
                            // Redirigir a la sección de facturación con el id_lectura como parámetro
                            window.location.href = `../vistas/facturacion.php?id_lectura=${idLectura}`;
                        });
                    });

                    // Agregar listeners para editar estado
                    document.querySelectorAll('.edit-status-btn').forEach(btn => {
                        btn.addEventListener('click', function () {
                            const idLectura = this.dataset.id;
                            const estadoActual = this.dataset.estado;

                            // Crear opciones de estado
                            let opcionesEstado = '';
                            const estados = ['Pendiente', 'Pagado', 'Cancelado'];

                            estados.forEach(estado => {
                                if (estado !== estadoActual) {
                                    let btnClass = 'btn-primary';
                                    let iconClass = 'fa-clock';

                                    if (estado === 'Pagado') {
                                        btnClass = 'btn-success';
                                        iconClass = 'fa-check-circle';
                                    } else if (estado === 'Cancelado') {
                                        btnClass = 'btn-warning';
                                        iconClass = 'fa-ban';
                                    }

                                    opcionesEstado += `<button class="btn ${btnClass} change-status-btn" data-estado="${estado}" style="margin: 0.5rem;"><i class="fas ${iconClass}"></i> ${estado}</button>`;
                                }
                            });

                            // Mostrar modal personalizado
                            const modalBackdrop = document.getElementById('customModalBackdrop');
                            const modalIcon = document.getElementById('modalIcon');
                            const modalTitle = document.getElementById('modalTitle');
                            const modalMessage = document.getElementById('modalMessage');
                            const modalActions = document.getElementById('modalActions');

                            modalIcon.className = 'modal-icon warning';
                            modalIcon.innerHTML = '<i class="fas fa-edit"></i>';
                            modalTitle.textContent = 'Cambiar Estado de Pago';
                            modalMessage.innerHTML = `
                                <p style="margin-bottom: 1rem;">Estado actual: <strong style="color: #2563eb;">${estadoActual}</strong></p>
                                <p style="margin-bottom: 1rem;">Seleccione el nuevo estado:</p>
                                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                                    ${opcionesEstado}
                                </div>
                            `;

                            // Botón cancelar
                            const cancelBtn = document.createElement('button');
                            cancelBtn.className = 'modal-btn btn-cancel';
                            cancelBtn.innerHTML = '<i class="fas fa-times"></i> Cancelar';
                            cancelBtn.onclick = () => closeModal();

                            modalActions.innerHTML = '';
                            modalActions.appendChild(cancelBtn);

                            modalBackdrop.classList.add('show');
                            modalBackdrop.style.display = 'flex';

                            // Agregar listeners a los botones de cambio de estado
                            setTimeout(() => {
                                document.querySelectorAll('.change-status-btn').forEach(changeBtn => {
                                    changeBtn.addEventListener('click', function () {
                                        const nuevoEstado = this.dataset.estado;

                                        // Confirmación adicional
                                        closeModal();

                                        setTimeout(() => {
                                            showModal(
                                                'Confirmar Cambio',
                                                `¿Está seguro de cambiar el estado de <strong>${estadoActual}</strong> a <strong>${nuevoEstado}</strong>?`,
                                                'warning',
                                                () => {
                                                    // Confirmar cambio
                                                    mostrarLoading(true);
                                                    fetch(`../controladores/lecturas.php?action=update_estado_pago&id_lectura=${idLectura}&estado=${nuevoEstado}`)
                                                        .then(r => r.json())
                                                        .then(data => {
                                                            mostrarLoading(false);
                                                            if (data.success) {
                                                                mostrarAlerta('Éxito', 'Estado de pago actualizado correctamente', 'success');
                                                                // Recargar modal de lecturas
                                                                setTimeout(() => mostrarModalLecturas(id), 1500);
                                                            } else {
                                                                mostrarAlerta('Error', data.message || 'No se pudo actualizar el estado', 'error');
                                                            }
                                                        })
                                                        .catch(() => {
                                                            mostrarLoading(false);
                                                            mostrarAlerta('Error', 'Error de conexión', 'error');
                                                        });
                                                },
                                                () => {
                                                    // Cancelar - no hacer nada
                                                }
                                            );
                                        }, 300);
                                    });
                                });
                            }, 100);
                        });
                    });

                    lecturasModalBackdrop.style.display = 'flex';
                    setTimeout(() => lecturasModalBackdrop.classList.add('show'), 10);
                } else showModal('Error', 'No se pudieron cargar las lecturas', 'error');
            })
            .catch(() => { mostrarLoading(false); showModal('Error', 'Error de conexión', 'error'); });
    }

    // --- Edit Logic & Debt Check ---
    let originalName = '';
    let originalStreet = '';

    function showDebtCheckModal(title, message, onNoDebts, onHasDebts) {
        // Reuse Warning Modal structure but custom buttons
        modalIcon.className = 'modal-icon warning';
        modalIcon.innerHTML = '<i class="fas fa-question-circle"></i>';
        modalTitle.textContent = title;
        modalMessage.innerHTML = message;
        modalActions.innerHTML = '';

        const btnYes = document.createElement('button');
        btnYes.className = 'modal-btn btn-error-modal'; // Red style from CSS
        btnYes.innerHTML = '<i class="fas fa-times-circle"></i> Sí, tiene adeudos';
        btnYes.onclick = () => { closeModal(); if (onHasDebts) onHasDebts(); };

        const btnNo = document.createElement('button');
        btnNo.className = 'modal-btn btn-success-modal'; // Green style
        btnNo.innerHTML = '<i class="fas fa-check-circle"></i> No, continuar';
        btnNo.onclick = () => { closeModal(); if (onNoDebts) onNoDebts(); };

        modalActions.appendChild(btnYes);
        modalActions.appendChild(btnNo);
        modalBackdrop.classList.add('show');
        modalBackdrop.style.display = 'flex';
    }

    function proceedUpdate(id) {
        console.log('Iniciando proceedUpdate para ID:', id);
        mostrarLoading(true);
        const formData = new FormData(editForm);
        formData.append('action', 'update');
        formData.append('id', id);

        // Debug FormData
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }

        fetch('../controladores/beneficiarios.php', { method: 'POST', body: formData })
            .then(r => {
                console.log('Respuesta cruda recibida:', r);
                return r.text().then(text => {
                    console.log('Texto de respuesta:', text);
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                        console.error('Error al parsear JSON:', e);
                        throw new Error('Respuesta del servidor inválida: ' + text.substring(0, 50));
                    }
                });
            })
            .then(data => {
                console.log('Datos procesados:', data);
                mostrarLoading(false);
                if (data.success) {
                    showModal('Éxito', 'Beneficiario actualizado correctamente', 'success');
                    closeEditModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    console.error('Error desde servidor:', data.message);
                    showModal('Error', data.message || 'Error al actualizar', 'error');
                }
            })
            .catch(err => {
                console.error('Error Catch:', err);
                mostrarLoading(false);
                showModal('Error', 'Error: ' + err.message, 'error');
            });
    }

    function mostrarModalEdicion(id) {
        mostrarLoading(true);
        console.log('Abriendo modal edición para ID:', id);
        // Streets and User data
        Promise.all([
            fetch('../controladores/beneficiarios.php?action=get_calles').then(r => r.json()),
            fetch(`../controladores/beneficiarios.php?action=get_usuario&id=${id}`).then(r => r.json())
        ]).then(([streetsData, userData]) => {
            mostrarLoading(false);
            if (streetsData.success) {
                const sel = document.getElementById('editStreetAndNumber');
                sel.innerHTML = '<option value="">Selecciona una calle</option>';
                streetsData.calles.forEach(c => { const opt = document.createElement('option'); opt.value = c; opt.textContent = c; sel.appendChild(opt); });
            }
            if (userData.success) {
                const u = userData.usuario;
                originalName = u.nombre;
                originalStreet = u.calle;
                console.log('Datos cargados:', u);

                document.getElementById('editIdDisplay').textContent = u.id_usuario;
                document.getElementById('editFechaDisplay').textContent = u.fecha_alta;
                document.getElementById('editBeneficiaryName').value = u.nombre;
                document.getElementById('editContractNumber').value = u.no_contrato;
                document.getElementById('editMeterNumber').value = u.no_medidor;
                document.getElementById('editStreetAndNumber').value = u.calle;
                document.getElementById('editStatus').value = u.activo;

                const prevNameContainer = document.getElementById('previousNameContainer');
                const prevNameDisplay = document.getElementById('previousNameDisplay');
                if (u.nombre_anterior && u.nombre_anterior !== u.nombre) {
                    prevNameDisplay.textContent = u.nombre_anterior;
                    prevNameContainer.style.display = 'block';
                } else {
                    prevNameContainer.style.display = 'none';
                }

                editForm.dataset.id = id;
                editModalBackdrop.classList.add('show');
                editModalBackdrop.style.display = 'flex';
            } else {
                showModal('Error', 'No se pudieron cargar los datos', 'error');
            }
        }).catch(() => { mostrarLoading(false); showModal('Error', 'Error de conexión', 'error'); });
    }

    // Edit form validation elements
    const editName = document.getElementById('editBeneficiaryName');
    const editContract = document.getElementById('editContractNumber');
    const editMeter = document.getElementById('editMeterNumber');
    const editStreet = document.getElementById('editStreetAndNumber');

    // Real-time validation for edit form
    if (editName) editName.addEventListener('input', function () { if (this.value.trim().length < 3) mostrarError(this, 'Mínimo 3 caracteres'); else quitarError(this); });
    // Contrato y medidor ya no son obligatorios, solo validar si tienen valor
    if (editContract) editContract.addEventListener('input', function () { quitarError(this); });
    if (editMeter) editMeter.addEventListener('input', function () { quitarError(this); });
    if (editStreet) editStreet.addEventListener('change', function () { if (this.value.trim() === '') mostrarError(this, 'Requerido'); else quitarError(this); });

    if (editForm) {
        editForm.addEventListener('submit', function (e) {
            e.preventDefault();
            console.log('Evento submit disparado');
            const id = editForm.dataset.id;
            console.log('ID del dataset:', id);

            if (!id) {
                console.error('No ID found in dataset');
                showModal('Error', 'Error interno: ID no encontrado', 'error');
                return;
            }

            // Validate fields - solo nombre y calle son obligatorios
            let v = true;
            if (editName.value.trim().length < 3) { mostrarError(editName, 'Inválido'); v = false; }
            if (editStreet.value.trim() === '') { mostrarError(editStreet, 'Requerido'); v = false; }
            if (!v) return;

            const currentName = document.getElementById('editBeneficiaryName').value;
            console.log('Nombre actual:', currentName, 'Original:', originalName);
            const currentStreet = document.getElementById('editStreetAndNumber').value;

            const nameChanged = currentName !== originalName;
            const streetChanged = currentStreet !== originalStreet;

            if (nameChanged || streetChanged) {
                closeEditModal();
                let msg = '';
                if (nameChanged) msg += `Cambio de nombre (<b>${originalName}</b> → <b>${currentName}</b>).<br>`;
                if (streetChanged) msg += `Cambio de calle (<b>${originalStreet}</b> → <b>${currentStreet}</b>).<br>`;
                msg += `<br>¿El usuario tiene adeudos pendientes?`;

                showDebtCheckModal('Verificación de Adeudos', msg,
                    () => { console.log('Confirmado sin adeudos'); proceedUpdate(id); },
                    () => {
                        showModal('Acción Denegada', 'No se pueden realizar estos cambios si el usuario tiene adeudos pendientes.', 'error');
                        setTimeout(() => { editModalBackdrop.classList.add('show'); editModalBackdrop.style.display = 'flex'; }, 4500);
                    }
                );
            } else {
                closeEditModal();
                showModal('¿Confirmar Cambios?', '¿Estás seguro de guardar los cambios?', 'warning', () => {
                    console.log('Confirmado guardar cambios');
                    proceedUpdate(id);
                });
            }
        });
    }

    function confirmarEliminacion(id) {
        showModal('Confirmar Eliminación', '¿Estás seguro de eliminar este beneficiario? Esta acción no se puede deshacer.', 'warning', () => {
            mostrarLoading(true);
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('id', id);
            fetch('../controladores/beneficiarios.php', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    mostrarLoading(false);
                    if (data.success) {
                        showModal('Éxito', 'Beneficiario eliminado correctamente', 'success');
                        const row = document.querySelector(`.card-wrapper[data-id="${id}"]`);
                        if (row) row.remove();
                    } else {
                        showModal('No se puede eliminar', data.message || 'Error', 'error');
                    }
                })
                .catch(() => { mostrarLoading(false); showModal('Error', 'Error de conexión', 'error'); });
        });
    }

    // --- Validation Helpers & Init ---
    function mostrarError(c, m) {
        c.style.borderColor = 'red'; c.style.boxShadow = '0 0 0 3px rgba(244,67,54,0.1)';
        let s = c.parentElement.querySelector('.error-message');
        if (!s) { s = document.createElement('span'); s.className = 'error-message'; s.style.color = 'red'; s.style.fontSize = '0.8rem'; c.parentElement.appendChild(s); }
        s.textContent = m;
    }
    function quitarError(c) {
        c.style.borderColor = '#ddd'; c.style.boxShadow = 'none';
        const s = c.parentElement.querySelector('.error-message'); if (s) s.textContent = '';
        const w = c.parentElement.querySelector('.warning-message'); if (w) w.textContent = '';
    }

    function mostrarAdvertencia(c, m) {
        c.style.borderColor = '#ff9800'; c.style.boxShadow = '0 0 0 3px rgba(255,152,0,0.1)';
        // Quitar mensaje de error si existe
        const e = c.parentElement.querySelector('.error-message');
        if (e) e.textContent = '';

        let s = c.parentElement.querySelector('.warning-message');
        if (!s) {
            s = document.createElement('span');
            s.className = 'warning-message';
            s.style.color = '#ff9800';
            s.style.fontSize = '0.8rem';
            s.style.fontWeight = 'bold';
            c.parentElement.appendChild(s);
        }
        s.textContent = '⚠️ ' + m;
    }

    function inicializarCallesYBarrios() {
        fetch('../controladores/beneficiarios.php?action=get_calles').then(r => r.json()).then(d => {
            if (d.success) {
                const sel = document.getElementById('streetAndNumber');
                d.calles.forEach(c => {
                    const oF = document.createElement('option'); oF.value = c; oF.textContent = c; if (streetFilter) streetFilter.appendChild(oF);
                    if (sel) { const oA = document.createElement('option'); oA.value = c; oA.textContent = c; sel.appendChild(oA); }
                });
            }
        });
        fetch('../controladores/beneficiarios.php?action=get_barrios').then(r => r.json()).then(d => {
            if (d.success) d.barrios.forEach(b => {
                const o = document.createElement('option'); o.value = b; o.textContent = b; if (barrioFilter) barrioFilter.appendChild(o);
            });
        });
    }
    inicializarCallesYBarrios();

    // --- Add Form Logic ---
    const formAdd = document.getElementById('beneficiaryForm');
    const inCon = document.getElementById('contractNumber');
    const inMed = document.getElementById('meterNumber');
    const inNom = document.getElementById('beneficiaryName');
    const inCal = document.getElementById('streetAndNumber');
    const btnCan = document.getElementById('cancelButton'); // Existing cancel button in add form

    if (document.getElementById('registrationDate')) document.getElementById('registrationDate').value = new Date().toISOString().split('T')[0];

    // Real-time validation con verificación de duplicados
    let contratoTimeout, medidorTimeout;

    if (inCon) {
        inCon.addEventListener('input', function () {
            // Limpiar timeout anterior
            clearTimeout(contratoTimeout);

            // Validar longitud (1-4 dígitos)
            const valor = this.value.trim();
            if (valor !== '') {
                if (!/^\d+$/.test(valor)) {
                    mostrarError(this, 'Solo números');
                    return;
                } else if (valor.length > 4) {
                    mostrarError(this, 'Máximo 4 dígitos');
                    return;
                } else {
                    quitarError(this);
                }

                // Verificar duplicados después de 500ms
                contratoTimeout = setTimeout(() => {
                    fetch(`../controladores/beneficiarios.php?action=check_duplicate&tipo=contrato&valor=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.exists) {
                                mostrarAdvertencia(inCon, `Ya registrado para: ${data.beneficiario}`);
                            } else {
                                quitarError(inCon);
                            }
                        })
                        .catch(() => { });
                }, 500);
            } else {
                quitarError(this);
            }
        });
    }

    if (inMed) {
        inMed.addEventListener('input', function () {
            // Limpiar timeout anterior
            clearTimeout(medidorTimeout);

            // Validar longitud (exactamente 8 dígitos)
            const valor = this.value.trim();
            if (valor !== '') {
                if (!/^\d+$/.test(valor)) {
                    mostrarError(this, 'Solo números');
                    return;
                } else if (valor.length < 8) {
                    mostrarError(this, 'Debe tener 8 dígitos');
                    return;
                } else if (valor.length > 8) {
                    mostrarError(this, 'Máximo 8 dígitos');
                    return;
                } else {
                    quitarError(this);
                }

                // Verificar duplicados después de 500ms
                medidorTimeout = setTimeout(() => {
                    fetch(`../controladores/beneficiarios.php?action=check_duplicate&tipo=medidor&valor=${encodeURIComponent(this.value)}`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.success && data.exists) {
                                mostrarAdvertencia(inMed, `Ya registrado para: ${data.beneficiario}`);
                            } else {
                                quitarError(inMed);
                            }
                        })
                        .catch(() => { });
                }, 500);
            } else {
                quitarError(this);
            }
        });
    }

    if (inNom) inNom.addEventListener('input', function () { if (this.value.length < 3) mostrarError(this, 'Mínimo 3 caracteres'); else quitarError(this); });
    if (inCal) inCal.addEventListener('input', function () { if (this.value.trim() === '') mostrarError(this, 'Requerido'); else quitarError(this); });

    if (formAdd) {
        formAdd.addEventListener('submit', function (e) {
            e.preventDefault();
            let v = true;

            // Solo validar campos obligatorios: nombre y calle
            if (inNom.value.trim().length < 3) { mostrarError(inNom, 'Inválido'); v = false; }
            if (inCal.value.trim() === '') { mostrarError(inCal, 'Requerido'); v = false; }

            if (!v) return;
            if (!navigator.onLine) { showModal('Sin Conexión', 'Verifica internet', 'error'); return; }

            mostrarLoading(true);
            const fd = new FormData(this);
            fetch('../controladores/beneficiarios.php', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(d => {
                    mostrarLoading(false);
                    if (d.success) {
                        let mensaje = 'Beneficiario guardado correctamente';
                        if (d.warnings && d.warnings.length > 0) {
                            mensaje += '\n\nAdvertencias:\n' + d.warnings.join('\n');
                        }
                        showModal('Éxito', mensaje.replace(/\n/g, '<br>'), 'success');
                        formAdd.reset();
                        [inCon, inMed, inNom, inCal].forEach(quitarError);
                        // Mantener al usuario en el formulario de agregar
                        // NO cambiar de pestaña ni recargar
                    } else showModal('Error', d.message || 'Error', 'error');
                })
                .catch(e => { mostrarLoading(false); showModal('Error', e.message, 'error'); });
        });
        if (btnCan) btnCan.addEventListener('click', () => { formAdd.reset();[inCon, inMed, inNom, inCal].forEach(quitarError); });
    }
});