
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
    const btnUsuariosExcel = document.getElementById('btnReporteUsuariosExcel');

    // Filtros
    const filtroTipo = document.getElementById('filtroTipoUsuarios');
    const filtroValor = document.getElementById('filtroValorUsuarios');
    const containerValor = document.getElementById('containerFiltroValor');

    // Lógica del filtro (Calle/Barrio)
    if (filtroTipo) {
        filtroTipo.addEventListener('change', async function () {
            const tipo = this.value;

            // Resetear
            filtroValor.innerHTML = '<option value="">Seleccione...</option>';

            if (!tipo) {
                containerValor.style.display = 'none';
                return;
            }

            try {
                // Determinar acción
                const action = tipo === 'calle' ? 'get_calles' : 'get_barrios';

                // Fetch datos
                const response = await fetch(`../controladores/beneficiarios.php?action=${action}`);
                if (!response.ok) throw new Error('Error en la respuesta del servidor');

                const data = await response.json();

                if (data.success) {
                    const items = tipo === 'calle' ? data.calles : data.barrios;

                    // Agregar opción "Todos" explícitamente
                    const optionTodas = document.createElement('option');
                    optionTodas.value = "";
                    optionTodas.textContent = "Todos";
                    filtroValor.appendChild(optionTodas);

                    if (items && items.length > 0) {
                        items.forEach(item => {
                            if (item && item.trim() !== '') {
                                const option = document.createElement('option');
                                option.value = item;
                                option.textContent = item;
                                filtroValor.appendChild(option);
                            }
                        });
                        // Mostrar contenedor
                        containerValor.style.display = 'block';
                    } else {
                        // Si no hay ítems, igual mostramos "Todos" para que no se bloquee
                        containerValor.style.display = 'block';
                    }
                } else {
                    console.error('Error del servidor:', data.message);
                }
            } catch (error) {
                console.error('Error obteniendo datos del filtro:', error);
                alert('Hubo un problema cargando los datos del filtro. Intente recargar la página.');
            }
        });
    }

    // Helper para obtener params de usuarios
    const getParamsUsuarios = () => {
        const params = {};
        if (filtroTipo && filtroTipo.value) {
            // Permitimos valor vacío (significa "Todos")
            params.filtro_tipo = filtroTipo.value;
            params.filtro_valor = filtroValor.value;
        }
        return params;
    };

    if (btnUsuarios) {
        btnUsuarios.addEventListener('click', () => {
            const params = getParamsUsuarios();
            if (params !== null) generarReporte('usuarios', params);
        });
    }

    if (btnUsuariosExcel) {
        btnUsuariosExcel.addEventListener('click', () => {
            const params = getParamsUsuarios();
            if (params !== null) {
                params.formato = 'excel';
                generarReporte('usuarios', params);
            }
        });
    }

    // 2. Reporte de Lecturas
    const btnLecturas = document.getElementById('btnReporteLecturas');
    const btnLecturasExcel = document.getElementById('btnReporteLecturasExcel');

    const handleLecturas = (formato = '') => {
        const inicio = document.getElementById('inicioLecturas').value;
        const fin = document.getElementById('finLecturas').value;

        if (!inicio || !fin) {
            alert('Por favor seleccione ambas fechas para el reporte de lecturas.');
            return;
        }

        const params = { inicio, fin };
        if (formato) params.formato = formato;

        generarReporte('lecturas', params);
    };

    if (btnLecturas) btnLecturas.addEventListener('click', () => handleLecturas());
    if (btnLecturasExcel) btnLecturasExcel.addEventListener('click', () => handleLecturas('excel'));

    // 3. Reporte de Ingresos
    const btnIngresos = document.getElementById('btnReporteIngresos');
    const btnIngresosExcel = document.getElementById('btnReporteIngresosExcel');

    const handleIngresos = (formato = '') => {
        const inicio = document.getElementById('inicioIngresos').value;
        const fin = document.getElementById('finIngresos').value;

        if (!inicio || !fin) {
            alert('Por favor seleccione ambas fechas para el reporte de ingresos.');
            return;
        }

        const params = { inicio, fin };
        if (formato) params.formato = formato;

        generarReporte('ingresos', params);
    };

    if (btnIngresos) btnIngresos.addEventListener('click', () => handleIngresos());
    if (btnIngresosExcel) btnIngresosExcel.addEventListener('click', () => handleIngresos('excel'));

    // 4. Reporte de Adeudos
    const btnAdeudos = document.getElementById('btnReporteAdeudos');
    const btnAdeudosExcel = document.getElementById('btnReporteAdeudosExcel');

    if (btnAdeudos) {
        btnAdeudos.addEventListener('click', () => {
            generarReporte('adeudos');
        });
    }

    if (btnAdeudosExcel) {
        btnAdeudosExcel.addEventListener('click', () => {
            generarReporte('adeudos', { formato: 'excel' });
        });
    }

    // 5. Reporte Historial Global
    const btnHistorial = document.getElementById('btnReporteHistorial');
    const btnHistorialExcel = document.getElementById('btnReporteHistorialExcel');

    // Filtros Historial
    const filtroTipoHistorial = document.getElementById('filtroTipoHistorial');
    const filtroValorHistorial = document.getElementById('filtroValorHistorial');
    const containerValorHistorial = document.getElementById('containerFiltroValorHistorial');

    // Lógica del filtro para Historial (copia adaptada de usuarios)
    if (filtroTipoHistorial) {
        filtroTipoHistorial.addEventListener('change', async function () {
            const tipo = this.value;
            filtroValorHistorial.innerHTML = '<option value="">Seleccione...</option>';

            if (!tipo) {
                containerValorHistorial.style.display = 'none';
                return;
            }

            try {
                const action = tipo === 'calle' ? 'get_calles' : 'get_barrios';
                const response = await fetch(`../controladores/beneficiarios.php?action=${action}`);
                if (!response.ok) throw new Error('Error en la respuesta del servidor');

                const data = await response.json();

                if (data.success) {
                    const items = tipo === 'calle' ? data.calles : data.barrios;

                    // Opción Todos explícita
                    const optionTodas = document.createElement('option');
                    optionTodas.value = "";
                    optionTodas.textContent = "Todos";
                    filtroValorHistorial.appendChild(optionTodas);

                    if (items && items.length > 0) {
                        items.forEach(item => {
                            if (item && item.trim() !== '') {
                                const option = document.createElement('option');
                                option.value = item;
                                option.textContent = item;
                                filtroValorHistorial.appendChild(option);
                            }
                        });
                        containerValorHistorial.style.display = 'block';
                    } else {
                        containerValorHistorial.style.display = 'block'; // Fallback
                    }
                }
            } catch (error) {
                console.error('Error filter historial:', error);
            }
        });
    }

    const getParamsHistorial = () => {
        const params = {};
        if (filtroTipoHistorial && filtroTipoHistorial.value) {
            params.filtro_tipo = filtroTipoHistorial.value;
            params.filtro_valor = filtroValorHistorial.value;
        }
        return params;
    };

    if (btnHistorial) {
        btnHistorial.addEventListener('click', () => {
            const params = getParamsHistorial();
            generarReporte('historial_global', params);
        });
    }

    if (btnHistorialExcel) {
        btnHistorialExcel.addEventListener('click', () => {
            const params = getParamsHistorial();
            params.formato = 'excel';
            generarReporte('historial_global', params);
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
