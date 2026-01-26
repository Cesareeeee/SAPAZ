document.addEventListener('DOMContentLoaded', function () {
    // --- Tabs Logic ---
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.dataset.target;

            // Toggle Buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Toggle Content
            tabContents.forEach(c => {
                c.classList.remove('active');
                if (c.id === targetId) c.classList.add('active');
            });

            // If switching to Reports, load filters if empty
            if (targetId === 'tab-reports' && document.getElementById('filterReportBarrio').children.length <= 1) {
                loadReportFilters();
                loadBeneficiaries();
            }
        });
    });

    // --- Reports Logic ---
    let currentPage = 1;
    let isLoading = false;
    let hasMore = true;

    const reportSearch = document.getElementById('searchReportInput');
    const btnClearReportSearch = document.getElementById('btnClearReportSearch');
    const filterBarrio = document.getElementById('filterReportBarrio');
    const filterCalle = document.getElementById('filterReportCalle');
    const filterStatus = document.getElementById('filterReportStatus');
    const resultsGrid = document.getElementById('reportResultsGrid');

    // Pagination Elements
    const btnPrevPage = document.getElementById('btnPrevReportPage');
    const btnNextPage = document.getElementById('btnNextReportPage');
    const pageIndicator = document.getElementById('reportPageIndicator'); // Needs to be added to HTML

    // Load Filters
    function loadReportFilters() {
        console.log("Loading filters...");
        fetch('../controladores/reportes_facturacion.php?action=get_filters')
            .then(r => r.json())
            .then(data => {
                if (data.success && data.filters) {
                    populateSelect(filterBarrio, data.filters.barrios);
                    populateSelect(filterCalle, data.filters.calles);
                }
            });
    }

    function populateSelect(select, items) {
        items.forEach(item => {
            const opt = document.createElement('option');
            opt.value = item;
            opt.textContent = item;
            select.appendChild(opt);
        });
    }

    // Load Beneficiaries
    function loadBeneficiaries(reset = false) {
        if (isLoading) return;

        if (reset) {
            currentPage = 1;
            hasMore = true;
        }

        isLoading = true;
        resultsGrid.innerHTML = `
            <div style="grid-column: 1/-1; text-align: center; padding: 3rem;">
                <i class="fas fa-spinner fa-spin fa-2x" style="color: #2563eb;"></i>
            </div>
        `;

        const query = new URLSearchParams({
            action: 'search_beneficiaries',
            q: reportSearch.value.trim(),
            barrio: filterBarrio.value,
            calle: filterCalle.value,
            status: filterStatus.value,
            page: currentPage
        });

        // Toggle Clear Button
        if (reportSearch.value.trim().length > 0) {
            btnClearReportSearch.style.display = 'block';
        } else {
            btnClearReportSearch.style.display = 'none';
        }

        fetch(`../controladores/reportes_facturacion.php?${query.toString()}`)
            .then(r => r.json())
            .then(data => {
                isLoading = false;
                if (data.success) {
                    resultsGrid.innerHTML = ''; // Clear loader
                    if (data.users.length === 0 && currentPage === 1) {
                        resultsGrid.innerHTML = `<div style="grid-column: 1/-1; text-align:center; padding: 3rem; color: #64748b;">
                            <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 1rem; color: #cbd5e1;"></i>
                            <p>No se encontraron resultados</p>
                        </div>`;
                        pageIndicator.textContent = `Página ${currentPage}`;
                        btnNextPage.disabled = true;
                        btnPrevPage.disabled = true;
                        return;
                    }

                    if (data.users.length < 20) {
                        hasMore = false;
                        btnNextPage.disabled = true;
                    } else {
                        hasMore = true;
                        btnNextPage.disabled = false;
                    }

                    btnPrevPage.disabled = (currentPage === 1);
                    pageIndicator.textContent = `Página ${currentPage}`;

                    renderBeneficiaries(data.users);
                }
            })
            .catch(e => {
                isLoading = false;
                resultsGrid.innerHTML = `<p style="color:red; text-align:center;">Error al cargar datos</p>`;
                console.error(e);
            });
    }

    function renderBeneficiaries(users) {
        users.forEach(u => {
            const card = document.createElement('div');
            card.className = 'beneficiary-card';

            // Add click listener to the entire card
            card.addEventListener('click', (e) => {
                // Ignore if clicked on a button inside (though currently only buttons exist)
                // Actually, let's just make the whole card do it, buttons can propagate naturally or be prevented?
                // User logic: "CUANDO SELECCIONEN LA CAD DE UN USUARIO QUE SE HABARA SU HOSTIRAL"
                // Ideally, clicking the "Historial" button does the same thing.
                verHistorialUsuario(u.id_usuario, u.nombre);
            });

            const statusClass = u.estatus === 'Al Corriente' ? 'paid' : 'debt';
            const statusIcon = u.estatus === 'Al Corriente' ? '<i class="fas fa-check-circle"></i>' : '<i class="fas fa-exclamation-circle"></i>';
            const statusLabel = u.estatus === 'Al Corriente' ? 'Al Corriente' : 'Con Adeudo';

            // Desktop "Row" Layout built with Flex/Grid in CSS
            // We structure it logically for the CSS Grid to pick up
            card.innerHTML = `
                <!-- Column 1: User Info -->
                <div class="card-user-info">
                    <span class="card-user-name">${u.nombre}</span>
                    <span class="card-contract">Contrato: ${u.no_contrato}</span>
                </div>
                
                <!-- Column 2: Meter -->
                <div class="card-meter-badge">
                    <i class="fas fa-tachometer-alt"></i> ${u.no_medidor || 'S/N'}
                </div>

                <!-- Column 3: Address -->
                <div class="card-address-container">
                    <span class="card-address-main"><i class="fas fa-map-marker-alt" style="color:#ef4444; margin-right:0.3rem;"></i> ${u.calle || 'Sin Calle'}</span>
                    <span class="card-address-sub">${u.barrio || ''}</span>
                </div>

                <!-- Column 4: Readings Status -->
                <div class="card-readings-info">
                    <span class="reading-count-badge" style="color: ${u.lecturas_pendientes > 0 ? '#dc2626' : '#64748b'}">
                        ${u.lecturas_pendientes} Pendientes
                    </span>
                </div>

                <!-- Column 5: Status -->
                 <span class="status-badge ${statusClass}">
                    ${statusIcon} ${statusLabel}
                </span>

                <!-- Column 6: Actions -->
                <div class="card-actions">
                    <button class="btn-history-card">
                        <i class="fas fa-history"></i> Historial
                    </button>
                </div>
            `;
            resultsGrid.appendChild(card);
        });
    }

    // Event Listeners
    reportSearch.addEventListener('input', () => debounce(() => loadBeneficiaries(true), 500)());

    btnClearReportSearch.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent card click? No, this is outside.
        reportSearch.value = '';
        reportSearch.focus();
        loadBeneficiaries(true);
    });

    filterBarrio.addEventListener('change', () => loadBeneficiaries(true));
    filterCalle.addEventListener('change', () => loadBeneficiaries(true));
    // filterStatus is now hidden input

    // Pagination Listeners
    btnPrevPage.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            loadBeneficiaries(false); // False means don't reset page to 1
        }
    });

    btnNextPage.addEventListener('click', () => {
        if (hasMore) {
            currentPage++;
            loadBeneficiaries(false);
        }
    });

    // Helper: Debounce
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // --- History Modal Logic ---
    window.verHistorialUsuario = function (id, nombre) {
        const modal = document.getElementById('historyModal');
        const content = document.getElementById('historyModalContent');
        const title = document.getElementById('historyModalTitle');

        title.innerHTML = `<i class="fas fa-history"></i> Historial de: <span style="color:#1e40af;">${nombre}</span>`;
        content.innerHTML = '<div style="text-align:center; padding:2rem;"><i class="fas fa-spinner fa-spin fa-2x" style="color:#3b82f6;"></i></div>';
        modal.style.display = 'flex';

        fetch(`../controladores/reportes_facturacion.php?action=get_user_history&id_usuario=${id}`)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    renderHistory(id, data.history, content);
                } else {
                    content.innerHTML = '<p class="text-error" style="text-align:center;">Error al cargar historial</p>';
                }
            });
    };

    function renderHistory(userId, history, container) {
        if (history.length === 0) {
            container.innerHTML = `
                <div style="text-align:center; padding:3rem; color:#94a3b8;">
                    <i class="fas fa-calendar-times" style="font-size:3rem; margin-bottom:1rem;"></i>
                    <p>No hay registros de lecturas o facturas recientes.</p>
                </div>`;
            return;
        }

        let html = '<div class="history-list-container">';
        history.forEach(h => {
            const date = new Date(h.fecha_lectura).toLocaleDateString('es-MX', { year: 'numeric', month: 'long', day: 'numeric' });
            // Logic for status
            let statusBadge = '';
            let actionBtn = '';

            if (h.id_factura && h.estado_factura === 'Pagado') {
                statusBadge = '<span class="status-tag-modal" style="background:#d1fae5; color:#059669;">PAGADO</span>';
                actionBtn = `<div style="font-size:0.8rem; color:#059669;"><i class="fas fa-check"></i> Folio #${h.id_factura}</div>`;
            } else if (h.id_factura) {
                // Factura existe pero no pagada (Pendiente) -> PAGO DIRECTO
                statusBadge = '<span class="status-tag-modal" style="background:#ffedd5; color:#c2410c;">PENDIENTE</span>';
                // Pass full object or needed params
                const montoTotal = h.monto_total || 0;
                actionBtn = `<button class="btn-pay-modal" onclick="procesarPagoDirectoDesdeHistorial(${h.id_factura}, '${montoTotal}', '${h.consumo_m3}', '${h.fecha_lectura}', '${userId}')">
                    <i class="fas fa-credit-card"></i> Pagar
                 </button>`;
            } else {
                // No hay factura generada aun (Solo lectura) -> IR A GENERADOR
                statusBadge = '<span class="status-tag-modal" style="background:#f1f5f9; color:#64748b;">LECTURA REGISTRADA</span>';
                actionBtn = `<button class="btn-pay-modal" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);" onclick="irAGenerarFactura(${userId}, ${h.id_lectura})">
                    <i class="fas fa-file-invoice"></i> Generar Factura
                 </button>`;
            }

            const monto = h.monto_total ? `$${parseFloat(h.monto_total).toFixed(2)}` : 'Calculando...';

            html += `
                <div class="history-card-item">
                    <div style="display:flex; gap:1rem; align-items:center;">
                        <div style="background:#eff6ff; padding:0.8rem; border-radius:50%; color:#1e40af;">
                            <i class="fas fa-tint"></i>
                        </div>
                        <div>
                            <div class="history-date">${date}</div>
                            <div class="history-meta">Consumo: <strong>${h.consumo_m3} m³</strong></div>
                            <div class="history-meta" style="margin-top:0.2rem;">${statusBadge}</div>
                        </div>
                    </div>
                    <div style="text-align:right; display:flex; flex-direction:column; gap:0.5rem; align-items:flex-end;">
                        <div class="history-amount">${monto}</div>
                        ${actionBtn}
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    // --- Window Actions ---

    // Case A: No Invoice -> Go to Generator Tab
    window.irAGenerarFactura = function (userId, readingId) {
        // 1. Close Modal
        document.getElementById('historyModal').style.display = 'none';

        // 2. Switch to Generator Tab
        document.querySelector('[data-target="tab-generator"]').click();

        // 3. Initiate Logic (Facturacion.js handles selection)
        const event = new CustomEvent('initiatePayment', {
            detail: {
                userId: userId,
                readingId: readingId,
                autoGenerate: false // Let user review before saving? Or true? User said "SE LLENE AUTOMATICAMENTE". pre-selection does this.
            }
        });
        document.dispatchEvent(event);
    };

    // Case B: Has Invoice -> Pay Here
    window.procesarPagoDirectoDesdeHistorial = function (idFactura, monto, consumo, periodo, userId) {
        const confirmMsg = `
            <div style="text-align:left; font-size:0.9rem;">
                <p><strong>Detalle del Cobro:</strong></p>
                <ul style="list-style:none; padding:0; margin:0.5rem 0;">
                    <li>Periodo: <strong>${new Date(periodo).toLocaleDateString()}</strong></li>
                    <li>Consumo: <strong>${consumo} m³</strong></li>
                    <li style="margin-top:0.5rem; font-size:1.1rem; color:#1e40af;">Total a Pagar: <strong>$${parseFloat(monto).toFixed(2)}</strong></li>
                </ul>
            </div>
        `;

        // Reuse global confirmation if available
        if (typeof mostrarConfirmacion === 'function') {
            mostrarConfirmacion(
                '¿Desea realizar el cobro?',
                confirmMsg,
                function () {
                    realizarPagoBackend(idFactura, userId);
                }
            );
        } else {
            // Fallback
            if (confirm("¿Desea realizar el cobro de $" + monto + "?")) {
                realizarPagoBackend(idFactura, userId);
            }
        }
    };

    function realizarPagoBackend(idFactura, userId) {
        // Show local interaction loader? 
        const btn = document.activeElement;
        if (btn) btn.disabled = true;

        fetch('../controladores/facturacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=pay_invoice&id_factura=${idFactura}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Close History Modal? Or Refresh it?
                    // User said "YA DESPUES EL TICKET".
                    // We should close history modal or keep it open?
                    // Probably close history modal to show ticket modal clearly.
                    document.getElementById('historyModal').style.display = 'none';

                    // Show Notification
                    if (typeof mostrarNotificacion === 'function') mostrarNotificacion('Pago Exitoso', 'La factura ha sido pagada.', 'success');

                    // Generate Ticket Object for display
                    // We need to construct a mock invoice object for the ticket viewer in facturacion.js
                    // Or fetch it. Ideally we pass the data needed.
                    // Reusing `imprimirTicket` from facturacion.js requires the GLOBAL function to be exposed.
                    // We will assume `verTicketDePago` (which we need to make sure exists or create)

                    // Let's create a custom event to trigger ticket view in facturacion.js which has the ticket logic
                    const event = new CustomEvent('paymentDeletedOrAdded', { detail: { userId: userId } }); // Refresh lists
                    document.dispatchEvent(event);

                    // Need to show ticket. We can fetch invoice details or reuse what we have.
                    // Best to fetch "get_invoice_details" if possible, or construct it.
                    // Let's try to trigger the existing ticket view logic if we can access it.
                    // `verTicket` is usually attached to the invoices list in tab 1. 
                    // We can construct a minimal object and call the logic if exposed.

                    // Hack: We will look for the global function `mostrarModalTicket` which facturacion.js likely defines
                    // or we implement a listener. 
                    // Actually, let's just reload the history to reflect change IF we didn't close it.
                    // But we closed it.

                    // To show ticket, we dispatch an event that facturacion.js listens to?
                    // Or simply call a global function. 
                    if (typeof window.generarTicketExterno === 'function') {
                        window.generarTicketExterno(idFactura);
                    } else {
                        // Start a fetch to get details then create ticket HTML manually here if needed, 
                        // but better to add `generarTicketExterno` in facturacion.js
                        // Dispatch event
                        const ticketEvent = new CustomEvent('showTicketRequest', { detail: { id_factura: idFactura } });
                        document.dispatchEvent(ticketEvent);
                    }

                } else {
                    if (typeof mostrarNotificacion === 'function') mostrarNotificacion('Error', data.message, 'error');
                    if (btn) btn.disabled = false;
                }
            })
            .catch(e => {
                console.error(e);
                if (typeof mostrarNotificacion === 'function') mostrarNotificacion('Error', 'Error de conexión', 'error');
                if (btn) btn.disabled = false;
            });
    }

    // Close Modal
    document.getElementById('btnCloseHistory').addEventListener('click', () => {
        document.getElementById('historyModal').style.display = 'none';
    });

    // Status Filter Logic using Buttons
    const statusButtons = document.querySelectorAll('.btn-filter-status');
    statusButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            statusButtons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Sync with hidden select
            filterStatus.value = btn.dataset.value;
            loadBeneficiaries(true);
        });
    });

    // --- Search Loader Logic ---
    reportSearch.addEventListener('input', () => {
        const loader = document.getElementById('searchReportLoader');
        if (reportSearch.value.trim().length > 0 && loader) loader.style.display = 'block';
        else if (loader) loader.style.display = 'none';

        debounce(() => {
            loadBeneficiaries(true);
            if (loader) loader.style.display = 'none';
        }, 500)();
    });

    // Toggle Location Filters
    const btnToggleLocation = document.getElementById('btnToggleLocationFilter');
    const locationContainer = document.getElementById('locationFiltersContainer');

    if (btnToggleLocation && locationContainer) {
        btnToggleLocation.addEventListener('click', () => {
            const isHidden = locationContainer.style.display === 'none';
            locationContainer.style.display = isHidden ? 'flex' : 'none';
            // Optional: Toggle icon direction
            const icon = btnToggleLocation.querySelector('.fa-chevron-down, .fa-chevron-up');
            if (icon) {
                icon.className = isHidden ? 'fas fa-chevron-up' : 'fas fa-chevron-down';
            }
        });
    }

});
