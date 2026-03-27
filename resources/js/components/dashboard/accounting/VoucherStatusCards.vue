<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Clock,
  CheckCircle,
  ShoppingCart,
} from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

defineProps<{
  statusCounts: {
    totalForVoucher: number;
    pending: number;
    forAudit: number;
    approved: number;
    return: number;
  };
}>()

const goToForVoucher = () => {
  router.get('/for-voucher');
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
    <Card class="h-full cursor-pointer transition-all duration-200 hover:shadow-md hover:bg-muted/50">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          For Audit
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.forAudit }}</div>
        <p class="text-xs text-muted-foreground">
          Waiting for Audit review
        </p>
      </CardContent>
    </Card>

    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Returned Voucher
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.return }}</div>
        <p class="text-xs text-muted-foreground">
          Vouchers sent back for revision or correction
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
  </div>
</template>