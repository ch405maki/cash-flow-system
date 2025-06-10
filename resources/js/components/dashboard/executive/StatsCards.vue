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
    totalPO: number;
    totalRequest: number;
    toOrderApproval: number;
    poApproval: number;
    rejected: number;
  };
}>()

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
        <CardTitle class="text-sm font-medium">
          Total Department Request(s)
        </CardTitle>
        <Users class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.totalRequest }}</div>
        <p class="text-xs text-muted-foreground">
          Month of  {{ currentMonth }}
        </p>
      </CardContent>
    </Card>
    
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Total Approved P. O. 's
        </CardTitle>
        <CheckCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.totalPO }}</div>
        <p class="text-xs text-muted-foreground">
          Ready for processing
        </p>
      </CardContent>
    </Card>

    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Total Approved Voucher(s)
        </CardTitle>
        <Clock class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">
          Month of  {{ currentMonth }}
        </p>
      </CardContent>
    </Card>

    <Card 
      class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" 
      @click="goToRequestApproval"
    >
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending Request To Order
        </CardTitle>
        <Users class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.toOrderApproval }}</div>
        <p class="text-xs text-muted-foreground">
          For Approval Request To Order
        </p>
      </CardContent>
    </Card>

    <!-- PO Approval -->
      <Card 
        class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" 
        @click="goToPurchaseApproval"
      >
      <CardHeader  class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending Purchase Order
        </CardTitle>
        <ShoppingCart class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.poApproval }}</div>
        <p class="text-xs text-muted-foreground">
          For Approval P. O.
        </p>
      </CardContent>
    </Card>

    <!-- Rejected Card -->
    <Card 
      class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" 
      @click="goToVoucherApproval"
    >
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending Voucher
        </CardTitle>
        <XCircle class="h-4 w-4 text-muted-foreground" />
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">0</div>
        <p class="text-xs text-muted-foreground">
          For Approval Voucher
        </p>
      </CardContent>
    </Card>
  </div>
</template>