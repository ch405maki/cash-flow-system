<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { formatDateTime } from '@/lib/utils';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next';
import LogDetails from './LogDetails.vue';
import { ref } from 'vue';

interface Log {
    id: number;
    description: string;
    log_name: string;
    subject_type: string;
    subject_id: number;
    causer: {
        username: string;
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

interface Props {
    logs: Log[]; // Changed from paginated structure to simple array
    filters: {
        search?: string;
    };
}

const props = defineProps<Props>();
const selectedLog = ref<Log | null>(null);
</script>

<template>
    <Head title="Activity Log" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <!-- Search input if needed -->
            <div class="relative w-full max-w-md">
                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                    placeholder="Search logs..."
                    class="w-full pl-10"
                />
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
                        <TableRow v-for="log in logs" :key="log.id">
                            <TableCell class="font-medium capitalize">
                                {{ log.properties.event || log.properties.action || 'N/A' }}
                            </TableCell>
                            <TableCell>{{ log.description }}</TableCell>
                            <TableCell>
                                <p class="text-sm text-muted-foreground">
                                    {{ log.causer?.username || 'System' }}
                                    <span v-if="log.causer?.email">({{ log.causer.email }})</span>
                                </p>
                            </TableCell>
                            <TableCell>
                                {{ formatDateTime(log.created_at) }}
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