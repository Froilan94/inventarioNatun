/**
 * entradas.js  →  js/movimientos/entradas.js
 *
 * Depende de (globals en core.js):
 *   - mostrarToast(tipo, mensaje)
 *   - USER_ROLE
 */

// ─────────────────────────────────────────────
// ESTADO LOCAL
// ─────────────────────────────────────────────
let _medicamentos   = [];
let _unidades       = [];
let _presentaciones = [];
let _filaIdx        = 0;

// ─────────────────────────────────────────────
// DELEGACIÓN DE EVENTOS
// Subtotal automático + advertencia de lote duplicado
// ─────────────────────────────────────────────
document.addEventListener('input', function (e) {
    const tbody = '#tbodyDetallesIngreso';

    // Subtotal en tiempo real
    if (e.target.matches(`${tbody} .det-cantidad, ${tbody} .det-precio`)) {
        const fila     = e.target.closest('tr');
        const cantidad = parseFloat(fila.querySelector('.det-cantidad').value) || 0;
        const precio   = parseFloat(fila.querySelector('.det-precio').value)   || 0;
        fila.querySelector('.det-subtotal').value = (cantidad * precio).toFixed(4);
    }

    // Advertencia de lote duplicado al escribir lote o cambiar medicamento
    if (e.target.matches(`${tbody} .det-lote, ${tbody} .det-medicamento`)) {
        _verificarLotesDuplicados();
    }
});

// ─────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────
async function initFormIngresoMed() {
    try {
        const res  = await fetch('ingresos_med.php?action=get_datos_iniciales');
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _medicamentos   = json.medicamentos;
        _unidades       = json.unidades;
        _presentaciones = json.presentaciones;

        _poblarSelect('ingresoProveedor',   json.proveedores, 'Seleccione...');

        // Recibido por: usuario de sesión o lista de operadores
        const selRecibido = document.getElementById('ingresoRecibidoPor');
        if (json.usuario_sesion) {
            selRecibido.innerHTML = `<option value="${json.usuario_sesion.id}">${json.usuario_sesion.nombre}</option>`;
            selRecibido.disabled  = true;
        } else {
            _poblarSelect('ingresoRecibidoPor', json.operadores, 'Seleccione...');
        }

        // Primera fila vacía
        const tbody = document.getElementById('tbodyDetallesIngreso');
        tbody.innerHTML = '';
        tbody.appendChild(_crearFila());

    } catch (err) {
        console.error('initFormIngresoMed:', err);
        mostrarToast('error', 'Error al cargar el formulario: ' + err.message);
    }
}

// ─────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────

function _poblarSelect(selectId, items, placeholder) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(({ id, nombre }) => {
        const opt   = document.createElement('option');
        opt.value   = id;
        opt.textContent = nombre;
        sel.appendChild(opt);
    });
}

function _opcionesHTML(items, placeholder = 'Seleccione...') {
    return `<option value="">${placeholder}</option>`
        + items.map(({ id, nombre }) => `<option value="${id}">${nombre}</option>`).join('');
}

