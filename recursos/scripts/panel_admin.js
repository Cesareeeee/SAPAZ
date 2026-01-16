// Elementos del DOM
const alternarMenu = document.getElementById('menuToggle');
const barraLateral = document.getElementById('sidebar');
const notificacion = document.getElementById('notification');
const modal = document.getElementById('modal');
const cerrarModal = document.getElementById('modalClose');
const cancelarModal = document.getElementById('modalCancel');
const formularioLectura = document.getElementById('readingForm');
const formularioCliente = document.getElementById('clientForm');

// Alternar Menú
if (alternarMenu) {
    alternarMenu.addEventListener('click', () => {
        barraLateral.classList.toggle('active');
    });
}

// Modal Functions
function showModal() {
    if (modal) modal.style.display = 'flex';
}

function hideModal() {
    if (modal) modal.style.display = 'none';
}

if (cerrarModal) cerrarModal.addEventListener('click', hideModal);
if (cancelarModal) cancelarModal.addEventListener('click', hideModal);

// Click outside modal to close
window.addEventListener('click', (e) => {
    if (modal && e.target === modal) {
        hideModal();
    }
});

// Clic fuera de la barra lateral para cerrar en móvil
window.addEventListener('click', (e) => {
    const barraLateral = document.getElementById('sidebar');
    const alternarMenu = document.getElementById('menuToggle');
    if (barraLateral && window.innerWidth <= 992 && !barraLateral.contains(e.target) && !alternarMenu.contains(e.target) && barraLateral.classList.contains('active')) {
        barraLateral.classList.remove('active');
    }
});

// Función de Notificación
function mostrarNotificacion(titulo, mensaje, tipo = 'success') {
    if (!notificacion) return;
    const tituloNotificacion = notificacion.querySelector('.notification-title');
    const mensajeNotificacion = notificacion.querySelector('.notification-message');
    const iconoNotificacion = notificacion.querySelector('.notification-icon');

    if (tituloNotificacion) tituloNotificacion.textContent = titulo;
    if (mensajeNotificacion) mensajeNotificacion.textContent = mensaje;

    // Establecer icono según tipo
    if (iconoNotificacion) {
        iconoNotificacion.className = 'notification-icon ' + tipo;

        if (tipo === 'success') {
            iconoNotificacion.innerHTML = '<i class="fas fa-check"></i>';
        } else if (tipo === 'error') {
            iconoNotificacion.innerHTML = '<i class="fas fa-times"></i>';
        } else if (tipo === 'warning') {
            iconoNotificacion.innerHTML = '<i class="fas fa-exclamation"></i>';
        }
    }

    // Mostrar notificación
    notificacion.classList.add('show');

    // Ocultar después de 3 segundos
    setTimeout(() => {
        notificacion.classList.remove('show');
    }, 3000);
}

// Envíos de Formularios
if (formularioLectura) {
    formularioLectura.addEventListener('submit', (e) => {
        e.preventDefault();
        mostrarNotificacion('Éxito', 'Lectura guardada correctamente', 'success');
        formularioLectura.reset();
    });
}

if (formularioCliente) {
    formularioCliente.addEventListener('submit', (e) => {
        e.preventDefault();
        mostrarNotificacion('Éxito', 'Cliente guardado correctamente', 'success');
        formularioCliente.reset();
    });
}

// Table Actions
document.querySelectorAll('.fa-eye').forEach(btn => {
    btn.parentElement.addEventListener('click', () => {
        showModal();
    });
});

// Chart Options
document.querySelectorAll('.chart-option').forEach(option => {
    option.addEventListener('click', () => {
        document.querySelectorAll('.chart-option').forEach(opt => opt.classList.remove('active'));
        option.classList.add('active');
    });
});

// Implementación Simple de Gráfico
const lienzo = document.getElementById('consumptionChart');
if (lienzo) {
    const contexto = lienzo.getContext('2d');
    const ancho = lienzo.parentElement.offsetWidth;
    const alto = 300;

    lienzo.width = ancho;
    lienzo.height = alto;

    // Datos de muestra
    const meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'];
    const datos = [65, 78, 90, 81, 56, 85];

    // Dibujar gráfico
    const anchoBarra = ancho / meses.length * 0.6;
    const espaciado = ancho / meses.length * 0.4;
    const valorMaximo = Math.max(...datos);
    const escala = (alto - 40) / valorMaximo;

    // Limpiar lienzo
    contexto.clearRect(0, 0, ancho, alto);

    // Dibujar barras
    datos.forEach((valor, indice) => {
        const x = indice * (anchoBarra + espaciado) + espaciado / 2;
        const altoBarra = valor * escala;
        const y = alto - altoBarra - 20;

        // Dibujar barra
        contexto.fillStyle = '#4FA3D1';
        contexto.fillRect(x, y, anchoBarra, altoBarra);

        // Dibujar valor
        contexto.fillStyle = '#6B7280';
        contexto.font = '12px sans-serif';
        contexto.textAlign = 'center';
        contexto.fillText(valor + ' m³', x + anchoBarra / 2, y - 5);

        // Dibujar mes
        contexto.fillText(meses[indice], x + anchoBarra / 2, alto - 5);
    });

    // Redibujar al cambiar tamaño de ventana
    window.addEventListener('resize', () => {
        const nuevoAncho = lienzo.parentElement.offsetWidth;
        lienzo.width = nuevoAncho;

        // Recalcular y redibujar
        const nuevoAnchoBarra = nuevoAncho / meses.length * 0.6;
        const nuevoEspaciado = nuevoAncho / meses.length * 0.4;

        contexto.clearRect(0, 0, nuevoAncho, alto);

        datos.forEach((valor, indice) => {
            const x = indice * (nuevoAnchoBarra + nuevoEspaciado) + nuevoEspaciado / 2;
            const altoBarra = valor * escala;
            const y = alto - altoBarra - 20;

            contexto.fillStyle = '#4FA3D1';
            contexto.fillRect(x, y, nuevoAnchoBarra, altoBarra);

            contexto.fillStyle = '#6B7280';
            contexto.font = '12px sans-serif';
            contexto.textAlign = 'center';
            contexto.fillText(valor + ' m³', x + nuevoAnchoBarra / 2, y - 5);
            contexto.fillText(meses[indice], x + nuevoAnchoBarra / 2, alto - 5);
        });
    });
}

// Establecer fecha de hoy como predeterminada para fecha de lectura
const fechaLectura = document.getElementById('readingDate');
if (fechaLectura) {
    const hoy = new Date().toISOString().split('T')[0];
    fechaLectura.value = hoy;
}