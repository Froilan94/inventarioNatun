/**
 * ingreso_med.js
 * Lógica del formulario "Registrar Ingreso de Medicamentos".
 *
 * Flujo:
 *   1. initFormIngresoMed()  → carga datos iniciales y pinta el formulario
 *   2. agregarDetalle()      → añade una fila a la tabla de detalles
 *   3. registrarIngreso()    → valida y envía el POST al PHP
 */

// ─────────────────────────────────────────────
// ESTADO LOCAL
// ─────────────────────────────────────────────
let _medicamentos   = [];
let _unidades       = [];
let _presentaciones = [];
let _filaIdx        = 0;      // índice incremental para IDs únicos de fila

// ─────────────────────────────────────────────
// INICIALIZACIÓN
// ─────────────────────────────────────────────
async function initFormIngresoMed() {
    try {
        const res  = await fetch('api/inventarios/medicamentos/reportes/ingreso_med.php?action=get_datos_iniciales');
        const json = await res.json();
        if (!json.ok) throw new Error(json.msg);

        _medicamentos   = json.medicamentos;
        _unidades       = json.unidades;
        _presentaciones = json.presentaciones;

        // Proveedor
        _poblarSelect('ingresoProveedor', json.proveedores, 'id', 'nombre', 'Seleccione...');

        // Recibido por: usuario de sesión o lista de operadores
        const selRecibido = document.getElementById('ingresoRecibidoPor');
        if (json.usuario_sesion) {
            // Solo una opción: el usuario logueado
            selRecibido.innerHTML = `
                <option value="${json.usuario_sesion.id}" selected>
                    ${json.usuario_sesion.nombre}
                </option>`;
            selRecibido.disabled = true; // no se puede cambiar
        } else {
            _poblarSelect('ingresoRecibidoPor', json.operadores, 'id', 'nombre', 'Seleccione...');
        }

        // Limpiar tabla de detalles y agregar primera fila vacía
        document.getElementById('tbodyDetallesIngreso').innerHTML = '';
        _filaIdx = 0;
        agregarDetalleIngreso();

    } catch (err) {
        console.error('initFormIngresoMed:', err);
        _mostrarAlerta('Error al cargar el formulario: ' + err.message, 'danger');
    }
}

// ─────────────────────────────────────────────
// POBLAR SELECT GENÉRICO
// ─────────────────────────────────────────────
function _poblarSelect(id, items, keyVal, keyLabel, placeholder) {
    const sel = document.getElementById(id);
    if (!sel) return;
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    items.forEach(item => {
        const opt = document.createElement('option');
        opt.value       = item[keyVal];
        opt.textContent = item[keyLabel];
        sel.appendChild(opt);
    });
}