function _crearFila() {
    const idx = _filaIdx++;
    const tr  = document.createElement('tr');
    tr.dataset.fila = idx;   // ← identificador de fila en dataset, no en id del DOM
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm det-medicamento" required>
                ${_opcionesHTML(_medicamentos)}
            </select>
        </td>
        <td>
            <input type="text"
                   class="form-control form-control-sm det-lote"
                   placeholder="Núm. lote" maxlength="120">
        </td>
        <td>
            <input type="date"
                   class="form-control form-control-sm det-vencimiento">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm det-cantidad"
                   min="0.0001" step="0.0001" placeholder="0" required>
        </td>
        <td>
            <select class="form-select form-select-sm det-unidad">
                ${_opcionesHTML(_unidades)}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm det-presentacion">
                ${_opcionesHTML(_presentaciones)}
            </select>
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm det-precio"
                   min="0" step="0.0001" placeholder="0.00">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm det-subtotal"
                   placeholder="0.00" readonly tabindex="-1">
        </td>
        <td>
            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarDetalleIngreso(this)"
                    title="Eliminar fila">✕</button>
        </td>
    `;
    return tr;
}

/**
 * Revisa todas las filas buscando combinaciones medicamento+lote repetidas.
 * - Filas duplicadas → fondo amarillo + tooltip explicativo
 * - Filas únicas     → sin marca
 * No bloquea: el PHP sumará los duplicados al guardar.
 */
function _verificarLotesDuplicados() {
    const filas = [...document.querySelectorAll('#tbodyDetallesIngreso tr')];
    const conteo = {};

    // Contar ocurrencias de cada combinación medicamento+lote
    filas.forEach(fila => {
        const med  = fila.querySelector('.det-medicamento').value;
        const lote = fila.querySelector('.det-lote').value.trim();
        if (!med || !lote) return;
        const clave = `${med}||${lote}`;
        conteo[clave] = (conteo[clave] || 0) + 1;
    });

    // Marcar / desmarcar filas
    filas.forEach(fila => {
        const med   = fila.querySelector('.det-medicamento').value;
        const lote  = fila.querySelector('.det-lote').value.trim();
        const clave = `${med}||${lote}`;
        const esDup = med && lote && conteo[clave] > 1;

        fila.classList.toggle('table-warning', esDup);

        // Tooltip en el campo lote
        const inputLote = fila.querySelector('.det-lote');
        inputLote.title = esDup
            ? '⚠️ Lote repetido — las cantidades se sumarán al guardar'
            : '';
    });
}

// ─────────────────────────────────────────────
// AGREGAR / ELIMINAR FILA
// ─────────────────────────────────────────────
function agregarDetalleIngreso() {
    document.getElementById('tbodyDetallesIngreso').appendChild(_crearFila());
}

function eliminarDetalleIngreso(btn) {
    const tbody = document.getElementById('tbodyDetallesIngreso');
    if (tbody.rows.length <= 1) {
        mostrarToast('error', 'Debe haber al menos una línea de detalle.');
        return;
    }
    btn.closest('tr').remove();
    _verificarLotesDuplicados(); // re-evaluar tras eliminar
}

// ─────────────────────────────────────────────
// RECOLECTAR DETALLES
// ─────────────────────────────────────────────
function _recolectarDetalles() {
    return [...document.querySelectorAll('#tbodyDetallesIngreso tr')]
        .map(fila => ({
            medicamento_id:    fila.querySelector('.det-medicamento').value,
            numero_lote:       fila.querySelector('.det-lote').value.trim(),
            fecha_vencimiento: fila.querySelector('.det-vencimiento').value,
            cantidad:          fila.querySelector('.det-cantidad').value,
            unidad_id:         fila.querySelector('.det-unidad').value,
            presentacion_id:   fila.querySelector('.det-presentacion').value,
            precio_unitario:   fila.querySelector('.det-precio').value || '0',
        }))
        .filter(d => d.medicamento_id !== '');
}

// ─────────────────────────────────────────────
// REGISTRAR INGRESO
// ─────────────────────────────────────────────
async function registrarIngreso() {
    const payload = {
        tipo_documento:   document.getElementById('ingresoTipoDoc').value,
        numero_documento: document.getElementById('ingresoNumDoc').value,
        serie_documento:  document.getElementById('ingresoSerie').value,
        fecha_ingreso:    document.getElementById('ingresoFecha').value,
        proveedor_id:     document.getElementById('ingresoProveedor').value,
        recibido_por:     document.getElementById('ingresoRecibidoPor').value,
        detalles:         _recolectarDetalles(),
    };

    // ── Validaciones ──────────────────────────
    if (!payload.tipo_documento)
        return mostrarToast('error', 'Seleccione el tipo de documento.');
    if (!payload.fecha_ingreso)
        return mostrarToast('error', 'Ingrese la fecha de ingreso.');
    if (!payload.detalles.length)
        return mostrarToast('error', 'Agregue al menos un medicamento en los detalles.');
    for (const [i, d] of payload.detalles.entries()) {
        if (parseFloat(d.cantidad) <= 0)
            return mostrarToast('error', `Línea ${i + 1}: la cantidad debe ser mayor a 0.`);
    }

    // ── Envío ─────────────────────────────────
    const btn = document.getElementById('btnRegistrarIngreso');
    btn.disabled    = true;
    btn.textContent = 'Guardando…';

    try {
        const res  = await fetch('ingresos_med.php?action=registrar_ingreso', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        mostrarToast('exito', `${json.msg} — ID ingreso: ${json.ingreso_id}`);
        _resetFormIngreso();

    } catch (err) {
        mostrarToast('error', 'Error: ' + err.message);
        console.error('registrarIngreso:', err);
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Registrar Ingreso';
    }
}

// ─────────────────────────────────────────────
// RESET TRAS GUARDAR
// ─────────────────────────────────────────────
function _resetFormIngreso() {
    ['ingresoTipoDoc', 'ingresoNumDoc', 'ingresoSerie', 'ingresoFecha', 'ingresoProveedor']
        .forEach(id => { document.getElementById(id).value = ''; });

    const tbody = document.getElementById('tbodyDetallesIngreso');
    tbody.innerHTML = '';
    _filaIdx = 0;
    tbody.appendChild(_crearFila());
}