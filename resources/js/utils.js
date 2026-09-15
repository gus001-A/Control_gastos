export function formatCurrency(value) {
    const n = Number(value ?? 0);
    const negativo = n < 0;
    const texto = '$' + Math.abs(n).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    return negativo ? `-${texto}` : texto;
}

export function formatFecha(fechaIso) {
    if (!fechaIso) return '-';
    const [y, m, d] = fechaIso.slice(0, 10).split('-');
    if (!y || !m || !d) return fechaIso;
    return `${d}/${m}/${y}`;
}

export function hoyIso() {
    const d = new Date();
    const mes = String(d.getMonth() + 1).padStart(2, '0');
    const dia = String(d.getDate()).padStart(2, '0');
    return `${d.getFullYear()}-${mes}-${dia}`;
}

export const TIPO_COLOR = {
    ingreso: '#16a34a',
    gasto: '#e11d48',
};
