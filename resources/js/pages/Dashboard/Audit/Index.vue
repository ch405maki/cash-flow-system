<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { FileText, ClipboardList, Receipt, Wallet } from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge'
import { formatDateTime } from '@/lib/utils'
import PageHeader from '@/components/PageHeader.vue';

const props = defineProps<{
    isDepartmentUser: boolean;
    userRole: string;
    username: string;
    dashboardStats: {
        pendingCanvasses: number;
        pendingVouchers: number;
        pendingPettyCash: number;
        totalPending: number;
    };
    recentPendingItems: {
        canvasses: Array<any>;
        vouchers: Array<any>;
        pettyCash: Array<any>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Functions to navigate to different audit sections
const navigateToCanvasses = () => {
    router.get('/canvas');
};

const navigateToVouchers = () => {
    router.get('/voucher-approval');
};

const navigateToPettyCash = () => {
    router.get('/audit/petty-cash');
};
</script>

<template>
    <Head title="Audit Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <PageHeader 
                class="capitalize"
                :title="`Welcome, ${username}`" 
                subtitle="Auditing dashboard"
            />

            <!-- Statistics Cards -->
            <div class="grid auto-rows-min gap-4 md:grid-cols-4">
                <!-- Total Pending Card -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Pending</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <FileText class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.totalPending }}</div>
                        <p class="text-xs text-muted-foreground">Items awaiting audit</p>
                    </CardContent>
                </Card>

                <!-- Purchase Canvasses Card -->
                <Card @click="navigateToCanvasses" class="cursor-pointer hover:bg-accent/50 transition-colors">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Purchase Canvasses</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <ClipboardList class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.pendingCanvasses }}</div>
                        <p class="text-xs text-muted-foreground">Status: Submitted</p>
                    </CardContent>
                </Card>

                <!-- Vouchers Card -->
                <Card @click="navigateToVouchers" class="cursor-pointer hover:bg-accent/50 transition-colors">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Vouchers</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <Receipt class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.pendingVouchers }}</div>
                        <p class="text-xs text-muted-foreground">Status: For Audit</p>
                    </CardContent>
                </Card>

                <!-- Petty Cash Card -->
                <Card @click="navigateToPettyCash" class="cursor-pointer hover:bg-accent/50 transition-colors">
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Petty Cash</CardTitle>
                        <div class="p-2 rounded-lg border">
                            <Wallet class="h-4 w-4" />
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ dashboardStats.pendingPettyCash }}</div>
                        <p class="text-xs text-muted-foreground">Status: Submitted</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Pending Items Section -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                <!-- Recent Canvasses -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                        <CardTitle class="text-base font-semibold">Recent Purchase Canvasses</CardTitle>
                        <Badge variant="secondary" class="bg-blue-100 text-blue-800 hover:bg-blue-100">
                            {{ recentPendingItems.canvasses.length }}
                        </Badge>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div 
                            v-for="canvas in recentPendingItems.canvasses.slice(0, 5)" 
                            :key="canvas.id"
                            class="p-3 border rounded-lg hover:bg-accent/50 cursor-pointer transition-colors"
                            @click="navigateTo(`/canvasses/${canvas.id}`)"
                        >
                            <p class="font-medium text-sm">{{ canvas.title }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDateTime(canvas.created_at) }}</p>
                        </div>
                        <div v-if="recentPendingItems.canvasses.length === 0" class="text-center py-4">
                            <p class="text-sm text-muted-foreground">No pending canvasses</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Vouchers -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                        <CardTitle class="text-base font-semibold">Recent Vouchers</CardTitle>
                        <Badge variant="secondary" class="bg-orange-100 text-orange-800 hover:bg-orange-100">
                            {{ recentPendingItems.vouchers.length }}
                        </Badge>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div 
                            v-for="voucher in recentPendingItems.vouchers.slice(0, 5)" 
                            :key="voucher.id"
                            class="p-3 border rounded-lg hover:bg-accent/50 cursor-pointer transition-colors"
                            @click="navigateTo(`/vouchers/${voucher.id}`)"
                        >
                            <p class="font-medium text-sm">Voucher #{{ voucher.voucher_no }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Payee: {{ voucher.payee }}</p>
                            <p class="text-xs text-muted-foreground">Amount: ₱{{ voucher.check_amount }}</p>
                        </div>
                        <div v-if="recentPendingItems.vouchers.length === 0" class="text-center py-4">
                            <p class="text-sm text-muted-foreground">No pending vouchers</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Petty Cash -->
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-4">
                        <CardTitle class="text-base font-semibold">Recent Petty Cash</CardTitle>
                        <Badge variant="secondary" class="bg-green-100 text-green-800 hover:bg-green-100">
                            {{ recentPendingItems.pettyCash.length }}
                        </Badge>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <div 
                            v-for="pc in recentPendingItems.pettyCash.slice(0, 5)" 
                            :key="pc.id"
                            class="p-3 border rounded-lg hover:bg-accent/50 cursor-pointer transition-colors"
                            @click="navigateTo(`/petty-cash/${pc.id}`)"
                        >
                            <p class="font-medium text-sm">PCV #{{ pc.pcv_no }}</p>
                            <p class="text-xs text-muted-foreground mt-1">Paid to: {{ pc.paid_to }}</p>
                            <p class="text-xs text-muted-foreground">{{ formatDateTime(pc.date) }}</p>
                        </div>
                        <div v-if="recentPendingItems.pettyCash.length === 0" class="text-center py-4">
                            <p class="text-sm text-muted-foreground">No pending petty cash</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>