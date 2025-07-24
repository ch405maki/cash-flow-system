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
import { Search, X, Calendar } from 'lucide-vue-next';
import LogDetails from './LogDetails.vue';
import { ref, computed } from 'vue';
import { Pagination, PaginationContent, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination'
import { Calendar as CalendarComponent } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

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
    logs: {
        data: Log[];
        links: {
            url: string | null;
            label: string;
            active: boolean;
        }[];
        current_page: number;
        last_page: number;
        prev_page_url: string | null;
        next_page_url: string | null;
        from: number;
        to: number;
        total: number;
    };
    filters?: {
        search?: string;
    };
}

const props = defineProps<Props>();
const selectedLog = ref<Log | null>(null);
const searchQuery = ref(props.filters?.search || '');
const dateFilter = ref<Date | undefined>();

const filteredLogs = computed(() => {
    if (!props.logs?.data) return [];
    
    let logs = [...props.logs.data];
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        logs = logs.filter(log => (
            log.description.toLowerCase().includes(query) ||
            (log.log_name?.toLowerCase().includes(query) ?? false) ||
            (log.causer?.username.toLowerCase().includes(query) ?? false) ||
            (log.causer?.email.toLowerCase().includes(query) ?? false) ||
            (log.subject?.name?.toLowerCase().includes(query) ?? false) ||
            (log.subject?.title?.toLowerCase().includes(query) ?? false) ||
            (log.subject?.request_no?.toLowerCase().includes(query) ?? false) ||
            (log.properties.event?.toLowerCase().includes(query) ?? false) ||
            (log.properties.action?.toLowerCase().includes(query) ?? false)
        ));
    }

    if (dateFilter.value) {
        const filterDate = new Date(dateFilter.value);
        logs = logs.filter(log => {
            const logDate = new Date(log.created_at);
            return (
                logDate.getFullYear() === filterDate.getFullYear() &&
                logDate.getMonth() === filterDate.getMonth() &&
                logDate.getDate() === filterDate.getDate()
            );
        });
    }

    return logs;
});

const clearSearch = () => {
    searchQuery.value = '';
};

const clearDateFilter = () => {
    dateFilter.value = undefined;
};
</script>

<template>
    <Head title="Activity Log" />

    <AppLayout>
        <div class="space-y-4 p-4">
            <!-- Filters Row -->
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Search Filter -->
                <div class="relative w-full md:w-1/3">
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

                <!-- Date Filter -->
                <div class="w-full md:w-1/3">
                    <Popover>
                        <PopoverTrigger as-child>
                            <Button
                                variant="outline"
                                class="w-full justify-start text-left font-normal"
                                :class="!dateFilter ? 'text-muted-foreground' : ''"
                            >
                                <Calendar class="mr-2 h-4 w-4" />
                                {{ dateFilter ? formatDateTime(dateFilter) : 'Filter by date...' }}
                                <X
                                    v-if="dateFilter"
                                    class="ml-auto h-4 w-4 text-muted-foreground cursor-pointer hover:text-foreground"
                                    @click.stop="clearDateFilter"
                                />
                            </Button>
                        </PopoverTrigger>
                        <PopoverContent class="w-auto p-0">
                            <CalendarComponent
                                v-model="dateFilter"
                                mode="single"
                                initial-focus
                            />
                        </PopoverContent>
                    </Popover>
                </div>
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
                        <template v-if="filteredLogs.length > 0">
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
                        </template>
                        <template v-else>
                            <TableRow>
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center space-y-2">
                                        <Search class="h-8 w-8" />
                                        <p>No logs found matching your criteria</p>
                                        <Button variant="ghost" size="sm" @click="[clearSearch(), clearDateFilter()]">
                                            Clear all filters
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>
            </div>

            <!-- Pagination -->
            <Pagination >
                <PaginationContent class="space-x-4">
                    <template v-for="(link, index) in logs.links" :key="index">
                    <PaginationItem v-if="link.url">
                        <a
                        :href="link.url"
                        class="px-3 py-1.5 text-sm rounded-md"
                        :class="{
                            'bg-primary text-primary-foreground': link.active,
                            'hover:bg-accent hover:text-accent-foreground': !link.active,
                            'text-muted-foreground': !link.active,
                        }"
                        v-html="link.label"
                        />
                    </PaginationItem>
                    <PaginationItem v-else>
                        <span class="px-3 py-1.5 text-sm text-muted-foreground" v-html="link.label" />
                    </PaginationItem>
                    </template>
                </PaginationContent>
            </Pagination>
        </div>
    </AppLayout>

    <LogDetails 
        :log="selectedLog" 
        @close="selectedLog = null" 
    />
</template>