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
import { router } from '@inertiajs/vue3'
import { AlertCircle } from "lucide-vue-next"
import { Alert, AlertDescription, AlertTitle } from "@/components/ui/alert"
import PageHeader from '@/components/PageHeader.vue';
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger
} from '@/components/ui/tooltip'

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
    pettyCash: Record<string, any> | null
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

// Go to petty cash liquidation page
const goToLiquidations = () => {
  router.visit('/petty-cash')
}
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <PageHeader 
                class="capitalize"
                :title="`Welcome, ${username}`" 
                subtitle="Purchasing Dashboard"
            />
            <div>
            <!-- Only show alert if there are petty cash records -->
            <Alert
                v-if="pettyCash && pettyCash.length > 0"
                variant="warning"
                @click="goToLiquidations"
                class="
                    bg-orange-100
                    border 
                    border-orange-200
                    hover:border-orange-300
                    cursor-pointer
                    transition-all
                    duration-200
                    ease-in-out
                    hover:shadow-sm
                "
                title="Visit petty cash page."
                >
                <div class="flex items-center gap-2">
                    <AlertCircle class="w-5 h-5 text-orange-600" />
                    <div>
                    <AlertTitle class="text-orange-700 font-semibold">
                        Pending Liquidations
                    </AlertTitle>
                    <AlertDescription class="text-sm text-orange-800">
                        You have <strong>{{ pettyCash.length }}</strong> petty cash
                        {{ pettyCash.length > 1 ? 'requests' : 'request' }} pending for liquidation.
                    </AlertDescription>
                    </div>
                </div>
            </Alert>
            </div>
            
            <StatsCards :status-counts="statusCounts" />

            <div class="grid grid-cols-1 gap-4 py-4" :class="{'md:grid-cols-[1fr_1.5fr]': !isChartExpanded}">
                <!-- Recent Requests Column (hidden when chart is expanded) -->
                <div class="space-y-4" v-if="!isChartExpanded">
                    <PageHeader 
                        title="Recent Approved Requests" 
                        subtitle="For (purchase order / canvas)"
                    />
                    <RecentRequestsTable 
                        :is-department-user="isDepartmentUser" 
                        :recent-requests="recentRequests" 
                        class="mt-2"
                    />
                </div>
                
                <!-- Chart Column (always visible) -->
                <div :class="{'col-span-1': !isChartExpanded, 'col-span-1 md:col-span-2': isChartExpanded}">
                    <div class="flex justify-between items-center">
                    <PageHeader 
                        title="Most Requested Items" 
                        subtitle="Chart for most number of request"
                    />
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