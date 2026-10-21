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
        class="h-10 w-full rounded-lg border-neutral-200 text-sm text-neutral-800 shadow-sm transition placeholder:text-neutral-400 hover:border-neutral-300 focus:border-brand-500 focus:ring-2 focus:ring-brand-200"
        v-model="model"
        ref="input"
    />
</template>
