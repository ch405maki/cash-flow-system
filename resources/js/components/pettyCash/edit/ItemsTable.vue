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
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'
import EditableCell from './EditableCell.vue'
import { usePettyCash } from '@/composables/usePettyCash'

const props = defineProps<{
  items: any[]
  isExisting?: boolean
  pettyCashId: number
}>()

const emit = defineEmits<{
  (e: 'item-updated', item: any): void
  (e: 'item-deleted', itemId: number): void
}>()

const { updateItem, deleteItem } = usePettyCash()

// Compute grouped totals for row coloring (liquidation status)
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

// Get row class based on item type first, then liquidation status
const getRowClass = (item: any) => {
  // First, apply base type colors
  let baseClass = ''
  
  switch (item.type) {
    case 'Cash Advance':
      baseClass = 'bg-blue-50/50 dark:bg-blue-950/20'
      break
    case 'Liquidation':
      baseClass = 'bg-green-50/50 dark:bg-green-950/20'
      break
    case 'Reimbursement':
      baseClass = 'bg-purple-50/50 dark:bg-purple-950/20'
      break
    default:
      baseClass = ''
  }

  // Then, override with liquidation status colors if applicable
  const key = item.type === 'Liquidation'
    ? item.liquidation_for_date || item.date
    : item.date

  const totals = groupedByDate.value[key]

  if (totals && totals.cashAdvance > 0) {
    if (totals.liquidation >= totals.cashAdvance) {
      return 'bg-green-100 dark:bg-green-900/20 hover:brightness-95 dark:hover:brightness-125' // Fully liquidated
    }
    if (totals.liquidation > 0 && totals.liquidation < totals.cashAdvance) {
      return 'bg-yellow-100 dark:bg-yellow-900/20 hover:brightness-95 dark:hover:brightness-125' // Partially liquidated
    }
    if (item.type?.toLowerCase() === 'cash advance') {
      return 'bg-rose-100 dark:bg-rose-900/20 hover:brightness-95 dark:hover:brightness-125' // Cash advance with no liquidation
    }
  }

  return `${baseClass} hover:brightness-95 dark:hover:brightness-125`
}

// Get badge color for item type
const getTypeBadgeClass = (type: string) => {
  switch (type) {
    case 'Cash Advance':
      return 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300'
    case 'Liquidation':
      return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
    case 'Reimbursement':
      return 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300'
    default:
      return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300'
  }
}

const handleUpdateItem = async (index: number, field: string, value: any) => {
  const item = props.items[index]
  
  // Update local state immediately for responsive UI
  item[field] = value
  emit('item-updated', item)
  
  // Auto-save to server if it's an existing item
  if (item.id) {
    try {
      await updateItem(props.pettyCashId, {
        id: item.id,
        [field]: value,
        type: item.type,
        particulars: item.particulars,
        date: item.date,
        liquidation_for_date: item.liquidation_for_date,
        amount: item.amount
      })
    } catch (error) {
      // Revert on error
      item[field] = item._originalValues?.[field] || item[field]
      emit('item-updated', item)
    }
  }
}

const handleDeleteItem = async (index: number) => {
  const item = props.items[index]
  
  if (confirm(`Are you sure you want to delete this item?`)) {
    if (item.id) {
      try {
        await deleteItem(props.pettyCashId, item.id)
        emit('item-deleted', item.id)
      } catch (error) {
        return
      }
    }
    
    // Remove from local array
    props.items.splice(index, 1)
    if (!item.id) {
      emit('item-deleted', item.id)
    }
  }
}
</script>

<template>
  <div class="overflow-x-auto scrollbar-thin scrollbar-thumb-border scrollbar-track-transparent">
    <Table>
      <TableHeader>
        <TableRow>
          <TableHead class="w-[150px]">Type</TableHead>
          <TableHead class="min-w-[250px]">Particulars</TableHead>
          <TableHead class="w-[130px]">Date</TableHead>
          <TableHead v-if="isExisting" class="w-[150px]">Liquidation Dated</TableHead>
          <TableHead class="w-[150px] text-right">Amount (₱)</TableHead>
          <TableHead class="w-[120px]">Receipt</TableHead>
          <TableHead class="w-[80px] text-center">Actions</TableHead>
        </TableRow>
      </TableHeader>
      <TableBody>
        <TableRow
          v-for="(item, index) in items"
          :key="item.id || index"
          :class="[getRowClass(item), 'transition-colors duration-200']"
        >
          <!-- Type Column -->
          <TableCell class="font-medium align-top">
            <div class="space-y-1">
              <span :class="['px-2 py-1 rounded-full text-xs font-medium inline-block', getTypeBadgeClass(item.type)]">
                {{ item.type }}
              </span>
              <span 
                v-if="item?.liquidation_for_date && !isExisting" 
                class="text-xs font-normal text-muted-foreground block mt-1"
              >
                Liq: {{ formatDate(item.liquidation_for_date) }}
              </span>
            </div>
          </TableCell>
          
          <!-- Particulars Column -->
          <TableCell class="align-top">
            <EditableCell
              :value="item.particulars"
              :item-id="item.id"
              :petty-cash-id="pettyCashId"
              field="particulars"
              @update:value="handleUpdateItem(index, 'particulars', $event)"
            />
          </TableCell>
          
          <!-- Date Column -->
          <TableCell class="align-top">
            <EditableCell
              :value="item.date"
              :item-id="item.id"
              :petty-cash-id="pettyCashId"
              field="date"
              type="date"
              @update:value="handleUpdateItem(index, 'date', $event)"
            />
          </TableCell>
          
          <!-- Liquidation Dated Column (Only for existing items) -->
          <TableCell v-if="isExisting" class="align-top">
            <span v-if="item.liquidation_for_date" class="text-sm">
              {{ formatDate(item.liquidation_for_date) }}
            </span>
            <span v-else class="text-muted-foreground italic text-sm">—</span>
          </TableCell>
          
          <!-- Amount Column -->
          <TableCell class="text-right align-top">
            <EditableCell
              :value="item.amount"
              :item-id="item.id"
              :petty-cash-id="pettyCashId"
              field="amount"
              type="number"
              class="text-right font-medium"
              @update:value="handleUpdateItem(index, 'amount', $event)"
            />
          </TableCell>
          
          <!-- Receipt Column -->
          <TableCell class="align-top">
            <div class="flex flex-col gap-1">
              <a 
                v-if="item.receipt" 
                :href="`/storage/${item.receipt}`" 
                target="_blank" 
                class="text-blue-600 hover:text-blue-800 underline text-sm inline-flex items-center gap-1"
              >
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="shrink-0"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><polyline points="14 2 14 8 20 8"/></svg>
                View
              </a>
              <span v-else class="text-muted-foreground italic text-sm">No file</span>
              <span v-if="item.receipt_name" class="text-xs text-muted-foreground truncate max-w-[100px]" :title="item.receipt_name">
                {{ item.receipt_name }}
              </span>
            </div>
          </TableCell>
          
          <!-- Actions Column -->
          <TableCell class="text-center align-top">
            <Button 
              variant="ghost" 
              size="sm" 
              @click="handleDeleteItem(index)"
              class="text-destructive hover:text-destructive hover:bg-destructive/10 h-8 w-8 p-0"
            >
              <Trash2 class="h-4 w-4" />
              <span class="sr-only">Delete</span>
            </Button>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
  </div>
</template>