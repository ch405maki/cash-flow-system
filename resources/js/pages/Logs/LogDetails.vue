<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'

// Define the log data structure
interface Log {
    description: string;
    properties: Record<string, unknown>;
    causer: {
        name: string;
        email: string;
    } | null;
    created_at: string;
}

// Props - takes a single log entry or null when closed
const props = defineProps<{
    log: Log | null;
}>();

// Emits - notifies parent when dialog should close
const emit = defineEmits(['close']);
</script>

<template>
    <!-- Dialog component that shows when log prop has a value -->
    <Dialog :open="!!log" @update:open="(val) => !val && emit('close')">
        <DialogContent class="max-w-2xl">
            <DialogHeader>
                <DialogTitle>Activity Details</DialogTitle>
            </DialogHeader>

            <!-- Content shown when log exists -->
            <div v-if="log" class="space-y-4">
                <!-- Description section -->
                <div>
                    <h3 class="font-medium">Description</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ log.description }}
                    </p>
                </div>

                <!-- Who performed the action -->
                <div>
                    <h3 class="font-medium">Performed By</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ log.causer?.name || 'System' }}
                        <span v-if="log.causer?.email">({{ log.causer.email }})</span>
                    </p>
                </div>

                <!-- When it happened -->
                <div>
                    <h3 class="font-medium">Date</h3>
                    <p class="text-sm text-muted-foreground">
                        {{ new Date(log.created_at).toLocaleString() }}
                    </p>
                </div>

                <!-- Raw properties data -->
                <div>
                    <h3 class="font-medium">Details</h3>
                    <pre class="max-h-96 overflow-auto rounded bg-muted p-4 text-sm">
                        {{ JSON.stringify(log.properties, null, 2) }}
                    </pre>
                </div>
            </div>
        </DialogContent>
    </Dialog>
</template>