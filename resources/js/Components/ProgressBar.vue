<script setup>
defineProps({
    value: { type: Number, required: true },
    color: { type: String, default: '#16a34a' },
});
</script>

<template>
    <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-100">
        <div
            class="h-full rounded-full transition-all"
            :style="{ width: Math.min(100, Math.max(0, value)) + '%', backgroundColor: color }"
        />
    </div>
</template>
