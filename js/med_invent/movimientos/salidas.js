/**
 * salidas.js  →  js2/movimientos/salidas.js
 *
 * Depende de (globals en core.js):
 *   - mostrarToast(tipo, mensaje)
 *   - USER_ROLE
 */

// ─────────────────────────────────────────────
// ESTADO LOCAL
// ─────────────────────────────────────────────
let _medSalida   = [];
let _unidSalida  = [];
let _presSalida  = [];
let _filaIdxSal  = 0;

// ─────────────────────────────────────────────
// DELEGACIÓN DE EVENTOS
// ─────────────────────────────────────────────
document.addEventListener('input', function (e) {
    const tbody = '#tbodyDetallesSalida';

    // Subtotal en tiempo real
    if (e.target.matches(`${tbody} .sal-cantidad, ${tbody} .sal-precio`)) {
        const fila     = e.target.closest('tr');
        const cantidad = parseFloat(fila.querySelector('.sal-cantidad').value) || 0;
        const precio   = parseFloat(fila.querySelector('.sal-precio').value)   || 0;
        fila.querySelector('.sal-subtotal').value = (cantidad * precio).toFixed(4);
    }

    // Búsqueda de beneficiario con debounce
    if (e.target.matches(`${tbody} .sal-beneficiario-input`)) {
        _buscarBeneficiarioDebounce(e.target);
    }
});

// Cerrar dropdowns de beneficiario al hacer clic fuera
document.addEventListener('click', function (e) {
    if (!e.target.matches('.sal-beneficiario-input')) {
        document.querySelectorAll('.beneficiario-dropdown').forEach(d => d.remove());
    }
});

// ─────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────
async function initFormSalidaMed() {
    try {
        const res  = await fetch('api/inventarios/medicamentos/movimientos/salidas_med.php?action=get_datos_iniciales');
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _medSalida   = json.medicamentos;
        _unidSalida  = json.unidades;
        _presSalida  = json.presentaciones;

        // Componente
        _poblarSelectSalida('salidaComponente', json.componentes, 'Seleccione...');

        // Entregado por
        const selEntregado = document.getElementById('salidaEntregadoPor');
        if (json.usuario_sesion) {
            selEntregado.innerHTML = `<option value="${json.usuario_sesion.id}">${json.usuario_sesion.nombre}</option>`;
            selEntregado.disabled  = true;
        } else {
            _poblarSelectSalida('salidaEntregadoPor', json.operadores, 'Seleccione...');
        }

        // Primera fila
        const tbody = document.getElementById('tbodyDetallesSalida');
        tbody.innerHTML = '';
        tbody.appendChild(_crearFilaSalida());

    } catch (err) {
        console.error('initFormSalidaMed:', err);
        mostrarToast('error', 'Error al cargar el formulario: ' + err.message);
    }
}

// ─────────────────────────────────────────────
// HELPERS PRIVADOS
// ─────────────────────────────────────────────
function _poblarSelectSalida(selectId, items, placeholder) {
    const sel = document.getElementById(selectId);
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(({ id, nombre }) => {
        const opt       = document.createElement('option');
        opt.value       = id;
        opt.textContent = nombre;
        sel.appendChild(opt);
    });
}

function _opcionesHTMLSalida(items, placeholder = 'Seleccione...') {
    return `<option value="">${placeholder}</option>`
        + items.map(({ id, nombre }) => `<option value="${id}">${nombre}</option>`).join('');
}

