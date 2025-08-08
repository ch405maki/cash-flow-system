<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { 
  Users,
  Clock,
  CheckCircle,
  ShoppingCart,
  XCircle,
} from 'lucide-vue-next'

defineProps<{
  statusCounts: {
    pending: number;
    approved: number;
    to_order: number;
    rejected: number;
    total: number;
  };
}>()
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <!-- Total Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Total Requests</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Users class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.total }}</div>
        <p class="text-xs text-muted-foreground">Department requests</p>
      </CardContent>
    </Card>

    <!-- Pending Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Pending</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.pending }}</div>
        <p class="text-xs text-muted-foreground">Waiting approval</p>
      </CardContent>
    </Card>

    <!-- Approved Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">Approved</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approved }}</div>
        <p class="text-xs text-muted-foreground">Ready for processing</p>
      </CardContent>
    </Card>

    <!-- To Order Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">To Order</CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.to_order }}</div>
        <p class="text-xs text-muted-foreground">Ready for purchase</p>
      </CardContent>
    </Card>
  </div>
</template>
