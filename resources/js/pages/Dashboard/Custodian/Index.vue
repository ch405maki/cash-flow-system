<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/custodian/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/custodian/RecentRequestsTable.vue';
import FrequentItemsChart from '@/components/dashboard/purchasing/FrequentItemsChart.vue';
import PageHeader from '@/components/PageHeader.vue';

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
            <PageHeader 
                class="capitalize"
                :title="`Welcome, ${username}`"
                subtitle="Property Custodian Dashboard"
            />

            <StatsCards :status-counts="statusCounts" />
            
            <PageHeader 
                title="Recent Requests"
            />
            <RecentRequestsTable 
                :is-department-user="isDepartmentUser" 
                :recent-requests="recentRequests" 
            />

            <PageHeader 
                title="Most Requested Items"
                subtitle="Chart for most number of request"
            />
            <FrequentItemsChart :items="frequentItems" />
        </div>
    </AppLayout>
</template>