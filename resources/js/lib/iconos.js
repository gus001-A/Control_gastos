import {
    Wallet, Landmark, PiggyBank, CreditCard, TrendingUp, Layers, UtensilsCrossed, Car, Home, Zap,
    HeartPulse, Clapperboard, Shirt, Sparkles, Package, Briefcase, Laptop, ShoppingBag, Coins, Tag,
} from '@lucide/vue';

export const ICONOS_CUENTA = {
    efectivo: Wallet,
    banco: Landmark,
    ahorro: PiggyBank,
    tarjeta_credito: CreditCard,
    tarjeta_debito: CreditCard,
    inversion: TrendingUp,
    otro: Layers,
};

export const ETIQUETAS_CUENTA = {
    efectivo: 'Efectivo',
    banco: 'Banco',
    ahorro: 'Ahorro',
    tarjeta_credito: 'Crédito',
    tarjeta_debito: 'Débito',
    inversion: 'Inversión',
    otro: 'Otro',
};

// Las categorías son libres, así que el icono se elige por palabras clave del nombre.
const ICONOS_CATEGORIA = [
    [/comida|despensa|super|restaurante/i, UtensilsCrossed],
    [/transporte|gasolina|auto|uber/i, Car],
    [/renta|hogar|casa/i, Home],
    [/servicio|luz|agua|internet/i, Zap],
    [/salud|m[eé]dic|farmacia/i, HeartPulse],
    [/entretenimiento|cine|ocio/i, Clapperboard],
    [/vestimenta|ropa/i, Shirt],
    [/personal/i, Sparkles],
    [/deuda/i, CreditCard],
    [/sueldo|n[oó]mina/i, Briefcase],
    [/freelance/i, Laptop],
    [/venta/i, ShoppingBag],
    [/otros ingresos/i, Coins],
    [/otros/i, Package],
];

export function iconoCategoria(nombre = '') {
    return ICONOS_CATEGORIA.find(([patron]) => patron.test(nombre))?.[1] ?? Tag;
}
