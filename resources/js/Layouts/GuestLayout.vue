<script setup>
import { Link } from '@inertiajs/vue3';
import { Wallet } from '@lucide/vue';
</script>

<template>
    <div class="relative flex min-h-screen flex-col items-center justify-center overflow-hidden bg-ink-950 px-4 py-10">
        <div class="pointer-events-none absolute inset-0 opacity-40">
            <div class="absolute -left-24 -top-24 h-72 w-72 rounded-full bg-brand-700 blur-3xl" />
            <div class="absolute -right-24 bottom-0 h-72 w-72 rounded-full bg-brand-900 blur-3xl" />
        </div>

        <Link href="/" class="relative z-10 flex animate-fade-in items-center gap-2.5">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-brand-500 to-brand-700 shadow-glow">
                <Wallet :size="20" class="text-white" stroke-width="2.25" />
            </div>
            <span class="text-xl font-extrabold tracking-tight text-white">Control de Gastos</span>
        </Link>
        <p class="relative z-10 mt-1.5 text-sm text-ink-300">Tus finanzas personales, siempre a la mano</p>

        <div class="relative z-10 mt-6 w-full max-w-md animate-scale-in rounded-2xl border border-white/10 bg-white p-8 shadow-2xl">
            <slot />
        </div>
    </div>
</template>
