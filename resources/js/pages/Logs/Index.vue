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
import { Input } from '@/components/ui/input'
import { Search } from 'lucide-vue-next';
import { Pagination } from '@/components/Pagination';
import LogDetails from './LogDetails.vue';
import { ref } from 'vue';

interface Log {
    id: number;
    description: string;
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
    properties: Record<string, unknown>;
    created_at: string;
}

interface Props {
    logs: {
        data: Log[];
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
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
        <!-- Your table and other content -->
        <Table>
            <!-- Table headers -->
            <TableBody>
                <TableRow v-for="log in logs.data" :key="log.id">
                    <!-- Table cells -->
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
    </AppLayout>

    <LogDetails 
        :log="selectedLog" 
        @close="selectedLog = null" 
    />
</template>