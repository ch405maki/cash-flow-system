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
import { Rocket, Forward } from 'lucide-vue-next'
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

// Auto-save main fields when they change
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
    // Update existing items array with the saved item
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
    <!-- Voucher Header with Auto-save -->
    <Table>
      <TableBody>
        <TableRow>
          <TableCell class="font-medium w-[20%] border-r">
            <div>
              <div class="text-xs text-muted-foreground mb-1">PCV No</div>
              <div>{{ props.pettyCash.pcv_no }}</div>
            </div>
          </TableCell>
          <TableCell class="w-[20%] border-r">
            <div>
              <div class="text-xs text-muted-foreground mb-1">Status</div>
              <div class="capitalize">{{ props.pettyCash.status }}</div>
            </div>
          </TableCell>
          <TableCell class="w-[40%] border-r">
            <div>
              <div class="text-xs text-muted-foreground mb-1">Paid To</div>
              <Input v-model="form.paid_to" placeholder="Paid to" />
              <span class="text-xs text-muted-foreground mt-1">Auto-saves on change</span>
            </div>
          </TableCell>
          <TableCell class="w-[20%]">
            <div>
              <div class="text-xs text-muted-foreground mb-1">Date</div>
              <Input v-model="form.date" type="date" />
              <span class="text-xs text-muted-foreground mt-1">Auto-saves on change</span>
            </div>
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
    
    <!-- Remarks with Auto-save -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="md:col-span-2">
        <Alert>
          <Rocket class="h-4 w-4" />
          <AlertTitle>Remarks</AlertTitle>
          <Textarea v-model="form.remarks" placeholder="Add remarks..." />
          <span class="text-xs text-muted-foreground mt-1">Auto-saves on change</span>
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

    <Separator />

    <!-- Add New Item Form - Auto-saves on Accept -->
    <ItemForm
      :existing-items="existingItems"
      :status="props.pettyCash.status"
      :is-cash-advance-user="user.is_cash_advance === 1"
      :petty-cash-id="props.pettyCash.id"
      @item-added="handleItemAdded"
    />

    <!-- Submit Button Only -->
    <div class="flex justify-end">
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