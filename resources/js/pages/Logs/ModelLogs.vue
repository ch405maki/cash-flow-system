<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import LogDetails from './LogDetails.vue';
import { ref, computed } from 'vue';

interface Log {
    id: number;
    description: string;
    log_name: string; // Add log name
    subject_type: string;
    subject_id: number;
    causer: {
        name: string;
        email: string;
    } | null;
    subject: {
        id: number;
        name?: string;
        title?: string;
        request_no?: string;
    } | null;
    properties: {
        action?: string;
        event?: string;
        [key: string]: unknown;
    };
    created_at: string;
}

interface Model {
    id: number;
    name?: string;
    request_no?: string;
}

interface Props {
    logs: Log[]; // Changed from paginated structure to simple array
    model: Model;
    modelType: string;
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const selectedLog = ref<Log | null>(null);

const modelTitle = computed(() => {
    if (props.modelType === 'Request' && props.model.request_no) {
        return props.model.request_no;
    }
    return props.model.name || `#${props.model.id}`;
});
</script>

<template>
    <Head :title="`${modelType} Activity`" />

    <AppLayout>
        <div class="flex flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">
                    Activity for 
                    <span class="text-primary">
                        {{ modelType }} {{ modelTitle }}
                    </span>
                </h1>
                <Button as-child>
                    <Link :href="route('logs.index')">Back to All Logs</Link>
                </Button>
            </div>

            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Log Name</TableHead>
                            <TableHead>Event</TableHead>
                            <TableHead>Action</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>User</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Details</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in logs" :key="log.id">
                            <TableCell class="font-medium capitalize">
                                {{ log.description.split(' ')[0] }}
                            </TableCell>
                            <TableCell>{{ log.description }}</TableCell>
                            <TableCell>
                                {{ log.causer?.name || 'System' }}
                            </TableCell>
                            <TableCell>
                                {{ new Date(log.created_at).toLocaleString() }}
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="selectedLog = log"
                                >
                                    View
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>
        </div>
    </AppLayout>

    <LogDetails 
        :log="selectedLog" 
        @close="selectedLog = null" 
    />
</template>