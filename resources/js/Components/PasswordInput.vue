<script setup>
import { ref } from 'vue';
import { Eye, EyeOff } from '@lucide/vue';

defineOptions({ inheritAttrs: false });

const model = defineModel({ type: String, required: true });

const visible = ref(false);
const input = ref(null);
defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <div class="relative">
        <input
            ref="input"
            v-model="model"
            v-bind="$attrs"
            :type="visible ? 'text' : 'password'"
            class="w-full rounded-lg border-ink-200 pr-10 text-sm text-ink-800 shadow-sm transition focus:border-brand-500 focus:ring-brand-500"
        />
        <button
            type="button"
            tabindex="-1"
            class="absolute inset-y-0 right-0 flex w-10 items-center justify-center text-ink-300 transition hover:text-ink-600"
            @click="visible = !visible"
        >
            <EyeOff v-if="visible" :size="17" />
            <Eye v-else :size="17" />
        </button>
    </div>
</template>
