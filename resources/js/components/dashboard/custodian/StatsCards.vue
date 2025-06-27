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
    approval: number;
    to_order: number;
    rejected: number;
  };
}>()
</script>

<template>
  <div class="grid gap-4 md:grid-cols-4">
    <!-- Pending Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending Request
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <Clock class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.pending }}</div>
        <p class="text-xs text-muted-foreground">Waiting to release</p>
      </CardContent>
    </Card>

    <!-- To Order Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          To Order
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <ShoppingCart class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.to_order }}</div>
        <p class="text-xs text-muted-foreground">Tagged to order</p>
      </CardContent>
    </Card>

    <!-- Approved Requests Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Pending Approval
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <CheckCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.approval }}</div>
        <p class="text-xs text-muted-foreground">Waiting for approval</p>
      </CardContent>
    </Card>

    <!-- Rejected Card -->
    <Card class="h-full">
      <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
        <CardTitle class="text-sm font-medium">
          Rejected
        </CardTitle>
        <div class="p-2 rounded-lg bg-purple-500/10 text-violet-500 border">
          <XCircle class="h-4 w-4 text-muted-foreground" />
        </div>
      </CardHeader>
      <CardContent>
        <div class="text-2xl font-bold">{{ statusCounts.rejected }}</div>
        <p class="text-xs text-muted-foreground">Not approved</p>
      </CardContent>
    </Card>
  </div>
</template>
