import { reactive } from 'vue';

// Estado compartido que pinta <Alertas /> (montado en AuthenticatedLayout).
export const estadoAlertas = reactive({
    toasts: [],
    confirmacion: null,
});

const DURACION = 3500;
const MAXIMO = 3;
let siguienteId = 1;

function notificar(tipo, texto, duracion = DURACION) {
    const toast = { id: siguienteId++, tipo, texto, duracion };
    estadoAlertas.toasts.push(toast);
    if (estadoAlertas.toasts.length > MAXIMO) estadoAlertas.toasts.shift();
    setTimeout(() => cerrarToast(toast.id), duracion);
}

export function cerrarToast(id) {
    const i = estadoAlertas.toasts.findIndex((t) => t.id === id);
    if (i !== -1) estadoAlertas.toasts.splice(i, 1);
}

export function exito(texto) {
    notificar('exito', texto);
}

export function error(texto) {
    notificar('error', texto, 5000);
}

export function advertencia(texto) {
    notificar('advertencia', texto, 4500);
}

/**
 * Diálogo de confirmación: confirmar({ title, content, danger, okText, onOk }).
 */
export function confirmar({ title = '¿Estás seguro?', content = '', danger = true, okText = 'Eliminar', onOk }) {
    estadoAlertas.confirmacion = { title, content, danger, okText, onOk };
}

export function responderConfirmacion(aceptar) {
    const actual = estadoAlertas.confirmacion;
    estadoAlertas.confirmacion = null;
    if (aceptar && actual?.onOk) actual.onOk();
}
