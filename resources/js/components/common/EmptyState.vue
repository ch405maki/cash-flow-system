<script setup lang="ts">
import { Inbox } from 'lucide-vue-next';

interface Props {
    title?: string;
    description?: string;
}

withDefaults(defineProps<Props>(), {
    title: 'No records found',
    description: 'There are no items to display at the moment.',
});
</script>

<template>
    <div class="flex flex-col items-center justify-center py-12 text-center">
        <Inbox class="h-12 w-12 text-muted-foreground mb-4" />
        <h3 class="text-lg font-medium">{{ title }}</h3>
        <p class="text-sm text-muted-foreground mt-1">{{ description }}</p>
        <slot />
    </div>
</template>
