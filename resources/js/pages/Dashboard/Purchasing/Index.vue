<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/purchasing/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/purchasing/RecentRequestsTable.vue';
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
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <h1 class="text-lg font-medium">
                Purchasing Dashboard
                <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
            </h1>
            
            <StatsCards :status-counts="statusCounts" />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 py-4">
                <!-- First Column -->
                <div class="space-y-4">
                    <h3 class="font-medium text-gray-900">
                        Recent Approved Requests
                        <p class="text-sm text-gray-500">For (purchase order / canvas)</p>
                    </h3>
                    <RecentRequestsTable 
                        :is-department-user="isDepartmentUser" 
                        :recent-requests="recentRequests" 
                        class="mt-2"
                    />
                </div>
                                
                <!-- Second Column -->
                <div>
                    <h3 class="font-medium text-gray-900">Most Requested Items</h3>
                    <p class="text-sm text-gray-500">Chart for most number of request</p>
                    <FrequentItemsChart :items="frequentItems" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>