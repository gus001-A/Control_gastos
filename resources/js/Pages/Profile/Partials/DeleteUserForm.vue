<script setup>
import DangerButton from '@/Components/DangerButton.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import PasswordInput from '@/Components/PasswordInput.vue';
import { useForm } from '@inertiajs/vue3';
import { nextTick, ref } from 'vue';

const confirmingUserDeletion = ref(false);
const passwordInput = ref(null);

const form = useForm({
    password: '',
});

const confirmUserDeletion = () => {
    confirmingUserDeletion.value = true;

    nextTick(() => passwordInput.value.focus());
};

const deleteUser = () => {
    form.delete(route('profile.destroy'), {
        preserveScroll: true,
        onSuccess: () => closeModal(),
        onError: () => passwordInput.value.focus(),
        onFinish: () => form.reset(),
    });
};

const closeModal = () => {
    confirmingUserDeletion.value = false;

    form.clearErrors();
    form.reset();
};
</script>

<template>
    <section class="space-y-4">
        <header>
            <h2 class="text-base font-bold text-ink-900">Eliminar cuenta</h2>
            <p class="mt-1 text-sm text-ink-400">
                Una vez que elimines tu cuenta, todos tus datos (cuentas, movimientos, deudas, proyectos) se borran para siempre.
                Descarga cualquier información que quieras conservar antes de continuar.
            </p>
        </header>

        <DangerButton @click="confirmUserDeletion">Eliminar cuenta</DangerButton>

        <Modal :show="confirmingUserDeletion" @close="closeModal">
            <div class="p-6">
                <h2 class="text-base font-bold text-ink-900">¿Seguro que quieres eliminar tu cuenta?</h2>

                <p class="mt-1 text-sm text-ink-400">
                    Esta acción no se puede deshacer. Escribe tu contraseña para confirmar.
                </p>

                <div class="mt-6">
                    <InputLabel for="password" value="Contraseña" class="sr-only" />
                    <PasswordInput
                        id="password"
                        ref="passwordInput"
                        v-model="form.password"
                        class="mt-1"
                        autocomplete="current-password"
                        placeholder="Contraseña"
                        @keyup.enter="deleteUser"
                    />
                    <InputError :message="form.errors.password" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="closeModal">Cancelar</SecondaryButton>
                    <DangerButton :disabled="form.processing" @click="deleteUser">Eliminar cuenta</DangerButton>
                </div>
            </div>
        </Modal>
    </section>
</template>
