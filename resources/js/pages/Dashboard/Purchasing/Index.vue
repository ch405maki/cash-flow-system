<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import StatsCards from '@/components/dashboard/purchasing/StatsCards.vue';
import RecentRequestsTable from '@/components/dashboard/purchasing/RecentRequestsTable.vue';
import FrequentItemsChart from '@/components/dashboard/purchasing/FrequentItemsChart.vue';
import { Expand, Minimize } from 'lucide-vue-next';
import { ref } from 'vue';

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

const isChartExpanded = ref(false);
const chartRefreshKey = ref(0); // Add this ref for forcing refresh

const toggleChartExpand = () => {
  isChartExpanded.value = !isChartExpanded.value;
  chartRefreshKey.value++; // Increment to force refresh
};
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

            <div class="grid grid-cols-1 gap-4 py-4" :class="{'md:grid-cols-[1fr_1.5fr]': !isChartExpanded}">
                <!-- Recent Requests Column (hidden when chart is expanded) -->
                <div class="space-y-4" v-if="!isChartExpanded">
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
                                
                <!-- Chart Column (always visible) -->
                <div :class="{'col-span-1': !isChartExpanded, 'col-span-1 md:col-span-2': isChartExpanded}">
                    <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-medium text-gray-900">Most Requested Items</h3>
                        <p class="text-sm text-gray-500">Chart for most number of request</p>
                    </div>
                    <Button variant="secondary" @click="toggleChartExpand">
                        <component :is="isChartExpanded ? Minimize : Expand" />
                    </Button>
                    </div>
                    <FrequentItemsChart :items="frequentItems" :key="chartRefreshKey" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>