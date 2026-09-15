<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-slate-100 px-4 py-10">
        <Link href="/" class="flex items-center gap-2 text-xl font-extrabold text-ink-900">
            💰 Control de Gastos
        </Link>
        <p class="mt-1 text-sm text-gray-500">Tus finanzas personales, siempre a la mano</p>

        <div class="mt-6 w-full max-w-md rounded-2xl border border-gray-200/80 bg-white p-8 shadow-sm shadow-gray-200/50">
            <slot />
        </div>
    </div>
</template>
