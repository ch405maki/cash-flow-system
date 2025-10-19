<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {Button} from '@/components/ui/button'
import StatsCards from '@/components/dashboard/accounting/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/accounting/RecentRequestsTable.vue';
import VoucherStats from '@/components/dashboard/accounting/VoucherStats.vue';
import { ChartArea } from 'lucide-vue-next';
import PageHeader from '@/components/PageHeader.vue';
import { ref } from 'vue';

const showVoucherStats = ref(true);

const props = defineProps<{
    isDepartmentUser: boolean;
    purchaseOrders: Array<any>;
    statusCounts: {
        pending: number;
        approved: number;
        forApproval: number;
        rejected: number;
    };
    voucherStats: {
        totalVouchers: number
        totalAmount: number
        overdueVouchers: number
        currentMonthVouchers: number
        statusCounts: {
            totalForVoucher?: number
            pending?: number
            forApproval?: number
            approved?: number
            paid?: number
            rejected?: number
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

const toggleVoucherStats = () => {
    showVoucherStats.value = !showVoucherStats.value;
};
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
                <Button @click="toggleVoucherStats">
                    <ChartArea /> {{ showVoucherStats ? 'Hide' : 'Show' }} Monthly Voucher Graph
                </Button>
            </div>
            
            <StatsCards :status-counts="statusCounts" />

            <div v-if="showVoucherStats" class="space-y-4">
                <PageHeader 
                    title="Monthly Voucher Trends" 
                    subtitle="Analytics and insights for voucher processing"
                />
                <VoucherStats :stats="voucherStats" />
            </div>
            
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