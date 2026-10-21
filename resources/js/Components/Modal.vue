<script setup>
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
import { X } from '@lucide/vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const emit = defineEmits(['close']);
const dialog = ref();
const showSlot = ref(props.show);

watch(
    () => props.show,
    () => {
        if (props.show) {
            document.body.style.overflow = 'hidden';
            showSlot.value = true;

            dialog.value?.showModal();
        } else {
            document.body.style.overflow = '';

            setTimeout(() => {
                dialog.value?.close();
                showSlot.value = false;
            }, 200);
        }
    },
);

const close = () => {
    if (props.closeable) {
        emit('close');
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape') {
        e.preventDefault();

        if (props.show) {
            close();
        }
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));

onUnmounted(() => {
    document.removeEventListener('keydown', closeOnEscape);

    document.body.style.overflow = '';
});

const maxWidthClass = computed(() => {
    return {
        sm: 'sm:max-w-sm',
        md: 'sm:max-w-md',
        lg: 'sm:max-w-lg',
        xl: 'sm:max-w-xl',
        '2xl': 'sm:max-w-2xl',
    }[props.maxWidth];
});
</script>

<template>
    <dialog
        class="z-50 m-0 min-h-full min-w-full overflow-y-auto bg-transparent backdrop:bg-transparent"
        ref="dialog"
    >
        <Transition
            enter-active-class="ease-[cubic-bezier(0.2,0.9,0.3,1.2)] duration-300"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div v-show="show" class="fixed inset-0 z-50 bg-neutral-950/60 backdrop-blur-sm" @click="close" />
        </Transition>

        <div class="fixed inset-0 z-50 flex min-h-full items-center justify-center overflow-y-auto p-4" scroll-region>
            <Transition
                enter-active-class="ease-[cubic-bezier(0.2,0.9,0.3,1.2)] duration-300"
                enter-from-class="opacity-0 translate-y-6 scale-90"
                enter-to-class="opacity-100 translate-y-0 scale-100"
                leave-active-class="ease-in duration-200"
                leave-from-class="opacity-100 translate-y-0 scale-100"
                leave-to-class="opacity-0 translate-y-6 scale-90"
            >
                <div
                    v-show="show"
                    class="relative my-8 w-full transform overflow-hidden rounded-2xl bg-white shadow-2xl shadow-purple-950/30 ring-1 ring-purple-900/10 transition-all"
                    :class="maxWidthClass"
                >
                    <button
                        v-if="closeable"
                        type="button"
                        class="absolute right-4 top-4 z-10 rounded-lg p-1.5 text-neutral-400 transition hover:bg-neutral-100 hover:text-neutral-700"
                        @click="close"
                    >
                        <X :size="18" />
                    </button>
                    <slot v-if="showSlot" />
                </div>
            </Transition>
        </div>
    </dialog>
</template>
