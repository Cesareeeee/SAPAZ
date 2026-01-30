
document.addEventListener('DOMContentLoaded', function () {

    // Función genérica para abrir PDFs
    const generarReporte = (tipo, params = {}) => {
        let url = `../controladores/generador_reportes.php?tipo=${tipo}`;

        // Agregar parámetros extra (fechas, etc)
        for (const [key, value] of Object.entries(params)) {
            if (value) {
                url += `&${key}=${value}`;
            }
        }

        // Abrir en nueva pestaña
        window.open(url, '_blank');
    };

    // 1. Reporte de Usuarios
    const btnUsuarios = document.getElementById('btnReporteUsuarios');
    if (btnUsuarios) {
        btnUsuarios.addEventListener('click', () => {
            generarReporte('usuarios');
        });
    }

    // 2. Reporte de Lecturas
    const btnLecturas = document.getElementById('btnReporteLecturas');
    if (btnLecturas) {
        btnLecturas.addEventListener('click', () => {
            const inicio = document.getElementById('inicioLecturas').value;
            const fin = document.getElementById('finLecturas').value;

            if (!inicio || !fin) {
                alert('Por favor seleccione ambas fechas para el reporte de lecturas.');
                return;
            }

            generarReporte('lecturas', { inicio, fin });
        });
    }

    // 3. Reporte de Ingresos
    const btnIngresos = document.getElementById('btnReporteIngresos');
    if (btnIngresos) {
        btnIngresos.addEventListener('click', () => {
            const inicio = document.getElementById('inicioIngresos').value;
            const fin = document.getElementById('finIngresos').value;

            if (!inicio || !fin) {
                alert('Por favor seleccione ambas fechas para el reporte de ingresos.');
                return;
            }

            generarReporte('ingresos', { inicio, fin });
        });
    }

    // 4. Reporte de Adeudos
    const btnAdeudos = document.getElementById('btnReporteAdeudos');
    if (btnAdeudos) {
        btnAdeudos.addEventListener('click', () => {
            generarReporte('adeudos');
        });
    }

    // Establecer fechas por defecto (Hoy y hace 30 días)
    const inputsFechaInicio = document.querySelectorAll('input[type="date"][id^="inicio"]');
    const inputsFechaFin = document.querySelectorAll('input[type="date"][id^="fin"]');

    const hoy = new Date().toISOString().split('T')[0];
    const haceUnMes = new Date();
    haceUnMes.setMonth(haceUnMes.getMonth() - 1);
    const fechaMesAtras = haceUnMes.toISOString().split('T')[0];

    inputsFechaFin.forEach(input => input.value = hoy);
    inputsFechaInicio.forEach(input => input.value = fechaMesAtras);
});
