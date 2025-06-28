<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Users,
  Clock,
  CheckCircle,
  ShoppingCart,
  XCircle,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

defineProps<{
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
}>();


const goToRequestApproval = () => {
  router.get('/for-approval');
};

const goToPurchaseApproval = () => {
  router.get('/purchase-orders');
};

const goToVoucherApproval = () => {
  router.get('/vouchers');
};

const currentMonth = new Date().toLocaleString('default', { month: 'long' });
</script>

<template>
  <div class="grid gap-4 md:grid-cols-3">
    <!-- Total Department Requests -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Total Department Request(s)</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Users class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.totalRequest }}</div>
        <p class="text-xs text-muted-foreground">Month of {{ currentMonth }}</p>
      </CardContent>
    </Card>
    
    <!-- Approved POs -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Total Approved P. O. 's</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.totalPO }}</div>
        <p class="text-xs text-muted-foreground">Ready for processing</p>
      </CardContent>
    </Card>

    <!-- Approved Vouchers -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Total Approved Voucher(s)</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">Month of {{ currentMonth }}</p>
      </CardContent>
    </Card>

    <!-- Pending Request To Order -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" @click="goToRequestApproval">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Pending Request To Order</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Users class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.toOrderApproval }}</div>
        <p class="text-xs text-muted-foreground">For Approval Request To Order</p>
      </CardContent>
    </Card>

    <!-- PO Approval -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" @click="goToPurchaseApproval">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Pending Purchase Order</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.poApproval }}</div>
        <p class="text-xs text-muted-foreground">For Approval P. O.</p>
      </CardContent>
    </Card>

    <!-- Voucher Approval -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" @click="goToVoucherApproval">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Pending Voucher</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <XCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">For Approval Voucher</p>
      </CardContent>
    </Card>
  </div>
</template>
