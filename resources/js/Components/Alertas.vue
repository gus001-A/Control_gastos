<script setup>
import { onMounted, onUnmounted } from 'vue';
import { estadoAlertas, cerrarToast, responderConfirmacion } from '@/lib/alertas';
import { CheckCircle2, XCircle, AlertTriangle, X, Trash2, HelpCircle } from '@lucide/vue';

const ESTILOS = {
    exito: { icon: CheckCircle2, titulo: '¡Listo!', caja: 'from-purple-500 to-purple-800', barra: 'bg-purple-500' },
    error: { icon: XCircle, titulo: 'Algo salió mal', caja: 'from-rose-500 to-rose-700', barra: 'bg-rose-500' },
    advertencia: { icon: AlertTriangle, titulo: 'Atención', caja: 'from-neutral-700 to-neutral-950', barra: 'bg-neutral-900' },
};

function alTeclear(e) {
    if (!estadoAlertas.confirmacion) return;
    if (e.key === 'Escape') responderConfirmacion(false);
    if (e.key === 'Enter') responderConfirmacion(true);
}

onMounted(() => document.addEventListener('keydown', alTeclear));
onUnmounted(() => document.removeEventListener('keydown', alTeclear));
</script>

<template>
    <!-- Notificaciones -->
    <div class="pointer-events-none fixed inset-x-0 top-4 z-[60] flex flex-col items-center gap-2 px-4 sm:inset-x-auto sm:right-4 sm:items-end">
        <TransitionGroup
            enter-active-class="transition duration-500 ease-[cubic-bezier(0.2,0.9,0.3,1.2)]"
            enter-from-class="opacity-0 translate-x-8 scale-90"
            leave-active-class="transition duration-200 ease-in absolute"
            leave-to-class="opacity-0 translate-x-8"
            move-class="transition duration-300"
        >
            <div
                v-for="t in estadoAlertas.toasts"
                :key="t.id"
                class="pointer-events-auto relative w-full max-w-sm overflow-hidden rounded-2xl border border-neutral-800 bg-neutral-950 text-white shadow-2xl shadow-purple-950/40"
                role="status"
            >
                <div class="flex items-start gap-3 p-4 pr-10">
                    <span
                        class="flex h-9 w-9 shrink-0 animate-scale-in items-center justify-center rounded-xl bg-gradient-to-br text-white shadow-lg"
                        :class="ESTILOS[t.tipo].caja"
                    >
                        <component :is="ESTILOS[t.tipo].icon" :size="18" />
                    </span>
                    <div class="min-w-0 pt-0.5">
                        <p class="text-sm font-bold">{{ ESTILOS[t.tipo].titulo }}</p>
                        <p class="mt-0.5 text-[13px] leading-snug text-neutral-300">{{ t.texto }}</p>
                    </div>
                </div>
                <button
                    class="absolute right-2.5 top-2.5 rounded-lg p-1 text-neutral-500 transition hover:bg-white/10 hover:text-white"
                    title="Cerrar"
                    @click="cerrarToast(t.id)"
                >
                    <X :size="15" />
                </button>
                <!-- Tiempo restante -->
                <span
                    class="barra-tiempo absolute bottom-0 left-0 h-0.5 w-full origin-left"
                    :class="ESTILOS[t.tipo].barra"
                    :style="{ animationDuration: `${t.duracion}ms` }"
                ></span>
            </div>
        </TransitionGroup>
    </div>

    <!-- Confirmación -->
    <Transition
        enter-active-class="transition duration-200 ease-out"
        enter-from-class="opacity-0"
        leave-active-class="transition duration-150 ease-in"
        leave-to-class="opacity-0"
    >
        <div
            v-if="estadoAlertas.confirmacion"
            class="fixed inset-0 z-[70] flex items-center justify-center bg-neutral-950/60 p-4 backdrop-blur-sm"
            @click.self="responderConfirmacion(false)"
        >
            <div
                class="w-full max-w-sm animate-scale-in overflow-hidden rounded-3xl bg-white text-center shadow-2xl shadow-purple-950/40"
                role="alertdialog"
                aria-modal="true"
            >
                <div class="relative px-6 pb-6 pt-8">
                    <div class="pointer-events-none absolute -top-16 left-1/2 h-40 w-40 -translate-x-1/2 rounded-full blur-3xl"
                        :class="estadoAlertas.confirmacion.danger ? 'bg-rose-400/30' : 'bg-purple-400/30'"
                    ></div>
                    <span
                        class="relative mx-auto flex h-16 w-16 items-center justify-center rounded-2xl text-white shadow-xl"
                        :class="estadoAlertas.confirmacion.danger
                            ? 'bg-gradient-to-br from-rose-500 to-neutral-950 shadow-rose-900/30'
                            : 'bg-gradient-to-br from-purple-500 to-neutral-950 shadow-purple-900/30'"
                    >
                        <span class="absolute inset-0 animate-ping rounded-2xl opacity-20"
                            :class="estadoAlertas.confirmacion.danger ? 'bg-rose-500' : 'bg-purple-500'"
                        ></span>
                        <component :is="estadoAlertas.confirmacion.danger ? Trash2 : HelpCircle" :size="28" class="relative animate-wiggle" />
                    </span>
                    <h3 class="relative mt-5 text-lg font-bold tracking-tight text-neutral-950">
                        {{ estadoAlertas.confirmacion.title }}
                    </h3>
                    <p v-if="estadoAlertas.confirmacion.content" class="relative mt-1.5 text-sm text-neutral-500">
                        {{ estadoAlertas.confirmacion.content }}
                    </p>
                </div>
                <div class="grid grid-cols-2 gap-2 border-t border-neutral-100 bg-neutral-50/80 p-3">
                    <button
                        class="h-11 rounded-xl border border-neutral-200 bg-white text-sm font-semibold text-neutral-700 transition-all duration-200 hover:border-neutral-300 hover:bg-neutral-100 active:scale-95"
                        @click="responderConfirmacion(false)"
                    >
                        Cancelar
                    </button>
                    <button
                        class="shine h-11 rounded-xl text-sm font-semibold text-white shadow-md transition-all duration-200 hover:-translate-y-0.5 active:scale-95"
                        :class="estadoAlertas.confirmacion.danger
                            ? 'bg-gradient-to-br from-rose-500 to-rose-700 shadow-rose-900/30 hover:shadow-lg hover:shadow-rose-700/40'
                            : 'bg-gradient-to-br from-purple-600 to-neutral-950 shadow-purple-900/30 hover:shadow-lg hover:shadow-purple-700/40'"
                        @click="responderConfirmacion(true)"
                    >
                        {{ estadoAlertas.confirmacion.okText }}
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<style scoped>
.barra-tiempo {
    animation-name: consumir;
    animation-timing-function: linear;
    animation-fill-mode: forwards;
}

@keyframes consumir {
    from { transform: scaleX(1); }
    to { transform: scaleX(0); }
}
</style>
