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

// Compute grouped totals for row coloring
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

// Get row class based on item type and liquidation status
const getRowClass = (item: any) => {
  const key = item.type === 'Liquidation'
    ? item.liquidation_for_date || item.date
    : item.date

  const totals = groupedByDate.value[key]

  if (!totals || totals.cashAdvance === 0) return ''

  if (totals.liquidation >= totals.cashAdvance) {
    return 'bg-green-100 dark:bg-green-900/20'
  }
  if (totals.liquidation > 0 && totals.liquidation < totals.cashAdvance) {
    return 'bg-yellow-100 dark:bg-yellow-900/20'
  }
  if (item.type?.toLowerCase() === 'cash advance') {
    return 'bg-rose-100 dark:bg-rose-900/20'
  }
  return ''
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
  <Table>
    <TableHeader>
      <TableRow>
        <TableHead>Type</TableHead>
        <TableHead>Particulars</TableHead>
        <TableHead>Date</TableHead>
        <TableHead v-if="isExisting" class="w-[15%]">Liquidation Dated</TableHead>
        <TableHead class="text-right">Amount</TableHead>
        <TableHead>Receipt</TableHead>
        <TableHead class="text-center w-[100px]">Actions</TableHead>
      </TableRow>
    </TableHeader>
    <TableBody>
      <TableRow
        v-for="(item, index) in items"
        :key="item.id || index"
        :class="getRowClass(item)"
      >
        <TableCell class="font-semibold">
          {{ item.type }}
          <span 
            v-if="item?.liquidation_for_date" 
            class="text-sm font-normal text-muted-foreground block"
          >
            ({{ formatDate(item.liquidation_for_date) }})
          </span>
        </TableCell>
        
        <EditableCell
          :value="item.particulars"
          :item-id="item.id"
          :petty-cash-id="pettyCashId"
          field="particulars"
          @update:value="handleUpdateItem(index, 'particulars', $event)"
        />
        
        <EditableCell
          :value="item.date"
          :item-id="item.id"
          :petty-cash-id="pettyCashId"
          field="date"
          type="date"
          @update:value="handleUpdateItem(index, 'date', $event)"
        />
        
        <TableCell v-if="isExisting">
          {{ item.liquidation_for_date ? formatDate(item.liquidation_for_date) : '-' }}
        </TableCell>
        
        <EditableCell
          :value="item.amount"
          :item-id="item.id"
          :petty-cash-id="pettyCashId"
          field="amount"
          type="number"
          class="text-right"
          @update:value="handleUpdateItem(index, 'amount', $event)"
        />
        
        <TableCell>
          <a 
            v-if="item.receipt" 
            :href="`/storage/${item.receipt}`" 
            target="_blank" 
            class="text-blue-600 hover:text-blue-800 underline text-sm"
          >
            View
          </a>
          <span v-else class="text-muted-foreground italic text-sm">No file</span>
        </TableCell>
        
        <TableCell class="text-center">
          <Button 
            variant="ghost" 
            size="sm" 
            @click="handleDeleteItem(index)"
            class="text-destructive hover:text-destructive hover:bg-destructive/10"
          >
            <Trash2 class="h-4 w-4" />
          </Button>
        </TableCell>
      </TableRow>
    </TableBody>
  </Table>
</template>