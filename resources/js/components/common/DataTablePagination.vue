<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface Props {
    currentPage: number;
    totalPages: number;
    hasPrev: boolean;
    hasNext: boolean;
}

defineProps<Props>();
const emit = defineEmits<{
    'prev': [];
    'next': [];
    'go-to': [page: number];
}>();
</script>

<template>
    <div v-if="totalPages > 1" class="flex items-center justify-between mt-4">
        <p class="text-sm text-muted-foreground">
            Page {{ currentPage }} of {{ totalPages }}
        </p>
        <div class="flex gap-2">
            <Button
                variant="outline"
                size="sm"
                :disabled="!hasPrev"
                @click="emit('prev')"
            >
                <ChevronLeft class="h-4 w-4 mr-1" /> Previous
            </Button>
            <Button
                variant="outline"
                size="sm"
                :disabled="!hasNext"
                @click="emit('next')"
            >
                Next <ChevronRight class="h-4 w-4 ml-1" />
            </Button>
        </div>
    </div>
</template>
