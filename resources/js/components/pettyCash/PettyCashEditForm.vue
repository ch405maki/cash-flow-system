<script setup lang="ts">
import { reactive, ref, computed, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Separator } from '@/components/ui/separator'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket, Forward, Pencil, PlusCircle, X } from 'lucide-vue-next'
import { formatDate } from '@/lib/utils'
import { usePettyCash } from '@/composables/usePettyCash'

import ItemsTable from '@/components/pettyCash/edit/ItemsTable.vue'
import ItemForm from '@/components/pettyCash/edit/ItemForm.vue'
import TotalsSummary from '@/components/pettyCash/edit/TotalsSummary.vue'
import ConfirmationDialog from '@/components/pettyCash/edit/ConfirmationDialog.vue'

const user = usePage().props.auth.user
const props = defineProps<{
  pettyCash: any
}>()

const toast = useToast()
const { updateMainFields } = usePettyCash()

// Main form data
const form = reactive({
  paid_to: props.pettyCash.paid_to,
  status: props.pettyCash.status,
  date: props.pettyCash.date,
  remarks: props.pettyCash.remarks,
})

// Editing states for header fields
const editingField = ref<string | null>(null)
const editValue = ref<string>('')

// Toggle for Add New Item form
const showAddItemForm = ref(false)

// Start editing a header field
const startEditing = (field: string, currentValue: string) => {
  editingField.value = field
  editValue.value = currentValue || ''
}

// Save edited field
const saveEdit = async (field: string) => {
  if (editValue.value === form[field as keyof typeof form]) {
    editingField.value = null
    return
  }

  // Update form value
  form[field as keyof typeof form] = editValue.value
  
  // Auto-save to server
  await updateMainFields(props.pettyCash.id, {
    [field]: editValue.value
  })
  
  editingField.value = null
}

// Cancel editing
const cancelEdit = () => {
  editingField.value = null
  editValue.value = ''
}

// Handle keyboard events
const handleKeyDown = (e: KeyboardEvent, field: string) => {
  if (e.key === 'Enter') {
    e.preventDefault()
    saveEdit(field)
  }
  if (e.key === 'Escape') {
    cancelEdit()
  }
}

// Existing items
const existingItems = ref(props.pettyCash.items?.map((item: any) => ({
  ...item,
  _originalValues: {
    particulars: item.particulars,
    date: item.date,
    amount: item.amount,
    liquidation_for_date: item.liquidation_for_date
  }
})) || [])

// Auto-save main fields when they change (as backup)
const saveMainFields = async () => {
  await updateMainFields(props.pettyCash.id, {
    paid_to: form.paid_to,
    date: form.date,
    remarks: form.remarks
  })
}

// Debounced auto-save for main fields
let saveTimeout: NodeJS.Timeout
watch([() => form.paid_to, () => form.date, () => form.remarks], () => {
  clearTimeout(saveTimeout)
  saveTimeout = setTimeout(saveMainFields, 1000)
})

// Handle item updates
const handleItemUpdated = (updatedItem: any) => {
  const index = existingItems.value.findIndex(i => i.id === updatedItem.id)
  if (index !== -1) {
    existingItems.value[index] = updatedItem
  }
}

// Handle item deletion
const handleItemDeleted = (deletedItemId: number) => {
  existingItems.value = existingItems.value.filter(i => i.id !== deletedItemId)
}

// Handle new item added
const handleItemAdded = (newItem: any) => {
  existingItems.value.push(newItem)
  // Close the form after adding
  showAddItemForm.value = false
}

// Toggle add item form
const toggleAddItemForm = () => {
  showAddItemForm.value = !showAddItemForm.value
}

// All items for totals
const allItemsForTotals = computed(() => existingItems.value)

// Submit for audit
const isSubmitDialogOpen = ref(false)
const submitConfirmChecked = ref(false)

