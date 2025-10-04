<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
        role="img"
        aria-hidden="false"
        aria-label="star icon"
    >
        <!-- Icono: estrella de 5 puntas -->
        <polygon
            fill="currentColor"
            points="20,3 25.6,15.1 38.8,15.6 28.6,24.3 32.2,36.7 20,29.2 7.8,36.7 11.4,24.3 1.2,15.6 14.4,15.1"
        />
    </svg>
</template>
