<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import StatsCards from '@/components/dashboard/executive/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/executive/RecentRequestsTable.vue';
import RecentRequestToOrdersTable from '@/components/dashboard/executive/RecentRequestToOrdersTable.vue';
import RecentPurchaseOrdersTable from '@/components/dashboard/executive/RecentPurchaseOrdersTable.vue';
import RecentVoucherTable from '@/components/dashboard/executive/RecentVoucherTable.vue';
import ApprovalTimeTrendsChart from '@/components/dashboard/executive/ApprovalTimeTrendsChart.vue';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
  isDepartmentUser: boolean;
  recentRequests: Array<any>;
  recentRequestToOrders: Array<any>;
  recentPurchaseOrders: Array<any>;
  vouchers: Array<any>;
  statusCounts: {
    totalRequest: number;
    totalPO: number;
    toOrderApproval: number;
    poApproval: number;
    rejected: number;
    totalRequestToOrder: number;
    totalPurchaseOrderAmount: number;
    pendingRequestToOrder: number;
  };
  monthlyMetrics: {
    requests: Record<number, number>;
    requestToOrders: Record<number, number>;
    purchaseOrders: Record<number, number>;
    purchaseOrderAmounts: Record<number, number>;
  };
  approvalTimeTrends: any; 
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
          subtitle="Executive Director Dashboard"
      />

      <StatsCards :status-counts="statusCounts" />

      <!-- Add the chart here -->
      <ApprovalTimeTrendsChart :approval-time-trends="approvalTimeTrends" />

      <Tabs default-value="requestToOrders" class="mt-4 w-full">
        <div class="flex items-center justify-between">
          <PageHeader 
            title="Recent Data" 
            subtitle="Recently added records and updates"
          />
          <TabsList class="grid w-full max-w-xl grid-cols-4">
            <TabsTrigger value="requests">Requests</TabsTrigger>
            <TabsTrigger value="requestToOrders">Request to Orders</TabsTrigger>
            <TabsTrigger value="purchaseOrders">Purchase Orders</TabsTrigger>
            <TabsTrigger value="vouchers">Vouchers</TabsTrigger>
          </TabsList>
        </div>

        <TabsContent value="requests">
          <RecentRequestsTable
            :is-department-user="isDepartmentUser"
            :recent-requests="recentRequests"
          />
        </TabsContent>

        <TabsContent value="requestToOrders">
          <RecentRequestToOrdersTable
            :recent-request-to-orders="recentRequestToOrders"
          />
        </TabsContent>

        <TabsContent value="purchaseOrders">
          <RecentPurchaseOrdersTable
            :recent-purchase-orders="recentPurchaseOrders"
          />
        </TabsContent>

        <TabsContent value="vouchers">
          <RecentVoucherTable :vouchers="vouchers" />
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
