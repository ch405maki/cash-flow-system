<script setup lang="ts">
import { computed } from 'vue'
import { formatDate } from '@/lib/utils'

const props = defineProps<{
  items: any[]
}>()

const totalsByType = computed(() => {
  return props.items.reduce(
    (totals, item) => {
      const type = item.type || 'Unknown'
      totals[type] = (totals[type] || 0) + Number(item.amount || 0)
      return totals
    },
    {} as Record<string, number>
  )
})

const totalAmount = computed(() => {
  return props.items
    .filter(item => ['Liquidation', 'Reimbursement'].includes(item.type))
    .reduce((sum, item) => sum + Number(item.amount || 0), 0)
})

const groupedByDate = computed(() => {
  const grouped: Record<string, { cashAdvance: number; liquidation: number }> = {}

  props.items.forEach(item => {
    const date = item.liquidation_for_date || item.date
    if (!grouped[date]) {
      grouped[date] = { cashAdvance: 0, liquidation: 0 }
    }

    if (item.type === 'Cash Advance') {
      grouped[date].cashAdvance += Number(item.amount) || 0
    }

    if (item.type === 'Liquidation' || item.type === 'Reimbursement') {
      grouped[date].liquidation += Number(item.amount) || 0
    }
  })

  return grouped
})

const changeByDate = computed(() => {
  const map: Record<string, number> = {}

  Object.entries(groupedByDate.value).forEach(([date, totals]) => {
    if (totals.cashAdvance > 0 && totals.liquidation < totals.cashAdvance) {
      map[date] = totals.cashAdvance - totals.liquidation
    } else {
      map[date] = 0
    }
  })

  return map
})
</script>

<template>
  <div class="mt-4 flex justify-end">
    <div class="grid grid-cols-1">
      <h3 class="text-md font-semibold">Running Totals</h3>
      <div v-for="(amount, type) in totalsByType" :key="type" class="flex space-x-2">
        <h1 class="font-medium w-48">{{ type }}:</h1>
        <h1 class="font-bold w-48 text-right">₱{{ amount.toLocaleString() }}</h1>
      </div>

      <div 
        v-for="(change, date) in changeByDate" 
        :key="date" 
        class="flex space-x-2 text-rose-600"
        v-if="items.some(item => item.type === 'Liquidation')"
      >
        <h1 class="font-medium w-48">Change ({{ formatDate(date) }}):</h1>
        <h1 class="font-bold w-48 text-right">₱{{ change.toLocaleString() }}</h1>
      </div>
      
      <div class="flex items-center justify-between mt-2 border-t border-gray-400 pt-2 font-bold">
        <h1>Grand Total</h1>
        <h1 class="text-right">₱{{ totalAmount.toLocaleString() }}</h1>
      </div>
    </div>
  </div>
</template>