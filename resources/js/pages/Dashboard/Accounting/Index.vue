<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {Button} from '@/components/ui/button'
import StatsCards from '@/components/dashboard/accounting/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/accounting/RecentRequestsTable.vue';
import VoucherStats from '@/components/dashboard/accounting/VoucherStats.vue';
import { ChartArea } from 'lucide-vue-next';
import { ref } from 'vue';

const showVoucherStats = ref(false);

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
            <div class="flex justify-between">
                <h1 class="text-lg font-medium">
                    Accounting Dashboard
                    <p class="text-sm text-muted-foreground capitalize">Welcome, {{ username }}</p>
                </h1>
                <Button @click="toggleVoucherStats">
                    <ChartArea /> {{ showVoucherStats ? 'Hide' : 'Show' }} Monthly Voucher Graph
                </Button>
            </div>
            
            <StatsCards :status-counts="statusCounts" />

            <div v-if="showVoucherStats">
                <h1 class="text-lg font-medium mb-4">Monthly Voucher Trends</h1>
                <VoucherStats :stats="voucherStats" />
            </div>
            
            <h1 class="text-lg font-medium">Recent Requests P. O. For Voucher</h1>
            <RecentRequestsTable
                :is-department-user="isDepartmentUser"
                :recent-requests="purchaseOrders"
            />
        </div>
    </AppLayout>
</template>