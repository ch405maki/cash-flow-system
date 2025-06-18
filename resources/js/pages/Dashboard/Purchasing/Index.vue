<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/purchasing/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/purchasing/RecentRequestsTable.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    recentRequests: Array<any>;
    statusCounts: {
        pending: number;
        approved: number;
        to_order: number;
        rejected: number;
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
                Purchasing Dashboard
                <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
            </h1>
            
            <StatsCards :status-counts="statusCounts" />
            
            <h1 class="text-lg font-medium">
                Recent Approved Request
                <p class="text-sm text-muted-foreground capitalize">For (purchase order / canvas)</p>
            </h1>
            
            <RecentRequestsTable 
                :is-department-user="isDepartmentUser" 
                :recent-requests="recentRequests" 
            />
        </div>
    </AppLayout>
</template>