// ─────────────────────────────────────────────
// AGREGAR FILA DE DETALLE
// ─────────────────────────────────────────────
function agregarDetalleIngreso() {
    const idx  = _filaIdx++;
    const tbody = document.getElementById('tbodyDetallesIngreso');

    const tr = document.createElement('tr');
    tr.id = `filaIngreso_${idx}`;
    tr.innerHTML = `
        <td>
            <select class="form-select form-select-sm" id="detMed_${idx}" required>
                <option value="">Selección</option>
                ${_medicamentos.map(m =>
                    `<option value="${m.id}">${m.nombre}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="text"
                   class="form-control form-control-sm"
                   id="detLote_${idx}"
                   placeholder="Núm. lote"
                   maxlength="120">
        </td>
        <td>
            <input type="date"
                   class="form-control form-control-sm"
                   id="detVenc_${idx}">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm"
                   id="detCantidad_${idx}"
                   min="0.0001"
                   step="0.0001"
                   placeholder="0"
                   oninput="calcularSubtotalFila(${idx})"
                   required>
        </td>
        <td>
            <select class="form-select form-select-sm" id="detUnidad_${idx}">
                <option value="">Selección</option>
                ${_unidades.map(u =>
                    `<option value="${u.id}">${u.nombre}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <select class="form-select form-select-sm" id="detPres_${idx}">
                <option value="">Seleccione...</option>
                ${_presentaciones.map(p =>
                    `<option value="${p.id}">${p.nombre}</option>`
                ).join('')}
            </select>
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm"
                   id="detPrecio_${idx}"
                   min="0"
                   step="0.0001"
                   placeholder="0.00"
                   oninput="calcularSubtotalFila(${idx})">
        </td>
        <td>
            <input type="number"
                   class="form-control form-control-sm"
                   id="detSubtotal_${idx}"
                   placeholder="0.00"
                   readonly
                   tabindex="-1">
        </td>
        <td>
            <button type="button"
                    class="btn btn-danger btn-sm"
                    onclick="eliminarDetalleIngreso(${idx})"
                    title="Eliminar fila">
                ✕
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

// ─────────────────────────────────────────────
// ELIMINAR FILA
// ─────────────────────────────────────────────
function eliminarDetalleIngreso(idx) {
    const tbody = document.getElementById('tbodyDetallesIngreso');
    if (tbody.rows.length <= 1) {
        _mostrarAlerta('Debe haber al menos una línea de detalle.', 'warning');
        return;
    }
    document.getElementById(`filaIngreso_${idx}`)?.remove();
}

// ─────────────────────────────────────────────
// CALCULAR SUBTOTAL POR FILA
// ─────────────────────────────────────────────
function calcularSubtotalFila(idx) {
    const cantidad = parseFloat(document.getElementById(`detCantidad_${idx}`)?.value) || 0;
    const precio   = parseFloat(document.getElementById(`detPrecio_${idx}`)?.value)   || 0;
    const subtotal = document.getElementById(`detSubtotal_${idx}`);
    if (subtotal) subtotal.value = (cantidad * precio).toFixed(4);
}

// ─────────────────────────────────────────────
// RECOLECTAR DETALLES DE LA TABLA
// ─────────────────────────────────────────────
function _recolectarDetalles() {
    const filas = document.querySelectorAll('#tbodyDetallesIngreso tr');
    const detalles = [];

    for (const fila of filas) {
        const id = fila.id.replace('filaIngreso_', '');
        const med_id = document.getElementById(`detMed_${id}`)?.value ?? '';

        if (!med_id) continue; // saltar filas sin medicamento elegido

        detalles.push({
            medicamento_id:   med_id,
            numero_lote:      document.getElementById(`detLote_${id}`)?.value.trim()  ?? '',
            fecha_vencimiento:document.getElementById(`detVenc_${id}`)?.value         ?? '',
            cantidad:         document.getElementById(`detCantidad_${id}`)?.value     ?? '0',
            unidad_id:        document.getElementById(`detUnidad_${id}`)?.value       ?? '',
            presentacion_id:  document.getElementById(`detPres_${id}`)?.value         ?? '',
            precio_unitario:  document.getElementById(`detPrecio_${id}`)?.value       ?? '0',
        });
    }
    return detalles;
}

// ─────────────────────────────────────────────
// REGISTRAR INGRESO
// ─────────────────────────────────────────────
async function registrarIngreso() {
    // Recolectar cabecera
    const payload = {
        tipo_documento:   document.getElementById('ingresoTipoDoc')?.value    ?? '',
        numero_documento: document.getElementById('ingresoNumDoc')?.value     ?? '',
        serie_documento:  document.getElementById('ingresoSerie')?.value      ?? '',
        fecha_ingreso:    document.getElementById('ingresoFecha')?.value      ?? '',
        proveedor_id:     document.getElementById('ingresoProveedor')?.value  ?? '',
        recibido_por:     document.getElementById('ingresoRecibidoPor')?.value ?? '',
        detalles:         _recolectarDetalles(),
    };

    // Validación frontend básica
    if (!payload.tipo_documento) {
        return _mostrarAlerta('Seleccione el tipo de documento.', 'warning');
    }
    if (!payload.fecha_ingreso) {
        return _mostrarAlerta('Ingrese la fecha de ingreso.', 'warning');
    }
    if (!payload.detalles.length) {
        return _mostrarAlerta('Agregue al menos un medicamento en los detalles.', 'warning');
    }
    for (let i = 0; i < payload.detalles.length; i++) {
        const d = payload.detalles[i];
        if (!d.medicamento_id) {
            return _mostrarAlerta(`Línea ${i+1}: seleccione un medicamento.`, 'warning');
        }
        if (parseFloat(d.cantidad) <= 0) {
            return _mostrarAlerta(`Línea ${i+1}: la cantidad debe ser mayor a 0.`, 'warning');
        }
    }

    // Deshabilitar botón mientras se envía
    const btnGuardar = document.getElementById('btnRegistrarIngreso');
    if (btnGuardar) { btnGuardar.disabled = true; btnGuardar.textContent = 'Guardando…'; }

    try {
        const res  = await fetch('ingreso_med.php?action=registrar_ingreso', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload),
        });
        const json = await res.json();

        if (!json.ok) throw new Error(json.msg);

        _mostrarAlerta(`✅ ${json.msg} (ID ingreso: ${json.ingreso_id})`, 'success');
        _resetFormIngreso();

    } catch (err) {
        _mostrarAlerta('❌ Error: ' + err.message, 'danger');
        console.error('registrarIngreso:', err);
    } finally {
        if (btnGuardar) { btnGuardar.disabled = false; btnGuardar.textContent = 'Registrar Ingreso'; }
    }
}

// ─────────────────────────────────────────────
// RESET DEL FORMULARIO TRAS GUARDAR
// ─────────────────────────────────────────────
function _resetFormIngreso() {
    ['ingresoTipoDoc','ingresoNumDoc','ingresoSerie',
     'ingresoFecha','ingresoProveedor'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });
    document.getElementById('tbodyDetallesIngreso').innerHTML = '';
    _filaIdx = 0;
    agregarDetalleIngreso();
}

// ─────────────────────────────────────────────
// ALERTAS (reutiliza el div #alertaIngreso si existe,
// si no lo crea sobre el formulario)
// ─────────────────────────────────────────────
function _mostrarAlerta(msg, tipo = 'info') {
    let div = document.getElementById('alertaIngreso');
    if (!div) {
        div = document.createElement('div');
        div.id = 'alertaIngreso';
        const form = document.getElementById('formIngresoMed')
                  ?? document.getElementById('VistaIngresoMedicamentos');
        form?.prepend(div);
    }
    div.className = `alert alert-${tipo} alert-dismissible fade show`;
    div.innerHTML = `
        ${msg}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
    // Auto-cerrar alertas de éxito después de 5 s
    if (tipo === 'success') setTimeout(() => div.remove(), 5000);
}