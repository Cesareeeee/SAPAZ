// Función de Alerta Personalizada
function mostrarAlerta(titulo, mensaje, tipo = 'success', autoCerrar = true) {
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
            ${tipo !== 'loading' ? '<button class="custom-alert-close">&times;</button>' : ''}
        </div>
    `;
    document.body.appendChild(alerta);

    // Mostrar con animación
    setTimeout(() => alerta.classList.add('show'), 10);

    if (tipo !== 'loading') {
        // Cerrar al hacer clic en el botón
        alerta.querySelector('.custom-alert-close').addEventListener('click', () => {
            alerta.classList.remove('show');
            setTimeout(() => {
                if (document.body.contains(alerta)) {
                    document.body.removeChild(alerta);
                }
            }, 300);
        });

        // Cerrar automáticamente después de 5 segundos si autoCerrar
        if (autoCerrar) {
            setTimeout(() => {
                if (document.body.contains(alerta)) {
                    alerta.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alerta)) {
                            document.body.removeChild(alerta);
                        }
                    }, 300);
                }
            }, 5000);
        }
    }

    return alerta; // Retornar para poder cerrarlo manualmente
}

// Validación de Beneficiarios
document.addEventListener('DOMContentLoaded', function () {
    // Tab switching
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons
            tabButtons.forEach(btn => btn.classList.remove('active'));
            // Add active to clicked
            button.classList.add('active');

            // Hide all contents
            tabContents.forEach(content => content.style.display = 'none');
            // Show selected
            const tab = button.getAttribute('data-tab');
            document.getElementById(tab + 'Section').style.display = 'block';
        });
    });

    // Restore active tab from localStorage or show default
    const savedTab = localStorage.getItem('activeTab') || 'list';
    const savedTabButton = document.querySelector(`[data-tab="${savedTab}"]`);
    if (savedTabButton) {
        tabButtons.forEach(btn => btn.classList.remove('active'));
        savedTabButton.classList.add('active');
        tabContents.forEach(content => content.style.display = 'none');
        document.getElementById(savedTab + 'Section').style.display = 'block';
    } else {
        document.getElementById('addSection').style.display = 'block';
    }


    // Combined search and filter functionality
    const searchInput = document.getElementById('searchInput');
    const streetFilter = document.getElementById('streetFilter');

    // Inicializar selectores de calle
    function inicializarCalles() {
        fetch('../controladores/beneficiarios.php?action=get_calles')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    data.calles.forEach(calle => {
                        const option = document.createElement('option');
                        option.value = calle.calle;
                        option.textContent = calle.calle;
                        streetFilter.appendChild(option);
                    });
                }
            })
            .catch(error => console.error('Error cargando calles:', error));
    }

    // Llamar a inicializar
    inicializarCalles();

    // Event listeners para modal de edición
    editCloseBtn.addEventListener('click', () => {
        editModalBackdrop.classList.remove('show');
    });

    editCancelBtn.addEventListener('click', () => {
        editModalBackdrop.classList.remove('show');
    });

    editModalBackdrop.addEventListener('click', (e) => {
        if (e.target === editModalBackdrop) {
            editModalBackdrop.classList.remove('show');
        }
    });

    // Submit handler for edit form
    document.getElementById('editForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        formData.append('action', 'update');

        fetch('../controladores/beneficiarios.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Éxito', 'Beneficiario actualizado correctamente', 'success');
                    editModalBackdrop.classList.remove('show');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    mostrarAlerta('Error', data.message || 'Error al actualizar', 'error');
                }
            })
            .catch(error => mostrarAlerta('Error', 'Error de conexión', 'error'));
    });

    function filterBeneficiaries() {
        const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
        const streetValue = streetFilter ? streetFilter.value : '';
        const rows = document.querySelectorAll('.beneficiary-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.querySelector('.beneficiary-name').textContent.toLowerCase();
            const street = row.querySelector('.card-item:nth-child(3) .card-value').textContent.trim().toLowerCase();
            const medidor = row.querySelector('.beneficiary-medidor').textContent.toLowerCase();

            const matchesSearch = name.includes(searchValue) || medidor.includes(searchValue);
            const matchesStreet = streetValue === '' || street.includes(streetValue.toLowerCase());

            if (matchesSearch && matchesStreet) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        const noResults = document.getElementById('noSearchResults');
        const noResultsRow = document.getElementById('noResultsRow');
        if (visibleCount === 0 && rows.length > 0) {
            noResults.style.display = 'block';
            if (noResultsRow) noResultsRow.style.display = 'none';
        } else {
            noResults.style.display = 'none';
            if (noResultsRow && rows.length === 0) noResultsRow.style.display = 'block';
        }
    }

    // Search functionality
    if (searchInput) {
        searchInput.addEventListener('input', filterBeneficiaries);
    }

    // Street filter functionality
    if (streetFilter) {
        streetFilter.addEventListener('change', filterBeneficiaries);
    }


    // Edit and Delete handlers

    document.addEventListener('click', function (e) {
        if (e.target.classList.contains('edit-btn') || e.target.closest('.edit-btn')) {
            const id = e.target.closest('.edit-btn').getAttribute('data-id');
            // Load data and open modal
            fetch('../controladores/beneficiarios.php?action=get&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('editId').value = data.beneficiario.id_usuario;
                        document.getElementById('editBeneficiaryName').value = data.beneficiario.nombre;
                        document.getElementById('editContractNumber').value = data.beneficiario.no_contrato;
                        document.getElementById('editMeterNumber').value = data.beneficiario.no_medidor;
                        document.getElementById('editStreetAndNumber').value = data.beneficiario.calle;
                        document.getElementById('editStatus').value = data.beneficiario.activo ? '1' : '0';
                        document.getElementById('previousName').textContent = data.beneficiario.nombre_anterior ? `El usuario anterior era: ${data.beneficiario.nombre_anterior}` : '';
                        editModalBackdrop.classList.add('show');
                    } else {
                        mostrarAlerta('Error', 'No se pudo cargar el beneficiario', 'error');
                    }
                })
                .catch(error => mostrarAlerta('Error', 'Error al cargar', 'error'));
        }
        if (e.target.classList.contains('delete-btn') || e.target.closest('.delete-btn')) {
            const id = e.target.closest('.delete-btn').getAttribute('data-id');
            mostrarAlerta('Confirmar Eliminación', '¿Estás seguro de eliminar este beneficiario?', 'warning', false);
            // Add confirm button to alert
            const alertContent = document.querySelector('.custom-alert-content.warning');
            if (alertContent) {
                const confirmBtn = document.createElement('button');
                confirmBtn.className = 'btn btn-primary';
                confirmBtn.textContent = 'Eliminar';
                confirmBtn.style.marginTop = '1rem';
                confirmBtn.addEventListener('click', function () {
                    // Close alert
                    const alerta = document.querySelector('.custom-alert');
                    alerta.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alerta)) {
                            document.body.removeChild(alerta);
                        }
                    }, 300);

                    // Mostrar alerta de carga
                    const alertaCarga = mostrarAlerta('Eliminando', 'Por favor espera mientras se elimina el beneficiario...', 'loading');

                    // Send delete request
                    fetch('../controladores/beneficiarios.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: 'action=delete&id=' + id
                    })
                        .then(response => response.json())
                        .then(data => {
                            // Cerrar alerta de carga
                            alertaCarga.classList.remove('show');
                            setTimeout(() => {
                                if (document.body.contains(alertaCarga)) {
                                    document.body.removeChild(alertaCarga);
                                }
                            }, 300);

                            if (data.success) {
                                mostrarAlerta('Eliminado', 'Beneficiario eliminado correctamente', 'success');
                                localStorage.setItem('activeTab', 'list');
                                setTimeout(() => location.reload(), 1500);
                            } else {
                                mostrarAlerta('Error', data.message || 'Error al eliminar', 'error');
                            }
                        })
                        .catch(error => {
                            // Cerrar alerta de carga
                            alertaCarga.classList.remove('show');
                            setTimeout(() => {
                                if (document.body.contains(alertaCarga)) {
                                    document.body.removeChild(alertaCarga);
                                }
                            }, 300);
                            mostrarAlerta('Error', 'Error al eliminar', 'error');
                        });
                });
                alertContent.appendChild(confirmBtn);
                const cancelBtn = document.createElement('button');
                cancelBtn.className = 'btn btn-outline';
                cancelBtn.textContent = 'Cancelar';
                cancelBtn.style.marginTop = '1rem';
                cancelBtn.style.marginLeft = '0.5rem';
                cancelBtn.addEventListener('click', function () {
                    const alerta = document.querySelector('.custom-alert');
                    alerta.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alerta)) {
                            document.body.removeChild(alerta);
                        }
                    }, 300);
                });
                alertContent.appendChild(cancelBtn);
            }
        }
    });
    const formularioBeneficiario = document.getElementById('beneficiaryForm');
    const numeroContrato = document.getElementById('contractNumber');
    const numeroMedidor = document.getElementById('meterNumber');
    const nombreBeneficiario = document.getElementById('beneficiaryName');
    const calleNumero = document.getElementById('streetAndNumber');
    const fechaAlta = document.getElementById('registrationDate');
    const botonGuardar = document.getElementById('saveButton');
    const botonCancelar = document.getElementById('cancelButton');

    // Función para mostrar error
    function mostrarError(campo, mensaje) {
        campo.style.borderColor = 'red';
        campo.style.boxShadow = '0 0 0 3px rgba(244, 67, 54, 0.1)';
        // Mostrar mensaje de error, por ejemplo, en un span
        let errorSpan = campo.parentElement.querySelector('.error-message');
        if (!errorSpan) {
            errorSpan = document.createElement('span');
            errorSpan.className = 'error-message';
            errorSpan.style.color = 'red';
            errorSpan.style.fontSize = '0.8rem';
            campo.parentElement.appendChild(errorSpan);
        }
        errorSpan.textContent = mensaje;
    }

    // Función para quitar error
    function quitarError(campo) {
        campo.style.borderColor = '#ddd';
        campo.style.boxShadow = 'none';
        const errorSpan = campo.parentElement.querySelector('.error-message');
        if (errorSpan) {
            errorSpan.textContent = '';
        }
    }

    // Validar número de contrato
    numeroContrato.addEventListener('input', function () {
        if (this.value.trim() === '' || isNaN(this.value)) {
            mostrarError(this, 'Debe ingresar un número válido');
        } else {
            quitarError(this);
        }
    });

    // Validar número de medidor
    numeroMedidor.addEventListener('input', function () {
        if (this.value.trim() === '' || isNaN(this.value)) {
            mostrarError(this, 'Debe ingresar un número válido');
        } else {
            quitarError(this);
        }
    });

    // Validar nombre
    nombreBeneficiario.addEventListener('input', function () {
        if (this.value.trim() === '' || this.value.length < 3) {
            mostrarError(this, 'El nombre debe tener al menos 3 caracteres');
        } else {
            quitarError(this);
        }
    });

    // Validar calle y número
    calleNumero.addEventListener('input', function () {
        if (this.value.trim() === '') {
            mostrarError(this, 'Debe ingresar la calle y número');
        } else {
            quitarError(this);
        }
    });

    // La fecha se establece automáticamente

    // Enviar formulario
    formularioBeneficiario.addEventListener('submit', function (e) {
        e.preventDefault();

        let esValido = true;

        // Validar todos los campos
        if (numeroContrato.value.trim() === '' || isNaN(numeroContrato.value)) {
            mostrarError(numeroContrato, 'Número de contrato inválido');
            esValido = false;
        }
        if (numeroMedidor.value.trim() === '' || isNaN(numeroMedidor.value)) {
            mostrarError(numeroMedidor, 'Número de medidor inválido');
            esValido = false;
        }
        if (nombreBeneficiario.value.trim() === '' || nombreBeneficiario.value.length < 3) {
            mostrarError(nombreBeneficiario, 'Nombre inválido');
            esValido = false;
        }
        if (calleNumero.value.trim() === '') {
            mostrarError(calleNumero, 'Calle y número requerido');
            esValido = false;
        }

        if (esValido) {
            // Verificar conexión a internet
            if (!navigator.onLine) {
                mostrarAlerta('Sin Conexión', 'No hay conexión a internet. Verifica tu conexión e intenta nuevamente.', 'error');
                return;
            }

            // Establecer fecha de registro
            const fechaAlta = document.getElementById('registrationDate');
            fechaAlta.value = new Date().toISOString().split('T')[0];

            // Mostrar alerta de carga
            const alertaCarga = mostrarAlerta('Guardando', 'Por favor espera mientras se guarda el beneficiario...', 'loading');
            botonGuardar.disabled = true;

            // Enviar formulario
            const formData = new FormData(this);
            fetch('../controladores/beneficiarios.php', {
                method: 'POST',
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta del servidor');
                    }
                    return response.json();
                })
                .then(data => {
                    // Cerrar alerta de carga
                    alertaCarga.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alertaCarga)) {
                            document.body.removeChild(alertaCarga);
                        }
                    }, 300);

                    if (data.success) {
                        mostrarAlerta('Éxito', 'Beneficiario guardado correctamente', 'success');
                        formularioBeneficiario.reset();
                        localStorage.setItem('activeTab', 'list');
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarAlerta('Error', data.message || 'Error al guardar', 'error');
                    }
                })
                .catch(error => {
                    // Cerrar alerta de carga
                    alertaCarga.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alertaCarga)) {
                            document.body.removeChild(alertaCarga);
                        }
                    }, 300);

                    if (error.message.includes('Failed to fetch') || error.message.includes('NetworkError')) {
                        mostrarAlerta('Error de Conexión', 'No se pudo conectar al servidor. Verifica tu conexión a internet.', 'error');
                    } else {
                        mostrarAlerta('Error', 'Error al guardar: ' + error.message, 'error');
                    }
                })
                .finally(() => {
                    botonGuardar.disabled = false;
                });
        }
    });

    // Cancelar
    botonCancelar.addEventListener('click', function () {
        formularioBeneficiario.reset();
        // Quitar errores
        [numeroContrato, numeroMedidor, nombreBeneficiario, calleNumero].forEach(campo => quitarError(campo));
    });

    // Hover para botón guardar
    botonGuardar.addEventListener('mouseover', function () {
        this.style.backgroundColor = '#004085';
        this.style.borderColor = '#004085';
    });
    botonGuardar.addEventListener('mouseout', function () {
        this.style.backgroundColor = '#0056b3';
        this.style.borderColor = '#0056b3';
    });

    // Modal handlers
    document.getElementById('modalClose').addEventListener('click', function () {
        document.getElementById('modal').style.display = 'none';
        document.getElementById('editForm').reset();
    });

    document.getElementById('modalCancel').addEventListener('click', function () {
        document.getElementById('modal').style.display = 'none';
        document.getElementById('editForm').reset();
    });

    // Validación para edición
    const editBeneficiaryName = document.getElementById('editBeneficiaryName');
    const editContractNumber = document.getElementById('editContractNumber');
    const editMeterNumber = document.getElementById('editMeterNumber');
    const editStreetAndNumber = document.getElementById('editStreetAndNumber');

    editContractNumber.addEventListener('input', function () {
        if (this.value.trim() === '' || isNaN(this.value)) {
            mostrarError(this, 'Debe ingresar un número válido');
        } else {
            quitarError(this);
        }
    });

    editMeterNumber.addEventListener('input', function () {
        if (this.value.trim() === '' || isNaN(this.value)) {
            mostrarError(this, 'Debe ingresar un número válido');
        } else {
            quitarError(this);
        }
    });

    editBeneficiaryName.addEventListener('input', function () {
        if (this.value.trim() === '' || this.value.length < 3) {
            mostrarError(this, 'El nombre debe tener al menos 3 caracteres');
        } else {
            quitarError(this);
        }
    });

    editStreetAndNumber.addEventListener('input', function () {
        if (this.value.trim() === '') {
            mostrarError(this, 'Debe ingresar la calle y número');
        } else {
            quitarError(this);
        }
    });

    document.getElementById('saveEditButton').addEventListener('click', function () {
        const formData = new FormData(document.getElementById('editForm'));
        formData.append('action', 'update');

        // Validar
        let esValido = true;

        if (editContractNumber.value.trim() === '' || isNaN(editContractNumber.value)) {
            mostrarError(editContractNumber, 'Número de contrato inválido');
            esValido = false;
        }
        if (editMeterNumber.value.trim() === '' || isNaN(editMeterNumber.value)) {
            mostrarError(editMeterNumber, 'Número de medidor inválido');
            esValido = false;
        }
        if (editBeneficiaryName.value.trim() === '' || editBeneficiaryName.value.length < 3) {
            mostrarError(editBeneficiaryName, 'Nombre inválido');
            esValido = false;
        }
        if (editStreetAndNumber.value.trim() === '') {
            mostrarError(editStreetAndNumber, 'Calle y número requerido');
            esValido = false;
        }

        if (esValido) {
            // Mostrar alerta de carga
            const alertaCarga = mostrarAlerta('Actualizando', 'Por favor espera mientras se actualiza el beneficiario...', 'loading');
            document.getElementById('saveEditButton').disabled = true;

            fetch('../controladores/beneficiarios.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    // Cerrar alerta de carga
                    alertaCarga.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alertaCarga)) {
                            document.body.removeChild(alertaCarga);
                        }
                    }, 300);

                    if (data.success) {
                        mostrarAlerta('Actualizado', 'Beneficiario actualizado correctamente', 'success');
                        document.getElementById('modal').style.display = 'none';
                        localStorage.setItem('activeTab', 'list');
                        document.getElementById('editForm').reset();
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        mostrarAlerta('Error', data.message || 'Error al actualizar', 'error');
                    }
                })
                .catch(error => {
                    // Cerrar alerta de carga
                    alertaCarga.classList.remove('show');
                    setTimeout(() => {
                        if (document.body.contains(alertaCarga)) {
                            document.body.removeChild(alertaCarga);
                        }
                    }, 300);
                    mostrarAlerta('Error', 'Error al actualizar', 'error');
                })
                .finally(() => {
                    document.getElementById('saveEditButton').disabled = false;
                });
        }
    });

    // Establecer fecha de hoy
    document.getElementById('registrationDate').value = new Date().toISOString().split('T')[0];

    // Cargar calles para selects
    fetch('../controladores/beneficiarios.php?action=get_streets')
        .then(respuesta => respuesta.json())
        .then(datos => {
            if (datos.success) {
                const select_calle = document.getElementById('streetAndNumber');
                const filtro_calle = document.getElementById('streetFilter');
                const edit_calle = document.getElementById('editStreetAndNumber');
                datos.streets.forEach(calle => {
                    // Para select de agregar
                    const opcion_agregar = document.createElement('option');
                    opcion_agregar.value = calle;
                    opcion_agregar.textContent = calle;
                    select_calle.appendChild(opcion_agregar);

                    // Para filtro
                    const opcion_filtro = document.createElement('option');
                    opcion_filtro.value = calle;
                    opcion_filtro.textContent = calle;
                    filtro_calle.appendChild(opcion_filtro);

                    // Para editar
                    const opcion_editar = document.createElement('option');
                    opcion_editar.value = calle;
                    opcion_editar.textContent = calle;
                    edit_calle.appendChild(opcion_editar);
                });
            }
        })
        .catch(error => console.error('Error al cargar calles:', error));
});