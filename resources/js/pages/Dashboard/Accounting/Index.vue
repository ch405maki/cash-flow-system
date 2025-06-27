<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/department/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/accounting/RecentRequestsTable.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    purchaseOrders: Array<any>;
    statusCounts: {
        pending: number;
        approved: number;
        to_order: number;
        rejected: number;
        total: number;
    };
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
                Accounting Dashboard
                <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
            </h1>
            
            <StatsCards :status-counts="statusCounts" />
            
            <h1 class="text-lg font-medium">Recent Requests P. O. For Voucher</h1>
            <RecentRequestsTable 
                :is-department-user="isDepartmentUser" 
                :recent-requests="purchaseOrders"
            />
        </div>
    </AppLayout>
</template>