function _crearFilaSalida() {
    const idx = _filaIdxSal++;
    const tr  = document.createElement('tr');
    tr.dataset.fila = idx;
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm sal-medicamento" required
                    onchange="cargarLotesSalida(this)">
                ${_opcionesHTMLSalida(_medSalida)}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm sal-lote" required>
                <option value="">— Elige medicamento —</option>
            </select>
            <small class="sal-stock-info text-muted"></small>
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm sal-cantidad"
                   min="0.0001" step="0.0001" placeholder="0" required>
        </td>
        <td>
            <!-- Autocompletado desde el lote — bloqueado -->
            <input type="text"
                   class="form-control form-control-sm sal-unidad-texto"
                   placeholder="—" readonly tabindex="-1"
                   style="background:#f8f9fa;">
            <input type="hidden" class="sal-unidad-id">
        </td>
        <td>
            <!-- Autocompletado desde el lote — bloqueado -->
            <input type="text"
                   class="form-control form-control-sm sal-presentacion-texto"
                   placeholder="—" readonly tabindex="-1"
                   style="background:#f8f9fa;">
            <input type="hidden" class="sal-presentacion-id">
        </td>
        <td style="position:relative;">
            <input type="text"
                   class="form-control form-control-sm sal-beneficiario-input"
                   placeholder="Buscar beneficiaria..."
                   autocomplete="off">
            <input type="hidden" class="sal-beneficiario-id">
        </td>
        <td>
            <!-- Autocompletado desde el lote — bloqueado -->
            <input type="number"
                   class="form-control form-control-sm sal-precio"
                   min="0" step="0.0001" placeholder="0.00"
                   readonly tabindex="-1"
                   style="background:#f8f9fa;">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm sal-subtotal"
                   placeholder="0.00" readonly tabindex="-1">
        </td>
        <td>
            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarDetalleSalida(this)"
                    title="Eliminar fila">✕</button>
        </td>
    `;

    // Al cambiar lote -> autocompletar unidad, presentacion, precio y stock
    tr.querySelector('.sal-lote').addEventListener('change', function () {
        const opt   = this.options[this.selectedIndex];
        const stock = opt.dataset.stock ?? '';
        const venc  = opt.dataset.venc  ?? '';
        const info  = tr.querySelector('.sal-stock-info');

        // Info de stock
        if (stock) {
            info.textContent = `Disp: ${parseFloat(stock).toFixed(2)}${venc ? ' | Vence: ' + venc : ''}`;
            info.className   = parseFloat(stock) <= 10
                ? 'sal-stock-info text-warning small'
                : 'sal-stock-info text-success small';
        } else {
            info.textContent = '';
        }

        // Autocompletar unidad, presentacion y precio desde el lote
        tr.querySelector('.sal-unidad-id').value          = opt.dataset.unidadId     ?? '';
        tr.querySelector('.sal-unidad-texto').value       = opt.dataset.unidadNombre ?? '—';
        tr.querySelector('.sal-presentacion-id').value    = opt.dataset.presId       ?? '';
        tr.querySelector('.sal-presentacion-texto').value = opt.dataset.presNombre   ?? '—';
        tr.querySelector('.sal-precio').value             = opt.dataset.precio       ?? '0';

        // Recalcular subtotal
        const cantidad = parseFloat(tr.querySelector('.sal-cantidad').value) || 0;
        const precio   = parseFloat(opt.dataset.precio) || 0;
        tr.querySelector('.sal-subtotal').value = (cantidad * precio).toFixed(4);
    });

    return tr;
}

// ─────────────────────────────────────────────
// CARGAR LOTES AL ELEGIR MEDICAMENTO
// ─────────────────────────────────────────────
async function cargarLotesSalida(selectMed) {
    const fila    = selectMed.closest('tr');
    const selLote = fila.querySelector('.sal-lote');
    const info    = fila.querySelector('.sal-stock-info');
    const med_id  = selectMed.value;

    selLote.innerHTML = '<option value="">Cargando...</option>';
    info.textContent  = '';

    if (!med_id) {
        selLote.innerHTML = '<option value="">— Elige medicamento —</option>';
        return;
    }

    try {
        const res  = await fetch(`api/inventarios/medicamentos/movimientos/salidas_med.php?action=get_lotes_medicamento&medicamento_id=${med_id}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        if (!json.lotes.length) {
            selLote.innerHTML = '<option value="">Sin stock disponible</option>';
            return;
        }

        selLote.innerHTML = '<option value="">Seleccione lote...</option>';
        json.lotes.forEach(l => {
            const opt        = document.createElement('option');
            opt.value        = l.id;
            opt.textContent  = `${l.lote} | Stock: ${parseFloat(l.stock).toFixed(2)}${l.vencimiento ? ' | Vence: ' + l.vencimiento : ''}`;
            opt.dataset.stock        = l.stock;
            opt.dataset.venc         = l.vencimiento     ?? '';
            // Datos del ultimo ingreso para autocompletar
            opt.dataset.unidadId     = l.unidad_id       ?? '';
            opt.dataset.unidadNombre = l.unidad_nombre   ?? '—';
            opt.dataset.presId       = l.presentacion_id ?? '';
            opt.dataset.presNombre   = l.presentacion_nombre ?? '—';
            opt.dataset.precio       = l.precio_unitario ?? '0';
            selLote.appendChild(opt);
        });

    } catch (err) {
        selLote.innerHTML = '<option value="">Error al cargar lotes</option>';
        console.error('cargarLotesSalida:', err);
    }
}

// ─────────────────────────────────────────────
// BÚSQUEDA DE BENEFICIARIA (con debounce)
// ─────────────────────────────────────────────
let _debounceTimer = null;

function _buscarBeneficiarioDebounce(input) {
    clearTimeout(_debounceTimer);
    _debounceTimer = setTimeout(() => _buscarBeneficiario(input), 300);
}

async function _buscarBeneficiario(input) {
    const q    = input.value.trim();
    const fila = input.closest('tr');

    // Limpiar id oculto si el usuario borra el texto
    if (q.length < 2) {
        fila.querySelector('.sal-beneficiario-id').value = '';
        _cerrarDropdown(input);
        return;
    }

    try {
        const res  = await fetch(`api/inventarios/medicamentos/movimientos/salidas_med.php?action=get_beneficiarios&q=${encodeURIComponent(q)}`);
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _mostrarDropdownBeneficiario(input, json.beneficiarios, fila);

    } catch (err) {
        console.error('_buscarBeneficiario:', err);
    }
}

