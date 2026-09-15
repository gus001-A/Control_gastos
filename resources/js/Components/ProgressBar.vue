<script setup>
defineProps({
    value: { type: Number, required: true },
    color: { type: String, default: '#9333ea' },
});
</script>

<template>
    <div class="h-2.5 w-full overflow-hidden rounded-full bg-ink-100">
        <div
            class="h-full rounded-full transition-all duration-500 ease-out"
            :style="{ width: Math.min(100, Math.max(0, value)) + '%', backgroundColor: color }"
        />
    </div>
</template>
