<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Iniciar sesión" />

        <h1 class="mb-6 text-lg font-bold text-ink-900">Iniciar sesión</h1>

        <div v-if="status" class="mb-4 text-sm font-medium text-brand-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Correo electrónico" />
                <TextInput id="email" type="email" class="mt-1" v-model="form.email" required autofocus autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Contraseña" />
                <PasswordInput id="password" class="mt-1" v-model="form.password" required autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4 block">
                <label class="flex items-center">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span class="ms-2 text-sm text-gray-600">Recordarme</span>
                </label>
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link v-if="canResetPassword" :href="route('password.request')" class="text-sm text-gray-500 underline hover:text-ink-900">
                    ¿Olvidaste tu contraseña?
                </Link>
                <PrimaryButton class="ms-auto" :disabled="form.processing">Entrar</PrimaryButton>
            </div>

            <p class="mt-6 text-center text-sm text-gray-500">
                ¿No tienes cuenta?
                <Link :href="route('register')" class="font-semibold text-brand-700 hover:underline">Regístrate</Link>
            </p>
        </form>
    </GuestLayout>
</template>
