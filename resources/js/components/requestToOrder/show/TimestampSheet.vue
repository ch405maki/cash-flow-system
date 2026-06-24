<script setup lang="ts">
import { CheckCircle } from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils'

defineProps<{
  order: any
}>()
</script>

<template>
  <div>
    <h3 class="text-sm font-medium text-muted-foreground">Request History</h3>
    <div class="mb-3">
      <h4 class="text-muted-foreground">{{ order.order_no }}</h4>
      <p>Created At: {{ formatDateTime(order.created_at) }}</p>
    </div>
    <div v-if="order.approvals?.length">
      <div class="relative pl-6">
        <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
        <div v-for="(approval, index) in order.approvals" :key="approval.id" class="relative mb-6 last:mb-0">
          <div class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10">
            <CheckCircle class="h-5 w-5 text-white" />
          </div>
          <div v-if="index < order.approvals.length - 1" class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"></div>
          <div class="pl-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <span class="capitalize">{{ approval.user?.username || 'Unknown' }}</span>
              </div>
              <span class="text-xs text-muted-foreground">{{ formatDateTime(approval.created_at) }}</span>
            </div>
            <div class="mt-1 flex items-start gap-2">
              <p class="text-sm text-xs text-muted-foreground">"{{ approval.remarks || 'No remarks' }}."</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