function _mostrarDropdownBeneficiario(input, beneficiarios, fila) {
    _cerrarDropdown(input);

    if (!beneficiarios.length) return;

    const dropdown = document.createElement('ul');
    dropdown.className    = 'beneficiario-dropdown list-group shadow';
    dropdown.style.cssText = `
        position:absolute; z-index:9999; width:100%;
        max-height:200px; overflow-y:auto;
        top: 100%; left: 0;
    `;

    beneficiarios.forEach(b => {
        const li = document.createElement('li');
        li.className     = 'list-group-item list-group-item-action py-1 px-2 small';
        li.textContent   = `${b.nombre} — ${b.dpi}`;
        li.style.cursor  = 'pointer';
        li.addEventListener('click', () => {
            input.value = b.nombre;
            fila.querySelector('.sal-beneficiario-id').value = b.id;
            _cerrarDropdown(input);
        });
        dropdown.appendChild(li);
    });

    // Insertar dropdown relativo al td
    input.parentElement.style.position = 'relative';
    input.parentElement.appendChild(dropdown);
}

function _cerrarDropdown(input) {
    input.parentElement.querySelectorAll('.beneficiario-dropdown').forEach(d => d.remove());
}

// ─────────────────────────────────────────────
// AGREGAR / ELIMINAR FILA
// ─────────────────────────────────────────────
function agregarDetalleSalida() {
    document.getElementById('tbodyDetallesSalida').appendChild(_crearFilaSalida());
}

function eliminarDetalleSalida(btn) {
    const tbody = document.getElementById('tbodyDetallesSalida');
    if (tbody.rows.length <= 1) {
        mostrarToast('error', 'Debe haber al menos una línea de detalle.');
        return;
    }
    btn.closest('tr').remove();
}

// ─────────────────────────────────────────────
// RECOLECTAR DETALLES
// ─────────────────────────────────────────────
function _recolectarDetallesSalida() {
    return [...document.querySelectorAll('#tbodyDetallesSalida tr')]
        .map(fila => ({
            medicamento_id:  fila.querySelector('.sal-medicamento').value,
            lote_id:         fila.querySelector('.sal-lote').value,
            cantidad:        fila.querySelector('.sal-cantidad').value,
            unidad_id:       fila.querySelector('.sal-unidad-id').value,
            presentacion_id: fila.querySelector('.sal-presentacion-id').value,
            beneficiario_id: fila.querySelector('.sal-beneficiario-id').value,
            precio_unitario: fila.querySelector('.sal-precio').value || '0',
        }))
        .filter(d => d.medicamento_id !== '');
}

// ─────────────────────────────────────────────
// REGISTRAR SALIDA
// ─────────────────────────────────────────────
async function registrarSalida() {
    const payload = {
        tipo_documento:   document.getElementById('salidaTipoDoc').value,
        numero_documento: document.getElementById('salidaNumDoc').value,
        serie_documento:  document.getElementById('salidaSerie').value,
        fecha_salida:     document.getElementById('salidaFecha').value,
        componente_id:    document.getElementById('salidaComponente').value,
        entregado_por:    document.getElementById('salidaEntregadoPor').value,
        detalles:         _recolectarDetallesSalida(),
    };

    // ── Validaciones ──────────────────────────
    if (!payload.tipo_documento)
        return mostrarToast('error', 'Seleccione el tipo de documento.');
    if (!payload.fecha_salida)
        return mostrarToast('error', 'Ingrese la fecha de salida.');
    if (!payload.detalles.length)
        return mostrarToast('error', 'Agregue al menos un medicamento.');

    for (const [i, d] of payload.detalles.entries()) {
        if (!d.lote_id)
            return mostrarToast('error', `Línea ${i + 1}: seleccione un lote.`);
        if (parseFloat(d.cantidad) <= 0)
            return mostrarToast('error', `Línea ${i + 1}: la cantidad debe ser mayor a 0.`);
        if (!d.beneficiario_id)
            return mostrarToast('error', `Línea ${i + 1}: seleccione una beneficiaria.`);
    }

    // ── Envío ─────────────────────────────────
    const btn = document.getElementById('btnRegistrarSalida');
    btn.disabled    = true;
    btn.textContent = 'Guardando…';

    try {
        const res  = await fetch('api/inventarios/medicamentos/movimientos/salidas_med.php?action=registrar_salida', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        mostrarToast('exito', `${json.msg} — ID: ${json.salida_id}`);

        /* Abrir planilla en nueva pestaña
        window.open(
            `api/inventarios/medicamentos/movimientos/planilla_salida.php?salida_id=${json.salida_id}`,
            '_blank'
        );*/

        _resetFormSalida();

    } catch (err) {
        mostrarToast('error', 'Error: ' + err.message);
        console.error('registrarSalida:', err);
    } finally {
        btn.disabled    = false;
        btn.textContent = 'Registrar Salida';
    }
}

// ─────────────────────────────────────────────
// RESET
// ─────────────────────────────────────────────
function _resetFormSalida() {
    ['salidaTipoDoc', 'salidaNumDoc', 'salidaSerie',
     'salidaFecha', 'salidaComponente'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    const tbody = document.getElementById('tbodyDetallesSalida');
    tbody.innerHTML = '';
    _filaIdxSal = 0;
    tbody.appendChild(_crearFilaSalida());
}
