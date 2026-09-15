<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import FlashMessages from '@/Components/FlashMessages.vue';

defineProps({
    title: { type: String, default: '' },
    subtitle: { type: String, default: '' },
});

const page = usePage();
const menuAbierto = ref(false);

const NAV = [
    { key: 'dashboard', label: 'Inicio', icon: '🏠', route: 'dashboard' },
    { key: 'cuentas', label: 'Cuentas', icon: '💳', route: 'cuentas.index' },
    { key: 'movimientos', label: 'Movimientos', icon: '🧾', route: 'movimientos.index' },
    { key: 'deudas', label: 'Deudas', icon: '📉', route: 'deudas.index' },
    { key: 'proyectos', label: 'Proyectos', icon: '💼', route: 'proyectos.index' },
    { key: 'reportes', label: 'Reportes', icon: '📊', route: 'reportes.index' },
];

const esActivo = (nombreRuta) => route().current(nombreRuta) || route().current(nombreRuta.split('.')[0] + '.*');
</script>

<template>
    <div class="min-h-screen bg-slate-100">
        <!-- Sidebar desktop -->
        <aside class="fixed inset-y-0 left-0 z-30 hidden w-64 flex-col bg-ink-900 lg:flex">
            <div class="px-6 pb-2 pt-7">
                <p class="text-lg font-extrabold text-white">💰 Control de Gastos</p>
                <p class="mt-0.5 text-xs text-slate-400">Tus finanzas personales</p>
            </div>
            <nav class="mt-4 flex-1 space-y-1 px-3">
                <Link
                    v-for="item in NAV"
                    :key="item.key"
                    :href="route(item.route)"
                    class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                    :class="esActivo(item.route)
                        ? 'bg-white/10 text-white border-l-[3px] border-brand-500 pl-[9px]'
                        : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                >
                    <span class="text-base">{{ item.icon }}</span>
                    {{ item.label }}
                </Link>
            </nav>
            <div class="border-t border-white/10 p-4">
                <Link :href="route('profile.edit')" class="block rounded-lg px-2 py-2 text-sm text-slate-300 hover:bg-white/5 hover:text-white">
                    <div class="truncate font-semibold text-white">{{ page.props.auth.user.name }}</div>
                    <div class="truncate text-xs text-slate-400">{{ page.props.auth.user.email }}</div>
                </Link>
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="mt-2 flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs font-semibold text-slate-400 hover:bg-white/5 hover:text-white"
                >
                    ⏻ Cerrar sesión
                </Link>
            </div>
        </aside>

        <!-- Sidebar mobile (drawer) -->
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-if="menuAbierto" class="fixed inset-0 z-40 bg-black/50 lg:hidden" @click="menuAbierto = false" />
        </Transition>
        <Transition
            enter-active-class="transition ease-out duration-200"
            enter-from-class="-translate-x-full"
            enter-to-class="translate-x-0"
            leave-active-class="transition ease-in duration-150"
            leave-from-class="translate-x-0"
            leave-to-class="-translate-x-full"
        >
            <aside v-if="menuAbierto" class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-ink-900 lg:hidden">
                <div class="flex items-center justify-between px-6 pb-2 pt-7">
                    <div>
                        <p class="text-lg font-extrabold text-white">💰 Control de Gastos</p>
                        <p class="mt-0.5 text-xs text-slate-400">Tus finanzas personales</p>
                    </div>
                    <button class="text-slate-400" @click="menuAbierto = false">✕</button>
                </div>
                <nav class="mt-4 flex-1 space-y-1 px-3">
                    <Link
                        v-for="item in NAV"
                        :key="item.key"
                        :href="route(item.route)"
                        @click="menuAbierto = false"
                        class="flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition"
                        :class="esActivo(item.route)
                            ? 'bg-white/10 text-white border-l-[3px] border-brand-500 pl-[9px]'
                            : 'text-slate-300 hover:bg-white/5 hover:text-white'"
                    >
                        <span class="text-base">{{ item.icon }}</span>
                        {{ item.label }}
                    </Link>
                </nav>
                <div class="border-t border-white/10 p-4">
                    <Link
                        :href="route('logout')"
                        method="post"
                        as="button"
                        class="flex w-full items-center gap-2 rounded-lg px-2 py-2 text-left text-xs font-semibold text-slate-400 hover:bg-white/5 hover:text-white"
                    >
                        ⏻ Cerrar sesión
                    </Link>
                </div>
            </aside>
        </Transition>

        <div class="lg:pl-64">
            <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-gray-200 bg-white/80 px-4 py-3 backdrop-blur lg:hidden">
                <button class="rounded-md p-1.5 text-ink-800 hover:bg-gray-100" @click="menuAbierto = true">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                </button>
                <span class="font-bold text-ink-900">💰 Control de Gastos</span>
            </header>

            <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 lg:py-8">
                <div v-if="title" class="mb-6">
                    <h1 class="text-2xl font-extrabold text-ink-900">{{ title }}</h1>
                    <p v-if="subtitle" class="mt-0.5 text-sm text-gray-500">{{ subtitle }}</p>
                </div>
                <slot />
            </main>
        </div>

        <FlashMessages />
    </div>
</template>
