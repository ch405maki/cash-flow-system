<script setup lang="ts">
import { PhilippinePeso } from 'lucide-vue-next'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'

interface Props {
  pettyCashFund?: {
    fund_amount?: number
    fund_balance?: number
  }
  fundStatus: any
}
const props = defineProps<Props>()

</script>

<template>
  <!-- Debug info -->
  <div v-if="false" class="p-2 bg-red-100 text-xs mb-2">
    FundBalance Debug: 
    pettyCashFund = {{ pettyCashFund }}, 
    fundBalance = {{ pettyCashFund?.fund_balance }},
    hasFundBalance = {{ pettyCashFund && pettyCashFund.fund_balance !== undefined }}
  </div>

  <TooltipProvider v-if="pettyCashFund && pettyCashFund.fund_balance !== undefined">
    <Tooltip>
      <TooltipTrigger as-child>
        <div
          :class="[
            'rounded-lg border px-5 py-2 flex items-center justify-between transition-colors cursor-pointer w-full',
            fundStatus?.color || 'border-gray-300 bg-gray-100',
          ]"
        >
          <!-- Icon -->
          <div
            class="flex items-center justify-center w-10 h-10 rounded-full border-2 flex-shrink-0"
            :class="fundStatus?.iconBackground || 'bg-gray-100 border-gray-400'"
          >
            <PhilippinePeso class="w-6 h-6" :class="fundStatus?.iconColor || 'text-gray-600'" />
          </div>

          <!-- Balance Info -->
          <div class="text-right ml-4 flex-1">
            <h1 class="text-2xl font-semibold leading-tight text-left">
              {{ Number(pettyCashFund.fund_balance).toLocaleString() }}
            </h1>
            <p
              v-if="fundStatus?.label"
              class="text-sm font-medium"
              :class="fundStatus?.textColor || 'text-gray-700'"
            >
              {{ fundStatus.label }}
            </p>
          </div>
        </div>
      </TooltipTrigger>

      <TooltipContent>
        <div class="space-y-1">
          <div class="flex justify-between gap-4">
            <span class="font-medium">Current Balance:</span>
            <span class="font-semibold">
              ₱{{ Number(pettyCashFund.fund_balance).toLocaleString() }}
            </span>    
          </div>
          <div class="flex justify-between gap-4">
            <span class="font-medium">Allocated Fund:</span>
            <span class="font-semibold">
              ₱{{ Number(pettyCashFund.fund_amount).toLocaleString() }}
            </span>
          </div>
          <div class="flex justify-between gap-4 border-t pt-1">
            <span class="font-medium">Utilized:</span>
            <span class="font-semibold">
              ₱{{ Number(pettyCashFund.fund_amount - pettyCashFund.fund_balance).toLocaleString() }}
            </span>
          </div>
        </div>
      </TooltipContent>
    </Tooltip>
  </TooltipProvider>

  <!-- Fallback if no fund data -->
  <div v-else class="rounded-lg border border-gray-300 bg-gray-100 px-5 py-2 flex items-center justify-between w-full opacity-50">
    <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 bg-gray-100 border-gray-400 flex-shrink-0">
      <PhilippinePeso class="w-6 h-6 text-gray-400" />
    </div>
    <div class="text-right ml-4 flex-1">
      <h1 class="text-2xl font-semibold leading-tight text-left text-gray-400">
        N/A
      </h1>
      <p class="text-sm font-medium text-gray-500">
        No fund data
      </p>
    </div>
  </div>
</template>