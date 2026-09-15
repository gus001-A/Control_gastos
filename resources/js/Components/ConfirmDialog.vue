<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

defineProps({
    show: Boolean,
    title: { type: String, default: '¿Estás seguro?' },
    message: { type: String, default: '' },
    confirmLabel: { type: String, default: 'Eliminar' },
});

const emit = defineEmits(['confirm', 'cancel']);
</script>

<template>
    <Modal :show="show" max-width="sm" @close="emit('cancel')">
        <div class="p-6">
            <h2 class="text-base font-bold text-ink-900">{{ title }}</h2>
            <p class="mt-2 text-sm text-ink-500">{{ message }}</p>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="emit('cancel')">Cancelar</SecondaryButton>
                <DangerButton @click="emit('confirm')">{{ confirmLabel }}</DangerButton>
            </div>
        </div>
    </Modal>
</template>
