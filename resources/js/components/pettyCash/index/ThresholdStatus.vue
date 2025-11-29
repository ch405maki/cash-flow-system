<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Info } from 'lucide-vue-next'

interface Props {
  thresholds?: {
    cash_advance: {
      limit: number
      used: number
      remaining: number
    }
    reimbursement: {
      limit: number
      used: number
      remaining: number
    }
  }
}

const props = defineProps<Props>()

</script>

<template>
  <!-- Debug info -->
  <div v-if="false" class="p-2 bg-blue-100 text-xs mb-2">
    ThresholdStatus Debug: 
    thresholds = {{ thresholds }},
    hasThresholds = {{ !!thresholds }}
  </div>

  <div class="space-y-4">
    <!-- Header -->
    <div class="flex items-center gap-2">
      <div class="flex items-center justify-center">
        <Info class="h-3 w-3 text-orange-600" />
      </div>
      <h3 class="font-medium">Daily Threshold Status</h3>
    </div>

    <!-- Threshold Cards -->
    <div class="space-y-3" v-if="thresholds">
      <!-- Cash Advance Card -->
      <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Cash Advance</span>
          <Badge 
            variant="outline" 
            :class="thresholds.cash_advance.used >= thresholds.cash_advance.limit 
              ? 'bg-red-50 text-red-700 border-red-200' 
              : 'bg-green-50 text-green-700 border-green-200'"
          >
            {{ thresholds.cash_advance.used >= thresholds.cash_advance.limit ? 'Limit Reached' : 'Available' }}
          </Badge>
        </div>
        
        <div class="space-y-2">
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="h-2 rounded-full transition-all duration-300"
              :class="thresholds.cash_advance.used >= thresholds.cash_advance.limit 
                ? 'bg-red-500' 
                : 'bg-orange-500'"
              :style="{ width: `${Math.min((thresholds.cash_advance.used / thresholds.cash_advance.limit) * 100, 100)}%` }"
            ></div>
          </div>
          
          <!-- Amounts -->
          <div class="flex justify-between text-xs text-gray-600">
            <span>Used: ₱{{ thresholds.cash_advance.used.toLocaleString() }}</span>
            <span>Limit: ₱{{ thresholds.cash_advance.limit.toLocaleString() }}</span>
          </div>
          
          <!-- Remaining -->
          <div class="text-sm font-medium text-center" 
              :class="thresholds.cash_advance.remaining <= 0 
                ? 'text-red-600' 
                : 'text-green-600'">
            Remaining: ₱{{ thresholds.cash_advance.remaining.toLocaleString() }}
          </div>
        </div>
      </div>

      <!-- Reimbursement Card -->
      <div class="rounded-lg border border-gray-200 bg-white p-3 shadow-sm">
        <div class="flex items-center justify-between mb-2">
          <span class="text-sm font-medium text-gray-700">Reimbursement</span>
          <Badge 
            variant="outline" 
            :class="thresholds.reimbursement.used >= thresholds.reimbursement.limit 
              ? 'bg-red-50 text-red-700 border-red-200' 
              : 'bg-green-50 text-green-700 border-green-200'"
          >
            {{ thresholds.reimbursement.used >= thresholds.reimbursement.limit ? 'Limit Reached' : 'Available' }}
          </Badge>
        </div>
        
        <div class="space-y-2">
          <!-- Progress Bar -->
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="h-2 rounded-full transition-all duration-300"
              :class="thresholds.reimbursement.used >= thresholds.reimbursement.limit 
                ? 'bg-red-500' 
                : 'bg-blue-500'"
              :style="{ width: `${Math.min((thresholds.reimbursement.used / thresholds.reimbursement.limit) * 100, 100)}%` }"
            ></div>
          </div>
          
          <!-- Amounts -->
          <div class="flex justify-between text-xs text-gray-600">
            <span>Used: ₱{{ thresholds.reimbursement.used.toLocaleString() }}</span>
            <span>Limit: ₱{{ thresholds.reimbursement.limit.toLocaleString() }}</span>
          </div>
          
          <!-- Remaining -->
          <div class="text-sm font-medium text-center" 
              :class="thresholds.reimbursement.remaining <= 0 
                ? 'text-red-600' 
                : 'text-green-600'">
            Remaining: ₱{{ thresholds.reimbursement.remaining.toLocaleString() }}
          </div>
        </div>
      </div>
    </div>

    <!-- Fallback if no thresholds data -->
    <div v-else class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-center">
      <p class="text-gray-500 text-sm">No threshold data available</p>
    </div>
  </div>
</template>