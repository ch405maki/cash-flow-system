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
import { Pagination } from '@/components/Pagination';
import LogDetails from './LogDetails.vue';
import { ref } from 'vue';

interface Log {
    id: number;
    description: string;
    properties: Record<string, unknown>;
    causer: {
        name: string;
        email: string;
    } | null;
    created_at: string;
}

interface Model {
    id: number;
    name?: string;
    request_no?: string;
    // Add other model-specific fields
}

interface Props {
    logs: {
        data: Log[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    model: Model;
    modelType: string;
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const selectedLog = ref<Log | null>(null);

// Format model title based on model type
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
                            <TableHead>Action</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead>User</TableHead>
                            <TableHead>Date</TableHead>
                            <TableHead>Details</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in logs.data" :key="log.id">
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

            <Pagination :links="logs.links" />
        </div>
    </AppLayout>

    <LogDetails 
        :log="selectedLog" 
        @close="selectedLog = null" 
    />
</template>