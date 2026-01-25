document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('userSearchInput');
    const resultsContainer = document.getElementById('userSearchResults');
    const btnClearSearch = document.getElementById('btnClearSearch');
    const searchLoader = document.getElementById('searchLoader');
    let searchTimeout;

    // Cargar Tarifa Inicial desde BD
    fetch('../controladores/facturacion.php?action=get_rate')
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                currentRate = parseFloat(data.rate);
                previousRate = currentRate;
                document.getElementById('rateDisplay').textContent = `$${currentRate.toFixed(2)}`;
            }
        })
        .catch(err => console.error('Error cargando tarifa:', err));

    // Mostrar/ocultar botón limpiar
    searchInput.addEventListener('input', function () {
        if (this.value.trim().length > 0) {
            btnClearSearch.style.display = 'flex';
        } else {
            btnClearSearch.style.display = 'none';
        }
    });

    // Limpiar búsqueda
    btnClearSearch.addEventListener('click', function () {
        searchInput.value = '';
        btnClearSearch.style.display = 'none';
        resultsContainer.style.display = 'none';
        resultsContainer.innerHTML = '';
        limpiarFormulario();

        // CORRECCIÓN: Volver a cargar historial global (recientes)
        loadInvoices(null);
    });

    // Search Users
    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        const query = this.value.trim();

        if (query.length < 2) {
            resultsContainer.style.display = 'none';
            searchLoader.style.display = 'none';
            return;
        }

        // Mostrar loader
        searchLoader.style.display = 'block';
        resultsContainer.style.display = 'none';

        searchTimeout = setTimeout(() => {
            fetch(`../controladores/facturacion.php?action=search_users&q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    searchLoader.style.display = 'none';
                    resultsContainer.innerHTML = '';

                    if (data.success && data.users.length > 0) {
                        data.users.forEach(user => {
                            const div = document.createElement('div');
                            div.className = 'user-result-item';
                            // CORRECCIÓN: Mostrar mes de lectura si existe
                            let mesInfo = '';
                            if (user.mes_texto) {
                                mesInfo = `<span style="color: #ef4444; font-weight: bold; font-size: 0.8rem; margin-left: 0.5rem;">• ${user.mes_texto}</span>`;
                            }

                            div.innerHTML = `
                                <div class="name">${user.nombre} ${mesInfo}</div>
                                <div class="meta">Contrato: ${user.no_contrato} | Medidor: ${user.no_medidor}</div>
                            `;
                            div.onclick = () => selectUser(user);
                            resultsContainer.appendChild(div);
                        });
                        resultsContainer.classList.remove('empty');
                        resultsContainer.style.display = 'block';
                    } else {
                        // Mostrar mensaje de sin resultados
                        resultsContainer.innerHTML = `
                            <div style="text-align: center; padding: 2rem;">
                                <i class="fas fa-search" style="font-size: 2rem; color: #cbd5e1; margin-bottom: 0.5rem;"></i>
                                <p style="margin: 0; color: var(--text-secondary); font-size: 0.9rem;">No se encontraron usuarios</p>
                            </div>
                        `;
                        resultsContainer.classList.add('empty');
                        resultsContainer.style.display = 'block';
                    }
                })
                .catch(err => {
                    searchLoader.style.display = 'none';
                    console.error('Error en búsqueda:', err);
                });
        }, 400);
    });

    // Hide results on click outside
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
            resultsContainer.style.display = 'none';
        }
    });

    // Select User
    let selectedUser = null;
    let currentReading = null;
    let pendingReadings = [];

    // Gestión de edición de tarifa
    const btnEditRate = document.getElementById('btnEditRate');
    const btnSaveRate = document.getElementById('btnSaveRate');
    const btnCancelRate = document.getElementById('btnCancelRate');
    const rateDisplaySection = document.getElementById('rateDisplaySection');
    const rateEditSection = document.getElementById('rateEditSection');
    const rateInput = document.getElementById('ratePerM3');
    const rateDisplay = document.getElementById('rateDisplay');

    let currentRate = 10.00;
    let previousRate = 10.00;

    // Mostrar sección de edición
    btnEditRate.addEventListener('click', function () {
        rateDisplaySection.style.display = 'none';
        rateEditSection.style.display = 'block';
        rateInput.value = currentRate.toFixed(2);
        rateInput.focus();
    });

    // Cancelar edición
    btnCancelRate.addEventListener('click', function () {
        rateEditSection.style.display = 'none';
        rateDisplaySection.style.display = 'block';
        rateInput.value = currentRate.toFixed(2);
    });

    // Guardar nueva tarifa
    btnSaveRate.addEventListener('click', function () {
        const newRate = parseFloat(rateInput.value);

        if (isNaN(newRate) || newRate <= 0) {
            mostrarNotificacion(
                'Error de Validación',
                'Por favor ingresa una tarifa válida mayor a $0.00',
                'error'
            );
            return;
        }

        // Confirmar cambio
        mostrarConfirmacion(
            '¿Confirmar Cambio de Tarifa?',
            `¿Estás seguro de cambiar la tarifa de <strong>$${currentRate.toFixed(2)}</strong> a <strong>$${newRate.toFixed(2)}</strong> por m³?<br><br>Este cambio afectará el cálculo de todas las nuevas facturas.`,
            function () {
                // Aceptar: Guardar en BD
                fetch('../controladores/facturacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update_rate&rate=${newRate}`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            previousRate = currentRate;
                            currentRate = newRate;
                            rateDisplay.textContent = `$${currentRate.toFixed(2)}`;
                            rateEditSection.style.display = 'none';
                            rateDisplaySection.style.display = 'block';

                            mostrarNotificacion(
                                'Tarifa Actualizada',
                                `La nueva tarifa de <strong>$${currentRate.toFixed(2)}</strong> por m³ ha sido guardada exitosamente.`,
                                'success'
                            );

                            // Recalcular si hay un usuario seleccionado
                            if (selectedUser && currentReading) {
                                const total = calculateTotal(currentReading.consumo_m3);
                                document.getElementById('totalAmount').textContent = `$${total.toFixed(2)}`;
                            }
                        } else {
                            mostrarNotificacion('Error', 'No se pudo guardar la tarifa en la base de datos', 'error');
                        }
                    });
            },
            function () {
                // Cancelar
                rateInput.value = currentRate.toFixed(2);
            }
        );
    });

    function limpiarFormulario() {
        selectedUser = null;
        currentReading = null;
        pendingReadings = [];
        document.getElementById('clientName').textContent = '-';
        document.getElementById('clientContract').textContent = '-';
        document.getElementById('pendingReadingsSection').style.display = 'none';
        document.getElementById('selectedReadingSection').style.display = 'none';
        document.getElementById('btnGenerate').disabled = true;
    }

    function selectUser(user) {
        selectedUser = user;
        searchInput.value = user.nombre;
        resultsContainer.style.display = 'none';

        // Fill Info
        document.getElementById('clientName').textContent = user.nombre;
        document.getElementById('clientContract').textContent = user.no_contrato;

        // Fetch Pending Readings (TODAS)
        fetch(`../controladores/facturacion.php?action=get_pending_readings&id_usuario=${user.id_usuario}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.readings && data.readings.length > 0) {
                    pendingReadings = data.readings;

                    if (pendingReadings.length === 1) {
                        // Solo una lectura, seleccionarla automáticamente
                        selectReading(pendingReadings[0]);
                    } else {
                        // Múltiples lecturas, mostrar lista para seleccionar
                        mostrarLecturasPendientes(pendingReadings);
                        mostrarNotificacion(
                            'Lecturas Pendientes',
                            `Este usuario tiene <strong>${pendingReadings.length} lecturas pendientes</strong> de pago. Por favor selecciona una para facturar.`,
                            'info'
                        );
                    }
                } else {
                    currentReading = null;
                    pendingReadings = [];
                    document.getElementById('pendingReadingsSection').style.display = 'none';
                    document.getElementById('selectedReadingSection').style.display = 'none';
                    document.getElementById('btnGenerate').disabled = true;
                    mostrarNotificacion('Info', 'No hay lecturas pendientes para este usuario', 'info');
                }
            });

        loadInvoices(user.id_usuario);
    }

    function mostrarLecturasPendientes(readings) {
        const container = document.getElementById('pendingReadingsList');
        container.innerHTML = '';

        readings.forEach((reading, index) => {
            const fecha = new Date(reading.fecha_lectura);
            const meses = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            const mes = meses[fecha.getMonth()];
            const año = fecha.getFullYear();

            // Determinar clases según consumo
            let consumoClass = '';
            let lecturaClass = '';
            if (parseFloat(reading.consumo_m3) > 30) {
                consumoClass = 'high-consumption';
            }
            if (parseFloat(reading.lectura_actual) < parseFloat(reading.lectura_anterior)) {
                lecturaClass = 'negative';
            }

            const item = document.createElement('div');
            item.className = 'pending-reading-item';
            item.dataset.index = index;

            let observacionesHTML = '';
            if (reading.observaciones && reading.observaciones.trim() !== '') {
                observacionesHTML = `
                    <div class="reading-observations">
                        <i class="fas fa-info-circle"></i> ${reading.observaciones}
                    </div>
                `;
            }

            item.innerHTML = `
                <div class="reading-month">${mes} ${año}</div>
                <div class="reading-details">
                    <div class="reading-detail">
                        <span class="reading-detail-label">Fecha</span>
                        <span class="reading-detail-value">${reading.fecha_lectura}</span>
                    </div>
                    <div class="reading-detail">
                        <span class="reading-detail-label">Consumo</span>
                        <span class="reading-detail-value ${consumoClass}">${reading.consumo_m3} m³</span>
                    </div>
                    <div class="reading-detail">
                        <span class="reading-detail-label">Lectura Actual</span>
                        <span class="reading-detail-value ${lecturaClass}">${reading.lectura_actual}</span>
                    </div>
                    <div class="reading-detail">
                        <span class="reading-detail-label">Lectura Anterior</span>
                        <span class="reading-detail-value">${reading.lectura_anterior}</span>
                    </div>
                </div>
                ${observacionesHTML}
            `;

            item.addEventListener('click', function () {
                // Remover selección anterior
                document.querySelectorAll('.pending-reading-item').forEach(el => {
                    el.classList.remove('selected');
                });
                // Seleccionar este
                this.classList.add('selected');
                selectReading(reading);
            });

            container.appendChild(item);
        });

        document.getElementById('pendingReadingsSection').style.display = 'block';
        document.getElementById('selectedReadingSection').style.display = 'none';
    }

    function selectReading(reading) {
        currentReading = reading;

        // Mostrar información de la lectura seleccionada
        document.getElementById('readingPeriod').textContent = reading.fecha_lectura;
        document.getElementById('consumption').textContent = `${reading.consumo_m3} m³`;
        document.getElementById('currentReading').textContent = reading.lectura_actual;

        // Mostrar observaciones si existen
        const obsSection = document.getElementById('observationsSection');
        const obsValue = document.getElementById('observations');
        if (reading.observaciones && reading.observaciones.trim() !== '') {
            obsValue.textContent = reading.observaciones;
            obsSection.style.display = 'block';
        } else {
            obsSection.style.display = 'none';
        }

        // Calcular total
        const total = calculateTotal(reading.consumo_m3);
        document.getElementById('totalAmount').textContent = `$${total.toFixed(2)}`;
        document.getElementById('btnGenerate').disabled = false;

        // Mostrar sección de lectura seleccionada
        document.getElementById('selectedReadingSection').style.display = 'block';

        // Verificar alertas
        const consumo = parseFloat(reading.consumo_m3);
        const lecturaActual = parseFloat(reading.lectura_actual);
        const lecturaAnterior = parseFloat(reading.lectura_anterior);

        // Alerta de consumo alto
        if (consumo > 30) {
            mostrarAlertaCritica(
                '⚠️ ALTO CONSUMO DETECTADO',
                `Esta lectura registra un consumo de <strong>${consumo} m³</strong>.<br><br>Supera el límite normal de 30 m³. Verifica que la lectura sea correcta.`
            );
        }

        // Alerta de medidor retrocedido
        if (lecturaActual < lecturaAnterior) {
            mostrarAlertaCritica(
                '⚠️ MEDIDOR RETROCEDIDO',
                `La lectura actual (<strong>${lecturaActual}</strong>) es menor que la anterior (<strong>${lecturaAnterior}</strong>).<br><br>Esto puede indicar un error de captura o un reinicio del medidor.`
            );
        }
    }

    function calculateTotal(m3) {
        const base = 50; // Base rate
        const rate = currentRate || 10.00;
        return base + (m3 * rate);
    }

    // Generate Invoice
    document.getElementById('btnGenerate').addEventListener('click', function () {
        if (!selectedUser || !currentReading) return;

        const total = parseFloat(document.getElementById('totalAmount').textContent.replace('$', ''));
        const consumo = parseFloat(currentReading.consumo_m3);

        fetch('../controladores/facturacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=create_invoice&id_usuario=${selectedUser.id_usuario}&id_lectura=${currentReading.id_lectura}&monto=${total}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const facturaId = data.id;
                    mostrarNotificacion('Éxito', 'Factura generada correctamente', 'success');
                    loadInvoices(selectedUser ? selectedUser.id_usuario : null);

                    // Preguntar si desea pagar inmediatamente
                    setTimeout(() => {
                        mostrarConfirmacionPago(facturaId, {
                            nombre: selectedUser.nombre,
                            consumo: consumo,
                            tarifa: currentRate,
                            total: total
                        });
                    }, 1500);

                    // Reset pending reading view
                    selectUser(selectedUser); // Reload to see if there are more
                } else {
                    mostrarNotificacion('Error', data.message || 'Error al generar factura', 'error');
                }
            });
    });

    // Variables de Paginación y Datos
    let allInvoices = [];
    let filteredInvoices = [];
    let currentPage = 1;
    const itemsPerPage = 10;

    // Load Invoices (Fetch Data Only)
    function loadInvoices(userId) {
        const list = document.getElementById('invoicesList');
        const loader = `<div style="text-align: center; color: #9ca3af; padding: 2rem;"><i class="fas fa-spinner fa-spin fa-2x"></i></div>`;
        list.innerHTML = loader;

        let url = userId ? `../controladores/facturacion.php?action=get_invoices&id_usuario=${userId}` : `../controladores/facturacion.php?action=get_invoices`;

        fetch(url)
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    allInvoices = data.invoices;
                    applyFilters(); // Esto llamará a renderInvoices
                } else {
                    allInvoices = [];
                    renderInvoices();
                }
            })
            .catch(err => {
                console.error("Error cargando facturas:", err);
                list.innerHTML = '<p style="text-align:center; color:#ef4444; padding: 1rem;">Error al cargar datos</p>';
            });
    }

    // Aplicar filtros de mes y año
    function applyFilters() {
        const filterMonthElem = document.getElementById('filterMonth');
        const filterYearElem = document.getElementById('filterYear');

        if (!filterMonthElem || !filterYearElem) return;

        const filterMonth = filterMonthElem.value;
        const filterYear = filterYearElem.value;

        filteredInvoices = allInvoices.filter(inv => {
            const d = new Date(inv.fecha_emision);
            const matchMonth = filterMonth ? (d.getMonth() + 1) == filterMonth : true;
            const matchYear = filterYear ? d.getFullYear() == filterYear : true;
            return matchMonth && matchYear;
        });

        currentPage = 1; // Reset a primera página al filtrar
        renderInvoices();
        renderPagination();
    }

    // Renderizar lista de facturas (Paginada)
    function renderInvoices() {
        const list = document.getElementById('invoicesList');
        if (!list) return;

        list.innerHTML = '';

        if (filteredInvoices.length === 0) {
            list.innerHTML = '<p style="text-align:center; color:var(--text-secondary); padding: 1rem;">No hay facturas que coincidan con los filtros.</p>';
            const paginationControls = document.getElementById('paginationControls');
            if (paginationControls) paginationControls.style.display = 'none';
            return;
        }

        // Calcular índices
        const startIndex = (currentPage - 1) * itemsPerPage;
        const endIndex = startIndex + itemsPerPage;
        const pageItems = filteredInvoices.slice(startIndex, endIndex);

        pageItems.forEach(inv => {
            const fechaEmision = new Date(inv.fecha_emision);
            const fecha = fechaEmision.toLocaleDateString('es-MX');
            const hora = fechaEmision.toLocaleTimeString('es-MX', { hour: '2-digit', minute: '2-digit' });

            const div = document.createElement('div');
            div.className = 'invoice-item';
            div.innerHTML = `
                <div class="invoice-info">
                    <div class="invoice-header-info">
                        <h4>Factura #${inv.id_factura}</h4>
                        <div class="invoice-user-info">
                            <span class="invoice-user-name"><i class="fas fa-user"></i> ${inv.nombre || 'N/A'}</span>
                            <span class="invoice-user-meter"><i class="fas fa-tachometer-alt"></i> ${inv.no_medidor || 'N/A'}</span>
                        </div>
                    </div>
                    <p class="invoice-date"><i class="far fa-calendar-alt"></i> ${fecha} <i class="far fa-clock"></i> ${hora}</p>
                </div>
                <div class="invoice-meta">
                    <span class="invoice-amount">$${parseFloat(inv.monto_total).toFixed(2)}</span>
                    <span class="invoice-status status-${inv.estado === 'Pagado' ? 'paid' : 'pending'}">${inv.estado}</span>
                    <div class="action-buttons">
                        <button class="btn-icon btn-print" onclick="viewTicket(${inv.id_factura})" title="Ver Ticket"><i class="fas fa-receipt"></i></button>
                        ${inv.estado !== 'Pagado'
                    ? `<button class="btn-icon btn-pay" onclick="iniciarProcesoPago(${inv.id_factura}, '${inv.nombre}', ${inv.monto_total})" title="Pagar"><i class="fas fa-money-bill-wave"></i></button>`
                    : `<button class="btn-icon btn-edit-status" onclick="editarEstadoPago(${inv.id_factura}, '${inv.estado}')" title="Editar Estado"><i class="fas fa-edit"></i></button>`
                }
                    </div>
                </div>
            `;
            list.appendChild(div);
        });

        const paginationControls = document.getElementById('paginationControls');
        if (paginationControls) paginationControls.style.display = 'flex';
        renderPagination();
    }

    function renderPagination() {
        const totalPages = Math.ceil(filteredInvoices.length / itemsPerPage);
        const pageInfo = document.getElementById('pageInfo');
        const btnPrev = document.getElementById('btnPrevPage');
        const btnNext = document.getElementById('btnNextPage');

        if (pageInfo) pageInfo.textContent = `Página ${currentPage} de ${totalPages || 1}`;
        if (btnPrev) btnPrev.disabled = currentPage === 1;
        if (btnNext) btnNext.disabled = currentPage >= totalPages;
    }

    // Event Listeners Paginación
    const btnPrev = document.getElementById('btnPrevPage');
    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderInvoices();
            }
        });
    }

    const btnNext = document.getElementById('btnNextPage');
    if (btnNext) {
        btnNext.addEventListener('click', () => {
            const totalPages = Math.ceil(filteredInvoices.length / itemsPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderInvoices();
            }
        });
    }

    // Event listeners para filtros
    const filterMonth = document.getElementById('filterMonth');
    if (filterMonth) filterMonth.addEventListener('change', applyFilters);

    const filterYear = document.getElementById('filterYear');
    if (filterYear) filterYear.addEventListener('change', applyFilters);

    // Llenar filtro de años
    const yearSelect = document.getElementById('filterYear');
    if (yearSelect) {
        const currentYear = new Date().getFullYear();
        for (let i = currentYear; i >= currentYear - 5; i--) {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = i;
            yearSelect.appendChild(opt);
        }
    }

    // Initial Load
    loadInvoices(null);

    // Gestión de URL para carga inicial de lectura
    const urlParams = new URLSearchParams(window.location.search);
    const idLectura = urlParams.get('id_lectura');

    if (idLectura) {
        fetch(`../controladores/facturacion.php?action=get_lectura_by_id&id_lectura=${idLectura}`)
            .then(r => r.json())
            .then(data => {
                if (data.success && data.lectura) {
                    const lectura = data.lectura;
                    selectedUser = {
                        id_usuario: lectura.id_usuario,
                        nombre: lectura.nombre,
                        no_contrato: lectura.no_contrato,
                        no_medidor: lectura.no_medidor
                    };
                    currentReading = {
                        id_lectura: lectura.id_lectura,
                        fecha_lectura: lectura.fecha_lectura,
                        consumo_m3: lectura.consumo_m3
                    };
                    searchInput.value = lectura.nombre;
                    document.getElementById('clientName').textContent = lectura.nombre;
                    document.getElementById('clientContract').textContent = lectura.no_contrato;
                    document.getElementById('readingPeriod').textContent = lectura.fecha_lectura;
                    document.getElementById('consumption').textContent = `${lectura.consumo_m3} m³`;
                    const total = calculateTotal(lectura.consumo_m3);
                    document.getElementById('totalAmount').textContent = `$${total.toFixed(2)}`;
                    document.getElementById('btnGenerate').disabled = false;
                    document.getElementById('selectedReadingSection').style.display = 'block';

                    loadInvoices(lectura.id_usuario);
                    mostrarNotificacion('Lectura Cargada', `Se ha cargado la lectura del ${lectura.fecha_lectura}`, 'success');
                    window.history.replaceState({}, document.title, window.location.pathname);
                }
            })
            .catch(err => console.error(err));
    }

    // ========== ASIGNACIÓN DE FUNCIONES AL OBJETO WINDOW ==========

    window.iniciarProcesoPago = function (id, nombre, total) {
        const mensaje = `
            <div style="text-align: left;">
                <p><strong>Usuario:</strong> ${nombre}</p>
                <p class="invoice-amount" style="font-size: 1.5rem; text-align: center; margin: 1rem 0;">$${parseFloat(total).toFixed(2)}</p>
            </div>
            <p>¿Registrar pago de esta factura?</p>
        `;

        mostrarConfirmacion(
            'Cobrar Factura',
            mensaje,
            function () { realizarPago(id, total); }
        );
    };

    window.mostrarConfirmacionPago = function (idFactura, detalles) {
        const base = 50;
        const consumoValor = (detalles.consumo * detalles.tarifa).toFixed(2);

        const mensaje = `
            <div style="text-align: left; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                <p><strong>Usuario:</strong> ${detalles.nombre}</p>
                <hr style="margin: 0.5rem 0; border-color: #e2e8f0;">
                <p><strong>Desglose:</strong></p>
                <ul style="margin: 0; padding-left: 1.2rem; font-size: 0.9rem; color: var(--text-secondary);">
                    <li>Tarifa Base: $${base.toFixed(2)}</li>
                    <li>Consumo (${detalles.consumo} m³ x $${detalles.tarifa.toFixed(2)}): $${consumoValor}</li>
                </ul>
                <hr style="margin: 0.5rem 0; border-color: #e2e8f0;">
                <p style="font-size: 1.1rem; color: #1e40af; text-align: right;"><strong>Total a Pagar: $${detalles.total.toFixed(2)}</strong></p>
            </div>
            <p style="margin-top: 1rem; text-align: center;">¿Desea registrar el pago ahora?</p>
        `;

        mostrarConfirmacion(
            'Confirmar Pago',
            mensaje,
            function () { realizarPago(idFactura, detalles.total); },
            null
        );
    };

    function realizarPago(id, total) {
        fetch('../controladores/facturacion.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=pay_invoice&id_factura=${id}`
        })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    mostrarNotificacion('Pago Exitoso', 'El pago ha sido registrado correctamente.', 'success');
                    loadInvoices(selectedUser ? selectedUser.id_usuario : null);

                    setTimeout(() => {
                        const mensajeTicket = `
                        <div style="text-align: center;">
                            <i class="fas fa-print" style="font-size: 3rem; color: #1e40af; margin-bottom: 1rem;"></i>
                            <p>¿Deseas imprimir el ticket de pago por <strong>$${parseFloat(total).toFixed(2)}</strong>?</p>
                        </div>
                    `;
                        mostrarConfirmacion('Imprimir Ticket', mensajeTicket, function () {
                            viewTicket(id);
                        });
                    }, 1000);
                } else {
                    mostrarNotificacion('Error', 'No se pudo procesar el pago', 'error');
                }
            });
    }

    window.viewTicket = function (idFactura) {
        const invoice = allInvoices.find(i => i.id_factura == idFactura);
        if (!invoice) {
            mostrarNotificacion('Error', 'No se encontraron datos de la factura', 'error');
            return;
        }

        const modal = document.getElementById('ticketModal');
        const preview = document.getElementById('ticketPreviewArea');
        const fecha = new Date(invoice.fecha_emision).toLocaleDateString();
        const hora = new Date(invoice.fecha_emision).toLocaleTimeString();

        const htmlTicket = `
            <div class="ticket-paper">
                <div class="ticket-logo">SISTEMA DE AGUA POTABLE DE</div>
                <div class="ticket-center" style="font-weight:bold; margin-bottom: 5px;">SAN NICOLÁS ZECALACOAYAN</div>
                
                <div class="ticket-info">
                    <div class="ticket-row"><span>Folio:</span> <span>#${invoice.id_factura}</span></div>
                    <div class="ticket-row"><span>Fecha:</span> <span>${fecha} ${hora}</span></div>
                    <div class="ticket-row"><span>Cajero:</span> <span>Admin</span></div>
                </div>
                
                <div class="ticket-divider"></div>
                
                <div class="ticket-info">
                    <div class="ticket-row"><span>Cliente:</span> <span>${invoice.nombre || 'Cliente General'}</span></div>
                    <div class="ticket-row"><span>Contrato:</span> <span>${invoice.no_contrato || 'N/A'}</span></div>
                    <div class="ticket-row"><span>Medidor:</span> <span>${invoice.no_medidor || 'N/A'}</span></div>
                </div>
                
                <div class="ticket-divider"></div>
                
                <div class="ticket-items">
                    <div class="ticket-row">
                        <span>CONSUMO AGUA POTABLE</span>
                        <span>$${parseFloat(invoice.monto_total).toFixed(2)}</span>
                    </div>
                    <div class="ticket-row" style="font-size:10px; color:#555;">
                        <span>(Periodo: ${invoice.fecha_lectura || 'Actual'})</span>
                    </div>
                </div>
                
                <div class="ticket-divider"></div>
                
                <div class="ticket-total">
                    TOTAL: $${parseFloat(invoice.monto_total).toFixed(2)}
                </div>
                <div class="ticket-center" style="margin-top:5px;">
                    (Pagado)
                </div>
                
                <div class="ticket-divider"></div>
                
                <div class="ticket-footer ticket-center">
                    <p>¡Gracias por su pago!</p>
                    <p>Conserve este comprobante para cualquier aclaración.</p>
                </div>
            </div>
        `;

        preview.innerHTML = htmlTicket;
        modal.style.display = 'flex';

        const btnPrint = document.getElementById('btnPrintTicket');
        const btnClose = document.getElementById('btnCloseTicket');
        const btnCancel = document.getElementById('btnCancelTicket');

        const nuevoBtnPrint = btnPrint.cloneNode(true);
        const nuevoBtnClose = btnClose.cloneNode(true);
        const nuevoBtnCancel = btnCancel.cloneNode(true);

        btnPrint.parentNode.replaceChild(nuevoBtnPrint, btnPrint);
        btnClose.parentNode.replaceChild(nuevoBtnClose, btnClose);
        btnCancel.parentNode.replaceChild(nuevoBtnCancel, btnCancel);

        nuevoBtnPrint.addEventListener('click', () => window.print());
        nuevoBtnClose.addEventListener('click', () => modal.style.display = 'none');
        nuevoBtnCancel.addEventListener('click', () => modal.style.display = 'none');
    };

    window.editarEstadoPago = function (id, estadoActual) {
        if (estadoActual !== 'Pagado') return;
        mostrarConfirmacion(
            'Revertir Pago',
            `¿Estás seguro de cancelar el pago de la factura <strong>#${id}</strong>?<br>El estado volverá a <strong>Pendiente</strong>.`,
            function () {
                fetch('../controladores/facturacion.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=revert_payment&id_factura=${id}`
                })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            mostrarNotificacion('Estado Actualizado', 'La factura ha vuelto a estado Pendiente.', 'success');
                            loadInvoices(selectedUser ? selectedUser.id_usuario : null);
                        } else {
                            mostrarNotificacion('Error', data.message || 'Error al revertir', 'error');
                        }
                    });
            }
        );
    };

    // ========== SISTEMA DE NOTIFICACIONES PROFESIONAL ==========
    function mostrarNotificacion(titulo, mensaje, tipo = 'info') {
        const notifAnterior = document.querySelector('.notificacion-modal');
        if (notifAnterior) notifAnterior.remove();
        const modal = document.createElement('div');
        modal.className = 'notificacion-modal';
        let icono, colorClass;
        switch (tipo) {
            case 'success': icono = 'fa-check-circle'; colorClass = 'notif-success'; break;
            case 'error': icono = 'fa-exclamation-circle'; colorClass = 'notif-error'; break;
            case 'warning': icono = 'fa-exclamation-triangle'; colorClass = 'notif-warning'; break;
            default: icono = 'fa-info-circle'; colorClass = 'notif-info';
        }
        modal.innerHTML = `
            <div class="notificacion-overlay"></div>
            <div class="notificacion-contenido ${colorClass}">
                <div class="notificacion-icono"><i class="fas ${icono}"></i></div>
                <div class="notificacion-texto"><h3 class="notificacion-titulo">${titulo}</h3><div class="notificacion-mensaje">${mensaje}</div></div>
                <button class="notificacion-cerrar"><i class="fas fa-times"></i></button>
            </div>
        `;
        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);
        const cerrar = () => { modal.classList.remove('show'); setTimeout(() => modal.remove(), 300); };
        modal.querySelector('.notificacion-cerrar').addEventListener('click', cerrar);
        modal.querySelector('.notificacion-overlay').addEventListener('click', cerrar);
        setTimeout(cerrar, 4000);
    }

    function mostrarConfirmacion(titulo, mensaje, onAceptar, onCancelar) {
        const confAnterior = document.querySelector('.confirmacion-modal');
        if (confAnterior) confAnterior.remove();
        const modal = document.createElement('div');
        modal.className = 'confirmacion-modal';
        modal.innerHTML = `
            <div class="confirmacion-overlay"></div>
            <div class="confirmacion-contenido">
                <div class="confirmacion-header"><div class="confirmacion-icono"><i class="fas fa-question-circle"></i></div><h3 class="confirmacion-titulo">${titulo}</h3></div>
                <div class="confirmacion-cuerpo"><div class="confirmacion-mensaje">${mensaje}</div></div>
                <div class="confirmacion-acciones"><button class="btn-conf-cancelar"><i class="fas fa-times"></i> Cancelar</button><button class="btn-conf-aceptar"><i class="fas fa-check"></i> Aceptar</button></div>
            </div>
        `;
        document.body.appendChild(modal);
        setTimeout(() => modal.classList.add('show'), 10);
        const cerrar = () => { modal.classList.remove('show'); setTimeout(() => modal.remove(), 300); };
        modal.querySelector('.btn-conf-cancelar').addEventListener('click', () => { cerrar(); if (onCancelar) onCancelar(); });
        modal.querySelector('.btn-conf-aceptar').addEventListener('click', () => { cerrar(); if (onAceptar) onAceptar(); });
        modal.querySelector('.confirmacion-overlay').addEventListener('click', () => { cerrar(); if (onCancelar) onCancelar(); });
    }
});
