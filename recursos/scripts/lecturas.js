// lecturas.js

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const readingForm = document.getElementById('readingForm');
    const lecturaForm = document.getElementById('lecturaForm');
    const cancelBtn = document.getElementById('cancelBtn');
    const clearSearch = document.getElementById('clearSearch');
    const notification = document.getElementById('notification');

    let searchTimeout;

    // Función para mostrar notificaciones
    function showNotification(message, type = 'success') {
        const icon = notification.querySelector('.notification-icon');
        const title = notification.querySelector('.notification-title');
        const msg = notification.querySelector('.notification-message');

        if (type === 'error') {
            icon.className = 'notification-icon error';
            icon.innerHTML = '<i class="fas fa-exclamation-triangle"></i>';
            title.textContent = 'Error';
        } else {
            icon.className = 'notification-icon success';
            icon.innerHTML = '<i class="fas fa-check"></i>';
            title.textContent = 'Éxito';
        }

        msg.textContent = message;
        notification.classList.add('show');

        setTimeout(() => {
            notification.classList.remove('show');
        }, 3000);
    }

    // Búsqueda con debounce
    searchInput.addEventListener('input', function() {
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
            item.addEventListener('click', function() {
                selectUser(this.dataset.id, this.dataset.nombre, this.dataset.medidor, this.dataset.calle);
            });
        });
    }

    // Seleccionar usuario
    function selectUser(id, nombre, medidor, calle) {
        document.getElementById('selectedUserId').value = id;
        document.getElementById('clienteNombre').value = nombre;
        document.getElementById('numeroMedidor').value = medidor;

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
                    document.getElementById('lecturaAnterior').value = data.lectura_anterior;
                    document.getElementById('fechaAnterior').value = data.fecha_anterior || 'Primera lectura';
                } else {
                    document.getElementById('lecturaAnterior').value = '0';
                    document.getElementById('fechaAnterior').value = 'Primera lectura';
                }

                // Fecha actual
                const today = new Date();
                document.getElementById('fechaLectura').value = today.toLocaleDateString('es-ES');

                // Mostrar formulario
                readingForm.style.display = 'block';
                readingForm.scrollIntoView({ behavior: 'smooth' });
            })
            .catch(error => {
                console.error('Error:', error);
                showNotification('Error al obtener datos', 'error');
            });
    }

    // Enviar formulario
    lecturaForm.addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../controladores/lecturas.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification('Lectura guardada correctamente');
                // Limpiar formulario
                lecturaForm.reset();
                readingForm.style.display = 'none';
                searchInput.value = '';
                searchResults.innerHTML = '';
            } else {
                showNotification(data.message || 'Error al guardar', 'error');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showNotification('Error de conexión', 'error');
        });
    });

    // Limpiar búsqueda
    clearSearch.addEventListener('click', function() {
        searchInput.value = '';
        searchResults.innerHTML = '';
        lecturaForm.reset();
        readingForm.style.display = 'none';
        searchInput.focus();
    });

    // Cancelar
    cancelBtn.addEventListener('click', function() {
        lecturaForm.reset();
        readingForm.style.display = 'none';
    });
});