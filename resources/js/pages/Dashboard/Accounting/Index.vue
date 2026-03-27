<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import VoucherStatusCards from '@/components/dashboard/accounting/VoucherStatusCards.vue';
import RequestStatusCards from '@/components/dashboard/accounting/RequestStatusCards.vue';
import RecentRequestsTable from '@/components/dashboard/accounting/RecentRequestsTable.vue';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    purchaseOrders: Array<any>;
    statusCounts: {
        pending: number;
        approved: number;
        forAudit: number;
        return: number;
    };
    voucherStats: {
        totalVouchers: number
        totalAmount: number
        overdueVouchers: number
        currentMonthVouchers: number
        statusCounts: {
            totalForVoucher?: number
            pending?: number
            forAudit?: number
            approved?: number
            paid?: number
            return?: number
        }
        monthlyData: Array<any>
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
            <div class="flex justify-between items-center">
                <PageHeader 
                    class="capitalize"
                    :title="`Welcome, ${username}`" 
                    subtitle="Accounting Dashboard"
                />
            </div>
            
            <VoucherStatusCards :status-counts="statusCounts" />
            <RequestStatusCards :status-counts="statusCounts" />
            
            <div class="space-y-4">
                <PageHeader 
                    title="Recent Requests P.O. For Voucher" 
                    subtitle="Latest purchase orders awaiting voucher creation"
                />
                <RecentRequestsTable
                    :is-department-user="isDepartmentUser"
                    :recent-requests="purchaseOrders"
                />
            </div>
        </div>
    </AppLayout>
</template>