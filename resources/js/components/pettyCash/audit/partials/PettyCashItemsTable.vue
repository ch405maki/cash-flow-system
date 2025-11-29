<script setup lang="ts">
import { computed } from 'vue'
import { formatDate } from '@/lib/utils'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

const props = defineProps<{
  items: any[]
}>()

// Group by date for row coloring
const groupedByDate = computed(() => {
  const map: Record<string, { cashAdvance: number; liquidation: number }> = {}
  props.items.forEach(item => {
    const key = item.type === 'Liquidation'
      ? item.liquidation_for_date || item.date
      : item.date
    if (!map[key]) map[key] = { cashAdvance: 0, liquidation: 0 }
    if (item.type === 'Cash Advance') map[key].cashAdvance += Number(item.amount)
    if (item.type === 'Liquidation') map[key].liquidation += Number(item.amount)
  })
  return map
})

const getRowClass = (item: any) => {
  const key = item.type === 'Liquidation'
    ? item.liquidation_for_date || item.date
    : item.date
  const totals = groupedByDate.value[key]
  if (!totals || totals.cashAdvance === 0) return ''
  if (totals.liquidation >= totals.cashAdvance) return 'bg-green-100'
  if (totals.liquidation > 0 && totals.liquidation < totals.cashAdvance) return 'bg-yellow-100'
  if (item.type?.toLowerCase() === 'cash advance') return 'bg-rose-100'
  return ''
}

// Calculate totals by type
const totalsByType = computed(() => {
  const totals: Record<string, number> = {}
  props.items.forEach(item => {
    totals[item.type] = (totals[item.type] || 0) + Number(item.amount)
  })
  return totals
})
</script>

<template>
  <div class="border rounded-xl p-4">
    <h3 class="text-lg font-semibold mb-2">Existing Items</h3>
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[20%]">Type</TableHead>
          <TableHead class="w-[25%]">Particulars</TableHead>
          <TableHead class="w-[15%]">Date</TableHead>
          <TableHead class="w-[15%] text-right">Amount</TableHead>
          <TableHead class="w-[15%]">Receipt</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="item in items"
          :key="item.id"
          :class="getRowClass(item)"
        >
          <TableCell class="font-semibold">
            {{ item.type }}
            <span v-if="item.liquidation_for_date" class="text-xs text-muted-foreground block">
              ({{ formatDate(item.liquidation_for_date) }})
            </span>
          </TableCell>
          <TableCell>{{ item.particulars }}</TableCell>
          <TableCell>{{ formatDate(item.date) }}</TableCell>
          <TableCell class="text-right font-medium">
            ₱{{ Number(item.amount).toLocaleString() }}
          </TableCell>
          <TableCell>
            <a
              v-if="item.receipt"
              :href="`/storage/${item.receipt}`"
              target="_blank"
              class="text-blue-600 hover:text-blue-800 underline text-sm"
            >
              View
            </a>
            <span v-else class="italic text-muted-foreground text-sm">No file</span>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>

    <!-- Totals Summary -->
    <div class="mt-4 flex justify-end">
      <div class="grid grid-cols-1 gap-2">
        <h3 class="text-md font-semibold">Running Totals</h3>
        <div v-for="(amount, type) in totalsByType" :key="type" class="flex space-x-2">
          <h1 class="font-medium w-48">{{ type }}:</h1>
          <h1 class="font-bold w-48 text-right">₱{{ amount.toLocaleString() }}</h1>
        </div>
      </div>
    </div>
  </div>
</template>