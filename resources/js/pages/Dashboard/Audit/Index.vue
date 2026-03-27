<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card';
import { Tabs, TabsList, TabsTrigger, TabsContent } from '@/components/ui/tabs';
import { 
    FileText, 
    ClipboardList, 
    Receipt, 
    Wallet,
} from 'lucide-vue-next';
import { Badge } from '@/components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import { formatCurrency } from '@/lib/utils';
import PageHeader from '@/components/PageHeader.vue';
import StatusBadge from '@/components/StatusBadge.vue';

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
        canvasses: Array<{
            id: number;
            title: string;
            status: string;
            files_count: number;
            created_at: string;
        }>;
        vouchers: Array<{
            id: number;
            voucher_no: string;
            payee: string;
            check_amount: number;
            created_at: string;
        }>;
        pettyCash: Array<{
            id: number;
            pcv_no: string;
            paid_to: string;
            remarks: string;
            date: string;
            total_amount: number;
            items_count: number;
            created_at: string;
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Active tab state
const activeTab = ref('canvasses');

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

const navigateToItem = (type: string, id: number) => {
    switch(type) {
        case 'canvas':
            router.get(`/canvas/${id}`);
            break;
        case 'voucher':
            router.get(`/vouchers/${id}`);
            break;
        case 'petty-cash':
            router.get(`/petty-cash/${id}`);
            break;
    }
};

// Format date helper
const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
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

            <!-- Recent Pending Items Section with Tabs -->
            <div class="mt-4 space-y-4">
                <!-- Header with Title and Tabs -->
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">Recent Pending Items</h2>
                        <p class="text-sm text-muted-foreground">View and manage items pending audit review</p>
                    </div>
                    
                    <Tabs v-model="activeTab" class="w-auto">
                        <TabsList class="inline-flex h-9 items-center justify-center rounded-lg bg-muted p-1 text-muted-foreground">
                            <TabsTrigger value="canvasses" class="inline-flex items-center px-3 py-1">
                                <span>Canvasses</span>
                                <Badge variant="secondary" class="ml-2 h-5 min-w-[1.5rem] px-1.5 bg-blue-100 text-blue-800">
                                    {{ recentPendingItems.canvasses.length }}
                                </Badge>
                            </TabsTrigger>

                            <TabsTrigger value="vouchers" class="inline-flex items-center px-3 py-1">
                                <span>Vouchers</span>
                                <Badge variant="secondary" class="ml-2 h-5 min-w-[1.5rem] px-1.5 bg-orange-100 text-orange-800">
                                    {{ recentPendingItems.vouchers.length }}
                                </Badge>
                            </TabsTrigger>

                            <TabsTrigger value="pettyCash" class="inline-flex items-center px-3 py-1">
                                <span>Petty Cash</span>
                                <Badge variant="secondary" class="ml-2 h-5 min-w-[1.5rem] px-1.5 bg-green-100 text-green-800">
                                    {{ recentPendingItems.pettyCash.length }}
                                </Badge>
                            </TabsTrigger>
                        </TabsList>
                    </Tabs>
                </div>

                <!-- Tab Content -->
                <div>
                    <!-- Canvasses Tab -->
                    <div v-show="activeTab === 'canvasses'">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[300px]">Title</TableHead>
                                    <TableHead class="w-[100px]">Status</TableHead>
                                    <TableHead class="w-[100px]">Files</TableHead>
                                    <TableHead class="w-[120px]">Created</TableHead>
                                    <TableHead class="w-[80px] text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="canvas in recentPendingItems.canvasses" 
                                    :key="canvas.id"
                                    class="cursor-pointer hover:bg-accent/50"
                                    @click="navigateToItem('canvas', canvas.id)"
                                >
                                    <TableCell class="font-medium">{{ canvas.title }}</TableCell>
                                    <TableCell>
                                        <StatusBadge 
                                            :status="canvas.status"
                                            show-icon
                                            size="md"
                                        />
                                    </TableCell>
                                    <TableCell>{{ canvas.files_count }} files</TableCell>
                                    <TableCell>{{ formatDate(canvas.created_at) }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" class="h-8 px-2">View</Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="recentPendingItems.canvasses.length === 0">
                                    <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                        No pending canvasses
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Vouchers Tab -->
                    <div v-show="activeTab === 'vouchers'">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[150px]">Voucher No.</TableHead>
                                    <TableHead class="w-[200px]">Payee</TableHead>
                                    <TableHead class="w-[120px]">Amount</TableHead>
                                    <TableHead class="w-[120px]">Date</TableHead>
                                    <TableHead class="w-[80px] text-right">Action</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="voucher in recentPendingItems.vouchers" 
                                    :key="voucher.id"
                                    class="cursor-pointer hover:bg-accent/50"
                                    @click="navigateToItem('voucher', voucher.id)"
                                >
                                    <TableCell class="font-medium">{{ voucher.voucher_no }}</TableCell>
                                    <TableCell>{{ voucher.payee }}</TableCell>
                                    <TableCell>{{ formatCurrency(voucher.check_amount) }}</TableCell>
                                    <TableCell>{{ formatDate(voucher.created_at) }}</TableCell>
                                    <TableCell class="text-right">
                                        <Button variant="ghost" size="sm" class="h-8 px-2">View</Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="recentPendingItems.vouchers.length === 0">
                                    <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                        No pending vouchers
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Petty Cash Tab -->
                    <div v-show="activeTab === 'pettyCash'">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[150px]">PCV No.</TableHead>
                                    <TableHead class="w-[200px]">Paid To</TableHead>
                                    <TableHead class="w-[120px]">Items</TableHead>
                                    <TableHead class="w-[120px]">Date</TableHead>
                                    <TableHead class="w-[200px] text-right">Remarks</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow 
                                    v-for="pc in recentPendingItems.pettyCash" 
                                    :key="pc.id"
                                    class="cursor-pointer hover:bg-accent/50"
                                    @click="navigateToItem('petty-cash', pc.id)"
                                >
                                    <TableCell class="font-medium">{{ pc.pcv_no }}</TableCell>
                                    <TableCell>{{ pc.paid_to }}</TableCell>
                                    <TableCell>{{ pc.items_count }} items</TableCell>
                                    <TableCell>{{ formatDate(pc.date) }}</TableCell>
                                    <TableCell class="max-w-[200px] truncate  text-right" :title="pc.remarks">
                                        "{{ pc.remarks || '—' }}"
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="recentPendingItems.pettyCash.length === 0">
                                    <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                        No pending petty cash
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>