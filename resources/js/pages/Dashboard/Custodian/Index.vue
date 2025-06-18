<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/custodian/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/custodian/RecentRequestsTable.vue';
import FrequentItemsChart from '@/components/dashboard/purchasing/FrequentItemsChart.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    recentRequests: Array<any>;
    statusCounts: {
        pending: number;
        approved: number;
        to_order: number;
        rejected: number;
    };
    frequentItems: Array<{
        item_description: string;
        total_quantity: number;
        request_count: number;
        unit?: string;
    }>;
    userRole: string;
    username: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 space-y-4">
            <div>
                <h1 class="text-lg font-medium mb-2">
                    Property Custodian Dashboard
                    <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
                </h1>
                <StatsCards :status-counts="statusCounts" />
            </div>
            
            <div>
                <h1 class="text-lg font-medium">Recent Requests</h1>
                <RecentRequestsTable 
                    :is-department-user="isDepartmentUser" 
                    :recent-requests="recentRequests" 
                />
            </div>

            <div>
                <h1 class="text-lg font-medium">Most Requested Items</h1>
                <p class="text-sm text-gray-500">Chart for most number of request</p>
                <FrequentItemsChart :items="frequentItems" />
            </div>
        </div>
    </AppLayout>
</template>