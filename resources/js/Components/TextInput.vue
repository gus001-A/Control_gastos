<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="w-full rounded-lg border-ink-200 text-sm text-ink-800 shadow-sm transition focus:border-brand-500 focus:ring-brand-500"
        v-model="model"
        ref="input"
    />
</template>
