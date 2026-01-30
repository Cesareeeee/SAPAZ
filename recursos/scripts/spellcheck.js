// Sistema de Corrección Ortográfica y Auto-Capitalización para Campos de Nombre
document.addEventListener('DOMContentLoaded', function () {

    // Campos que tendrán corrección ortográfica y auto-capitalización
    const camposConCorreccion = [
        document.getElementById('beneficiaryName'),
        document.getElementById('editBeneficiaryName')
    ];

    // Función para capitalizar cada palabra automáticamente
    function capitalizarCadaPalabra(texto) {
        return texto.split(' ').map(palabra => {
            if (palabra.length === 0) return palabra;
            // Capitalizar primera letra de cada palabra
            return palabra.charAt(0).toUpperCase() + palabra.slice(1).toLowerCase();
        }).join(' ');
    }

    // Función para aplicar auto-capitalización a un campo
    function aplicarAutoCapitalizacion(campo) {
        if (!campo) return;

        // Evitar agregar el listener múltiples veces
        if (campo.dataset.spellcheckEnabled) return;
        campo.dataset.spellcheckEnabled = 'true';

        campo.addEventListener('input', function (e) {
            const cursorPos = this.selectionStart;
            const valorAnterior = this.value;

            // Capitalizar cada palabra
            const valorCapitalizado = capitalizarCadaPalabra(this.value);

            // Solo actualizar si hay cambios
            if (valorAnterior !== valorCapitalizado) {
                this.value = valorCapitalizado;
                // Mantener la posición del cursor
                this.setSelectionRange(cursorPos, cursorPos);
            }
        });
    }

    // Agregar auto-capitalización a los campos estáticos
    camposConCorreccion.forEach(campo => {
        aplicarAutoCapitalizacion(campo);
    });

    // Observar campos dinámicos (para carga masiva)
    const observer = new MutationObserver(function (mutations) {
        mutations.forEach(function (mutation) {
            mutation.addedNodes.forEach(function (node) {
                if (node.nodeType === 1) { // Es un elemento
                    // Buscar inputs con clase input-nombre
                    const inputs = node.querySelectorAll ? node.querySelectorAll('.input-nombre') : [];
                    inputs.forEach(input => {
                        if (input.type === 'text') {
                            aplicarAutoCapitalizacion(input);
                        }
                    });

                    // Si el nodo mismo es un input-nombre
                    if (node.classList && node.classList.contains('input-nombre') && node.type === 'text') {
                        aplicarAutoCapitalizacion(node);
                    }
                }
            });
        });
    });

    // Observar cambios en el body
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });

    // Crear menú contextual personalizado
    const menuContextual = document.createElement('div');
    menuContextual.id = 'spellCheckMenu';
    menuContextual.style.cssText = `
        position: fixed;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        padding: 8px 0;
        z-index: 10000;
        display: none;
        min-width: 220px;
        font-family: 'Inter', sans-serif;
    `;
    document.body.appendChild(menuContextual);

    // Variable para almacenar el campo activo
    let campoActivo = null;
    let palabraSeleccionada = null;

    // Función para obtener la palabra en la posición del cursor
    function obtenerPalabraEnPosicion(input, posicion) {
        const texto = input.value;
        let inicio = posicion;
        let fin = posicion;

        // Buscar el inicio de la palabra
        while (inicio > 0 && /\S/.test(texto[inicio - 1])) {
            inicio--;
        }

        // Buscar el fin de la palabra
        while (fin < texto.length && /\S/.test(texto[fin])) {
            fin++;
        }

        return {
            palabra: texto.substring(inicio, fin),
            inicio: inicio,
            fin: fin
        };
    }

    // Diccionario de correcciones comunes en español (nombres y apellidos mexicanos)
    function obtenerSugerencias(palabra) {
        const correccionesComunes = {
            // Nombres masculinos
            'jose': 'José',
            'maria': 'María',
            'jesus': 'Jesús',
            'martin': 'Martín',
            'ramon': 'Ramón',
            'angel': 'Ángel',
            'andres': 'Andrés',
            'oscar': 'Óscar',
            'victor': 'Víctor',
            'hector': 'Héctor',
            'ruben': 'Rubén',
            'adrian': 'Adrián',
            'sebastian': 'Sebastián',
            'fabian': 'Fabián',
            'julian': 'Julián',
            'agustin': 'Agustín',
            'nicolas': 'Nicolás',
            'tomas': 'Tomás',
            'german': 'Germán',
            'cesar': 'César',
            // Nombres femeninos
            'ines': 'Inés',
            'monica': 'Mónica',
            'veronica': 'Verónica',
            'beatriz': 'Beatriz',
            'patricia': 'Patricia',
            'cecilia': 'Cecilia',
            'silvia': 'Silvia',
            'sofia': 'Sofía',
            'lucia': 'Lucía',
            'ana': 'Ana',
            'carmen': 'Carmen',
            'rosa': 'Rosa',
            'guadalupe': 'Guadalupe',
            'juana': 'Juana',
            'teresa': 'Teresa',
            'margarita': 'Margarita',
            'francisca': 'Francisca',
            'isabel': 'Isabel',
            'elena': 'Elena',
            'cristina': 'Cristina',
            'alejandra': 'Alejandra',
            'daniela': 'Daniela',
            'gabriela': 'Gabriela',
            'fernanda': 'Fernanda',
            'andrea': 'Andrea',
            'valeria': 'Valeria',
            'camila': 'Camila',
            'natalia': 'Natalia',
            'carolina': 'Carolina',
            'paula': 'Paula',
            'laura': 'Laura',
            'diana': 'Diana',
            'sandra': 'Sandra',
            'claudia': 'Claudia',
            'lorena': 'Lorena',
            'susana': 'Susana',
            'raquel': 'Raquel',
            'rocio': 'Rocío',
            'belen': 'Belén',
            // Apellidos
            'garcia': 'García',
            'gomez': 'Gómez',
            'lopez': 'López',
            'martinez': 'Martínez',
            'rodriguez': 'Rodríguez',
            'hernandez': 'Hernández',
            'gonzalez': 'González',
            'perez': 'Pérez',
            'sanchez': 'Sánchez',
            'ramirez': 'Ramírez',
            'torres': 'Torres',
            'flores': 'Flores',
            'rivera': 'Rivera',
            'diaz': 'Díaz',
            'cruz': 'Cruz',
            'morales': 'Morales',
            'jimenez': 'Jiménez',
            'ruiz': 'Ruiz',
            'alvarez': 'Álvarez',
            'castillo': 'Castillo',
            'gutierrez': 'Gutiérrez',
            'mendoza': 'Mendoza',
            'moreno': 'Moreno',
            'ortiz': 'Ortiz',
            'vazquez': 'Vázquez',
            'reyes': 'Reyes',
            'munoz': 'Muñoz',
            'romero': 'Romero',
            'medina': 'Medina',
            'aguilar': 'Aguilar',
            'guerrero': 'Guerrero',
            'cortes': 'Cortés',
            'nunez': 'Núñez',
            // Preposiciones comunes en apellidos
            'de': 'De',
            'del': 'Del',
            'la': 'La',
            'las': 'Las',
            'los': 'Los',
            'san': 'San',
            'santa': 'Santa'
        };

        const palabraLower = palabra.toLowerCase();
        const sugerencias = [];

        // Buscar en el diccionario
        if (correccionesComunes[palabraLower]) {
            sugerencias.push(correccionesComunes[palabraLower]);
        }

        // Capitalizar primera letra si no está en el diccionario
        if (sugerencias.length === 0 && palabra.length > 0) {
            const capitalizada = palabra.charAt(0).toUpperCase() + palabra.slice(1).toLowerCase();
            if (capitalizada !== palabra) {
                sugerencias.push(capitalizada);
            }
        }

        // Agregar versión en mayúsculas
        if (palabra !== palabra.toUpperCase() && palabra.length > 1) {
            sugerencias.push(palabra.toUpperCase());
        }

        return sugerencias;
    }

    // Función para mostrar el menú contextual
    function mostrarMenuContextual(event, campo) {
        event.preventDefault();

        campoActivo = campo;
        const posicion = campo.selectionStart;
        const info = obtenerPalabraEnPosicion(campo, posicion);

        if (!info.palabra || info.palabra.trim() === '') {
            return;
        }

        palabraSeleccionada = info;
        const sugerencias = obtenerSugerencias(info.palabra);

        // Limpiar menú
        menuContextual.innerHTML = '';

        // Título
        const titulo = document.createElement('div');
        titulo.style.cssText = `
            padding: 8px 16px;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.875rem;
        `;
        titulo.textContent = `Palabra: "${info.palabra}"`;
        menuContextual.appendChild(titulo);

        if (sugerencias.length > 0) {
            // Agregar sugerencias
            const tituloSugerencias = document.createElement('div');
            tituloSugerencias.style.cssText = `
                padding: 8px 16px;
                font-size: 0.75rem;
                color: #6b7280;
                font-weight: 500;
            `;
            tituloSugerencias.textContent = 'Sugerencias:';
            menuContextual.appendChild(tituloSugerencias);

            sugerencias.forEach(sugerencia => {
                const item = document.createElement('div');
                item.style.cssText = `
                    padding: 8px 16px;
                    cursor: pointer;
                    transition: background-color 0.2s;
                    font-size: 0.875rem;
                    color: #1f2937;
                `;
                item.textContent = sugerencia;

                item.addEventListener('mouseenter', function () {
                    this.style.backgroundColor = '#f3f4f6';
                });

                item.addEventListener('mouseleave', function () {
                    this.style.backgroundColor = 'transparent';
                });

                item.addEventListener('click', function () {
                    reemplazarPalabra(sugerencia);
                });

                menuContextual.appendChild(item);
            });
        } else {
            const noSugerencias = document.createElement('div');
            noSugerencias.style.cssText = `
                padding: 8px 16px;
                font-size: 0.875rem;
                color: #9ca3af;
                font-style: italic;
            `;
            noSugerencias.textContent = 'No hay sugerencias disponibles';
            menuContextual.appendChild(noSugerencias);
        }

        // Agregar opción de ignorar
        const separador = document.createElement('div');
        separador.style.cssText = `
            height: 1px;
            background-color: #e5e7eb;
            margin: 4px 0;
        `;
        menuContextual.appendChild(separador);

        const ignorar = document.createElement('div');
        ignorar.style.cssText = `
            padding: 8px 16px;
            cursor: pointer;
            transition: background-color 0.2s;
            font-size: 0.875rem;
            color: #6b7280;
        `;
        ignorar.textContent = 'Ignorar';

        ignorar.addEventListener('mouseenter', function () {
            this.style.backgroundColor = '#f3f4f6';
        });

        ignorar.addEventListener('mouseleave', function () {
            this.style.backgroundColor = 'transparent';
        });

        ignorar.addEventListener('click', function () {
            ocultarMenu();
        });

        menuContextual.appendChild(ignorar);

        // Posicionar menú
        menuContextual.style.left = event.pageX + 'px';
        menuContextual.style.top = event.pageY + 'px';
        menuContextual.style.display = 'block';
    }

    // Función para reemplazar la palabra
    function reemplazarPalabra(nuevaPalabra) {
        if (!campoActivo || !palabraSeleccionada) return;

        const texto = campoActivo.value;
        const nuevoTexto = texto.substring(0, palabraSeleccionada.inicio) +
            nuevaPalabra +
            texto.substring(palabraSeleccionada.fin);

        campoActivo.value = nuevoTexto;

        // Posicionar cursor después de la palabra reemplazada
        const nuevaPosicion = palabraSeleccionada.inicio + nuevaPalabra.length;
        campoActivo.setSelectionRange(nuevaPosicion, nuevaPosicion);
        campoActivo.focus();

        // Disparar evento de input para que se actualicen las validaciones
        campoActivo.dispatchEvent(new Event('input', { bubbles: true }));

        ocultarMenu();
    }

    // Función para ocultar el menú
    function ocultarMenu() {
        menuContextual.style.display = 'none';
        campoActivo = null;
        palabraSeleccionada = null;
    }

    // Agregar event listeners a los campos
    camposConCorreccion.forEach(campo => {
        if (campo) {
            // Click derecho para mostrar menú
            campo.addEventListener('contextmenu', function (e) {
                mostrarMenuContextual(e, this);
            });

            // Ocultar menú al hacer click en el campo
            campo.addEventListener('click', function () {
                ocultarMenu();
            });
        }
    });

    // Ocultar menú al hacer click fuera
    document.addEventListener('click', function (e) {
        if (e.target !== menuContextual && !menuContextual.contains(e.target)) {
            ocultarMenu();
        }
    });

    // Ocultar menú al presionar Escape
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            ocultarMenu();
        }
    });

    // Ocultar menú al hacer scroll
    window.addEventListener('scroll', ocultarMenu);
});
