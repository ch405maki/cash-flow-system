<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/executive/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/executive/RecentRequestsTable.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    recentRequests: Array<any>;
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
                Executive Director Dashboard
                <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
            </h1>
            
            <StatsCards :status-counts="statusCounts" />
            
            <h1 class="text-lg font-medium">Recent Requests</h1>
            
            <!-- <RecentRequestsTable 
                :is-department-user="isDepartmentUser" 
                :recent-requests="recentRequests" 
            /> -->
        </div>
    </AppLayout>
</template>