<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/department/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/department/RecentRequestsTable.vue';
import PageHeader from '@/components/PageHeader.vue';

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
            <PageHeader 
                class="capitalize"
                :title="`Welcome, ${username}`" 
                subtitle="Department Dashboard"
            />
            
            <StatsCards :status-counts="statusCounts" />
            
            <RecentRequestsTable 
                :is-department-user="isDepartmentUser" 
                :recent-requests="recentRequests" 
            />
        </div>
    </AppLayout>
</template>