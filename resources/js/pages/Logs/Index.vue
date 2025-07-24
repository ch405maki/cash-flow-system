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
import { Search, X } from 'lucide-vue-next';
import LogDetails from './LogDetails.vue';
import { ref, computed } from 'vue';

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
    logs: Log[];
}

const props = defineProps<Props>();
const selectedLog = ref<Log | null>(null);
const searchQuery = ref('');

// Computed property for filtered logs
const filteredLogs = computed(() => {
    if (!searchQuery.value) return props.logs;
    
    const query = searchQuery.value.toLowerCase();
    return props.logs.filter(log => {
        return (
            log.description.toLowerCase().includes(query) ||
            (log.log_name?.toLowerCase().includes(query) ?? false) ||
            (log.causer?.username.toLowerCase().includes(query) ?? false) ||
            (log.causer?.email.toLowerCase().includes(query) ?? false) ||
            (log.subject?.name?.toLowerCase().includes(query) ?? false) ||
            (log.subject?.title?.toLowerCase().includes(query) ?? false) ||
            (log.subject?.request_no?.toLowerCase().includes(query) ?? false) ||
            (log.properties.event?.toLowerCase().includes(query) ?? false) ||
            (log.properties.action?.toLowerCase().includes(query) ?? false)
        );
    });
});

const clearSearch = () => {
    searchQuery.value = '';
};
</script>

<template>
    <Head title="Activity Log" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <!-- Client-side search filter -->
            <div class="relative w-full max-w-md">
                <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                    v-model="searchQuery"
                    placeholder="Filter logs by any field..."
                    class="w-full pl-10 pr-10"
                />
                <X
                    v-if="searchQuery"
                    class="absolute right-3 top-3 h-4 w-4 text-muted-foreground cursor-pointer hover:text-foreground"
                    @click="clearSearch"
                />
            </div>

            <!-- Logs Table -->
            <div class="rounded-md border">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead class="w-[120px]">Event</TableHead>
                            <TableHead>Description</TableHead>
                            <TableHead class="w-[200px]">User</TableHead>
                            <TableHead class="w-[180px]">Date</TableHead>
                            <TableHead class="w-[100px]">Details</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="log in filteredLogs" :key="log.id">
                            <TableCell class="font-medium capitalize">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary/10 text-primary">
                                    {{ log.properties.event || log.properties.action || 'system' }}
                                </span>
                            </TableCell>
                            <TableCell class="max-w-[300px] truncate">
                                {{ log.description }}
                            </TableCell>
                            <TableCell>
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium">{{ log.causer?.username || 'System' }}</span>
                                    <span v-if="log.causer?.email" class="text-xs text-muted-foreground">
                                        ({{ log.causer.email }})
                                    </span>
                                </div>
                            </TableCell>
                            <TableCell class="text-muted-foreground">
                                {{ formatDateTime(log.created_at) }}
                            </TableCell>
                            <TableCell>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    class="w-full"
                                    @click="selectedLog = log"
                                >
                                    View
                                </Button>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="filteredLogs.length === 0">
                            <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                <div class="flex flex-col items-center justify-center space-y-2">
                                    <Search class="h-8 w-8" />
                                    <p>No logs found matching "{{ searchQuery }}"</p>
                                    <Button variant="ghost" size="sm" @click="clearSearch">
                                        Clear filter
                                    </Button>
                                </div>
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