const confirmAndSubmit = async () => {
  if (!submitConfirmChecked.value) {
    toast.warning('Please confirm before submitting.')
    return
  }

  await router.put(`/petty-cash/${props.pettyCash.id}/submit`, {}, {
    onSuccess: () => {
      toast.success('Petty Cash submitted for audit!')
      isSubmitDialogOpen.value = false
      submitConfirmChecked.value = false
      setTimeout(() => {
        router.visit(route('petty-cash.index'))
      }, 2000)
    },
    onError: () => toast.error('Failed to submit petty cash.')
  })
}

watch(() => usePage().props.success, (success) => {
  if (success) {
    toast.success(success)
  }
})

watch(() => usePage().props.saved_item, (savedItem) => {
  if (savedItem) {
    const index = existingItems.value.findIndex(i => i.id === savedItem.id)
    if (index !== -1) {
      existingItems.value[index] = savedItem
    } else {
      existingItems.value.push(savedItem)
    }
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Voucher Header with Double-click to Edit -->
    <div class="overflow-hidden">
      <Table>
        <TableBody>
          <TableRow class="divide-x">
            <!-- PCV No (Read-only) -->
            <TableCell class="font-medium w-[20%]">
              <div class="space-y-1">
                <div class="text-xs text-muted-foreground">PCV No</div>
                <div class="font-semibold">{{ props.pettyCash.pcv_no }}</div>
              </div>
            </TableCell>
            
            <!-- Status (Read-only) -->
            <TableCell class="w-[20%]">
              <div class="space-y-1">
                <div class="text-xs text-muted-foreground">Status</div>
                <div class="capitalize">
                  <span :class="{
                    'text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full text-xs': props.pettyCash.status === 'pending',
                    'text-blue-600 bg-blue-100 px-2 py-1 rounded-full text-xs': props.pettyCash.status === 'for liquidation',
                    'text-green-600 bg-green-100 px-2 py-1 rounded-full text-xs': props.pettyCash.status === 'submitted',
                    'text-gray-600 bg-gray-100 px-2 py-1 rounded-full text-xs': props.pettyCash.status === 'draft'
                  }">
                    {{ props.pettyCash.status }}
                  </span>
                </div>
              </div>
            </TableCell>
            
            <!-- Paid To (Editable) -->
            <TableCell class="w-[40%] cursor-pointer hover:bg-muted/50 transition-colors" 
                       @dblclick="startEditing('paid_to', form.paid_to)">
              <div class="space-y-1">
                <div class="text-xs text-muted-foreground flex items-center gap-1">
                  Paid To
                  <span class="text-[10px] text-muted-foreground/70">(double-click to edit)</span>
                </div>
                <div v-if="editingField === 'paid_to'" class="flex items-center gap-2">
                  <Input
                    v-model="editValue"
                    placeholder="Enter paid to"
                    class="h-8"
                    @blur="saveEdit('paid_to')"
                    @keydown.enter="saveEdit('paid_to')"
                    @keydown.escape="cancelEdit"
                    autofocus
                  />
                  <span class="text-xs text-muted-foreground animate-pulse">Auto-saves</span>
                </div>
                <div v-else class="font-medium flex items-center gap-2 group">
                  {{ form.paid_to || '—' }}
                  <Pencil class="h-3 w-3 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
              </div>
            </TableCell>
            
            <!-- Date (Editable) -->
            <TableCell class="w-[20%] cursor-pointer hover:bg-muted/50 transition-colors"
                       @dblclick="startEditing('date', form.date)">
              <div class="space-y-1">
                <div class="text-xs text-muted-foreground flex items-center gap-1">
                  Date
                  <span class="text-[10px] text-muted-foreground/70">(double-click to edit)</span>
                </div>
                <div v-if="editingField === 'date'" class="flex items-center gap-2">
                  <Input
                    v-model="editValue"
                    type="date"
                    class="h-8 w-auto"
                    @blur="saveEdit('date')"
                    @keydown.enter="saveEdit('date')"
                    @keydown.escape="cancelEdit"
                    autofocus
                  />
                  <span class="text-xs text-muted-foreground animate-pulse">Auto-saves</span>
                </div>
                <div v-else class="font-medium flex items-center gap-2 group">
                  {{ formatDate(form.date) }}
                  <Pencil class="h-3 w-3 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
                </div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
    
    <!-- Remarks with Double-click to Edit -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <Alert class="cursor-pointer hover:bg-muted/50 transition-colors" 
               @dblclick="startEditing('remarks', form.remarks)">
          <Rocket class="h-4 w-4" />
          <AlertTitle class="flex items-center gap-2">
            Remarks
            <span class="text-xs font-normal text-muted-foreground">(double-click to edit)</span>
          </AlertTitle>
          <AlertDescription>
            <div v-if="editingField === 'remarks'" class="mt-2 space-y-2">
              <Textarea
                v-model="editValue"
                placeholder="Add remarks..."
                class="min-h-[100px]"
                @blur="saveEdit('remarks')"
                @keydown.enter.prevent="saveEdit('remarks')"
                @keydown.escape="cancelEdit"
                autofocus
              />
              <div class="flex justify-end">
                <span class="text-xs text-muted-foreground animate-pulse">Auto-saves on blur</span>
              </div>
            </div>
            <div v-else class="flex items-center justify-between group">
              <span :class="{ 'text-muted-foreground italic': !form.remarks }">
                {{ form.remarks || 'No remarks yet. Double-click to add...' }}
              </span>
              <Pencil class="h-3 w-3 text-muted-foreground opacity-0 group-hover:opacity-100 transition-opacity" />
            </div>
          </AlertDescription>
        </Alert>
      </div>
    </div>

    <!-- Existing Items -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Items</h3>
        <span class="text-sm text-muted-foreground">Double-click to edit - auto-saves</span>
      </div>
      
      <ItemsTable
        :items="existingItems"
        :is-existing="true"
        :petty-cash-id="props.pettyCash.id"
        @item-updated="handleItemUpdated"
        @item-deleted="handleItemDeleted"
      />
      
      <TotalsSummary :items="allItemsForTotals" />
    </div>

    <!-- Add New Item Form (Hidden by default) -->
    <div v-if="showAddItemForm" class="border rounded-xl p-4 bg-muted/5">
      <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold flex items-center gap-2">
          Add New Item
        </h3>
        <Button 
          variant="ghost" 
          size="sm" 
          @click="toggleAddItemForm"
          class="gap-2"
        >
          <X class="h-4 w-4" />
          Close
        </Button>
      </div>

      <ItemForm
        :existing-items="existingItems"
        :status="props.pettyCash.status"
        :is-cash-advance-user="user.is_cash_advance === 1"
        :petty-cash-id="props.pettyCash.id"
        @item-added="handleItemAdded"
      />
    </div>

    <!-- Action Buttons Row -->
    <div class="flex justify-end gap-3">
      <!-- Add Item Button -->
      <Button
        v-if="!showAddItemForm"
        variant="outline"
        @click="toggleAddItemForm"
        class="gap-2"
      >
        <PlusCircle class="h-4 w-4" />
        Add Item
      </Button>

      <!-- Submit Button -->
      <Button
        v-if="user.access_id == 3"
        @click="isSubmitDialogOpen = true"
        class="bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="props.pettyCash.status === 'submitted'"
      >
        <Forward class="mr-2 h-4 w-4" /> Submit Petty Cash
      </Button>
    </div>

    <!-- Submit Confirmation Dialog -->
    <ConfirmationDialog
      v-model:open="isSubmitDialogOpen"
      v-model:checked="submitConfirmChecked"
      title="Submit Petty Cash"
      description="Please confirm that all entries are correct before submitting this petty cash voucher for audit."
      confirm-label="Confirm & Submit"
      @confirm="confirmAndSubmit"
    />
  </div>
</template>