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
    totalForVoucher: number;
    pending: number;
    forApproval: number;
    approved: number;
    rejected: number;
  };
}>()

const goToForVoucher = () => {
  router.get('/for-voucher');
};

const goToPending = () => {
  router.get('/vouchers')
};
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <!-- Pending Requests Card -->
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50" @click="goToForVoucher">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          For Voucher
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.totalForVoucher }}</div>
        <p class="text-xs text-muted-foreground">
          Approved P.O. For Voucher
        </p>
      </CardContent>
    </Card>
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50"  @click="goToPending">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.pending }}</div>
        <p class="text-xs text-muted-foreground">
          Waiting for department head approval
        </p>
      </CardContent>
    </Card>

    <!-- To Order Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          For EOD Approval
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.forApproval }}</div>
        <p class="text-xs text-muted-foreground">
          Pending For EOD Approval
        </p>
      </CardContent>
    </Card>

    <!-- Approved Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Approved
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approved }}</div>
        <p class="text-xs text-muted-foreground">
          Ready for check processing or for release
        </p>
      </CardContent>
    </Card>

    <!-- Rejected Card -->
    <!-- <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Rejected
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <XCircle class="h-4 w-4 text-muted-foreground"/>
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.rejected }}</div>
        <p class="text-xs text-muted-foreground">
          Not approved for review
        </p>
      </CardContent>
    </Card> -->
  </div>
</template>