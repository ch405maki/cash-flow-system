<script setup lang="ts">
import { useAppearance } from '@/composables/useAppearance';
import { Moon, Sun } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    class?: string;
}

const { class: containerClass = '' } = defineProps<Props>();

const { appearance, updateAppearance } = useAppearance();

const isDark = computed(() => {
  return appearance.value === 'dark' || 
         (appearance.value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
});

const toggleAppearance = () => {
  updateAppearance(isDark.value ? 'light' : 'dark');
};
</script>

<template>
    <button
        @click="toggleAppearance"
        :class="[
            'group inline-flex items-center rounded-lg p-2 transition-all duration-300',
            'bg-neutral-100 hover:bg-amber-50 dark:bg-neutral-800 dark:hover:bg-indigo-900/50',
            'ring-1 ring-neutral-200 dark:ring-neutral-700',
            'hover:ring-amber-200 dark:hover:ring-indigo-700',
            containerClass
        ]"
        :aria-label="isDark ? 'Switch to light mode' : 'Switch to dark mode'"
        title="Toggle appearance"
    >
        <component 
            :is="isDark ? Sun : Moon" 
            :class="[
                'h-4 w-4 transition-all duration-500',
                {
                    'text-amber-500 group-hover:text-amber-600': !isDark,
                    'text-indigo-400 group-hover:text-indigo-300': isDark
                }
            ]" 
        />
    </button>
</template>