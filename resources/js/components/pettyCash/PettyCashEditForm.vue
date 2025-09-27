<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  RadioGroup,
  RadioGroupItem
} from '@/components/ui/radio-group'
import { formatDate } from '@/lib/utils';
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter
} from '@/components/ui/dialog'
import { Checkbox } from '@/components/ui/checkbox'

const props = defineProps<{
  pettyCash: any
}>()

const toast = useToast()

const form = reactive({
  paid_to: props.pettyCash.paid_to,
  status: props.pettyCash.status,
  date: props.pettyCash.date,
  remarks: props.pettyCash.remarks,
  items: [] as any[]
})

const existingItems = ref(props.pettyCash.items ?? [])

const newItem = reactive({
  type: '',
  particulars: '',
  date: '',
  amount: 0,
  receipt: null as File | null
})

const fileKey = ref(0)

const totalAmount = computed(() => {
  const existingTotal = existingItems.value.reduce((sum, i) => sum + (Number(i.amount) || 0), 0)
  const newTotal = form.items.reduce((sum, i) => sum + (Number(i.amount) || 0), 0)
  return existingTotal + newTotal
})

/**
 * Compute totals per date (cash advance + liquidation combined)
 */
const groupedByDate = computed(() => {
  const map: Record<string, { cashAdvance: number; liquidation: number }> = {}
  const allItems = [...existingItems.value, ...form.items]
  allItems.forEach(item => {
    const key = item.date
    if (!map[key]) map[key] = { cashAdvance: 0, liquidation: 0 }
    if (item.type === 'Cash Advance') map[key].cashAdvance += Number(item.amount)
    if (item.type === 'Liquidation') map[key].liquidation += Number(item.amount)
  })
  return map
})

/**
 * Returns background class for any item based on date totals
 */
const getRowClass = (item: any) => {
  const totals = groupedByDate.value[item.date]
  if (!totals || totals.cashAdvance === 0) return '' // no cash advance = no highlight
  if (totals.liquidation >= totals.cashAdvance) {
    return 'bg-green-100'
  }
  if (totals.liquidation > 0 && totals.liquidation < totals.cashAdvance) {
    return 'bg-yellow-100'
  }
  if (item.type?.toLowerCase() === 'cash advance') {
    return 'bg-rose-100';
  }
  return ''
}

const addItem = () => {
  if (!newItem.type || !newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please select type and fill all fields before adding.')
    return
  }

  form.items.push({ ...newItem })
  newItem.type = ''
  newItem.particulars = ''
  newItem.date = ''
  newItem.amount = 0
  newItem.receipt = null
  fileKey.value++
}

const removeExistingItem = async (id: number) => {
  if (!confirm('Are you sure you want to remove this item?')) return

  await router.delete(`/petty-cash-items/${id}`, {
    onSuccess: () => {
      existingItems.value = existingItems.value.filter(item => item.id !== id)
      toast.success('Item removed.')
    },
    onError: () => toast.error('Failed to delete item.')
  })
}

const removeNewItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = async () => {
  let hasUnderLiquidation = false
  for (const [date, totals] of Object.entries(groupedByDate.value)) {
    if (totals.cashAdvance > 0 && totals.liquidation < totals.cashAdvance) {
      hasUnderLiquidation = true
      toast.warning(
        `⚠️ Liquidation for ${date} is less than cash advance (₱${totals.liquidation.toLocaleString()} / ₱${totals.cashAdvance.toLocaleString()}).`
      )
    }
  }

  if (hasUnderLiquidation) {
    if (!confirm('Some dates are under-liquidated. Do you still want to proceed?')) return
  }

  const data = new FormData()
  data.append('_method', 'PUT')
  data.append('paid_to', form.paid_to || '')
  data.append('status', form.status || '')
  data.append('date', form.date || '')
  data.append('remarks', form.remarks || '')

  form.items.forEach((item, i) => {
    data.append(`items[${i}][type]`, item.type)
    data.append(`items[${i}][particulars]`, item.particulars)
    data.append(`items[${i}][date]`, item.date)
    data.append(`items[${i}][amount]`, item.amount.toString())
    if (item.receipt) data.append(`items[${i}][receipt]`, item.receipt)
  })

  await router.post(`/petty-cash/${props.pettyCash.id}`, data, {
    onSuccess: () => toast.success('Petty Cash Voucher updated successfully!'),
    onError: () => toast.error('Failed to update voucher.')
  })
}

const editing = reactive<{ index: number | null; field: string | null }>({
  index: null,
  field: null
})
const editValue = ref('')

const startEditing = (index: number, field: string) => {
  editing.index = index
  editing.field = field
  editValue.value = form.items[index][field]
}

const isEditing = (index: number, field: string) => {
  return editing.index === index && editing.field === field
}

const saveEdit = (index: number, field: string) => {
  form.items[index][field] = field === 'amount'
    ? Number(editValue.value)
    : editValue.value
  editing.index = null
  editing.field = null
}

const isSubmitDialogOpen = ref(false)
const confirmChecked = ref(false)

const confirmAndSubmit = async () => {
  if (!confirmChecked.value) {
    toast.warning('Please confirm before submitting.')
    return
  }

  await router.put(`/petty-cash/${props.pettyCash.id}/submit`, {}, {
    onSuccess: () => {
      toast.success('Petty Cash submitted for audit!')
      isSubmitDialogOpen.value = false
      confirmChecked.value = false
    },
    onError: () => toast.error('Failed to submit petty cash.')
  })
}
</script>

<template>
  <div class="space-y-6">
    <!-- Voucher Header -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label>PCV No: {{ props.pettyCash.pcv_no }}</Label><br />
        <Label>Paid To: {{ props.pettyCash.paid_to }}</Label><br />
        <Label>Status: {{ props.pettyCash.status }}</Label><br />
        <Label>Date: {{ props.pettyCash.date }}</Label>
      </div>
      <div class="md:col-span-2">
        <Label>Remarks</Label>
        <Input v-model="form.remarks" />
      </div>
    </div>

    <!-- Existing Items Table -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">Existing Items</h3>
      <table class="w-full border-collapse">
        <thead class="bg-muted">
          <tr>
            <th class="text-left p-2 border-b">Type</th>
            <th class="text-left p-2 border-b">Particulars</th>
            <th class="text-left p-2 border-b">Date</th>
            <th class="text-right p-2 border-b">Amount</th>
            <th class="text-left p-2 border-b">Receipt</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="item in existingItems"
            :key="item.id"
            class="border-b"
            :class="getRowClass(item)"
          >
            <td class="p-2 font-semibold">{{ item.type }}</td>
            <td class="p-2">{{ item.particulars }}</td>
            <td class="p-2">{{ formatDate(item.date) }}</td>
            <td class="p-2 text-right">{{ item.amount.toLocaleString() }}</td>
            <td class="p-2">
              <a v-if="item.receipt" :href="`/storage/${item.receipt}`" target="_blank" class="text-blue-600 underline">
                View
              </a>
              <span v-else class="text-muted-foreground italic">No file</span>
            </td>
          </tr>
        </tbody>
      </table>
    </div>


    <!-- New Items Table -->
    <div v-if="form.items.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">New Items (Unsubmitted)</h3>
      <table class="w-full border-collapse">
        <thead class="bg-muted">
          <tr>
            <th class="text-left p-2 border-b">Type</th>
            <th class="text-left p-2 border-b">Particulars</th>
            <th class="text-left p-2 border-b">Date</th>
            <th class="text-right p-2 border-b">Amount</th>
            <th class="text-left p-2 border-b">Receipt</th>
            <th class="p-2 border-b text-center">Action</th>
          </tr>
        </thead>
        <tbody>
        <tr
          v-for="(item, index) in form.items"
          :key="index"
          class="border-b"
          :class="getRowClass(item)"
        >
          <!-- Type (read-only) -->
          <td class="p-2">{{ item.type }}</td>

          <!-- Particulars (inline editable) -->
          <td class="p-2" @dblclick="startEditing(index, 'particulars')">
            <template v-if="isEditing(index, 'particulars')">
              <Input
                v-model="editValue"
                @blur="saveEdit(index, 'particulars')"
                @keyup.enter="saveEdit(index, 'particulars')"
                class="w-full"
              />
            </template>
            <template v-else>{{ item.particulars }}</template>
          </td>

          <!-- Date (inline editable) -->
          <td class="p-2" @dblclick="startEditing(index, 'date')">
            <template v-if="isEditing(index, 'date')">
              <Input
                v-model="editValue"
                type="date"
                @blur="saveEdit(index, 'date')"
                @keyup.enter="saveEdit(index, 'date')"
                class="w-full"
              />
            </template>
            <template v-else>{{ formatDate(item.date) }}</template>
          </td>

          <!-- Amount (inline editable) -->
          <td class="p-2 text-right" @dblclick="startEditing(index, 'amount')">
            <template v-if="isEditing(index, 'amount')">
              <Input
                v-model.number="editValue"
                type="number"
                min="0"
                @blur="saveEdit(index, 'amount')"
                @keyup.enter="saveEdit(index, 'amount')"
                class="w-full text-right"
              />
            </template>
            <template v-else>{{ item.amount.toLocaleString() }}</template>
          </td>

          <!-- Receipt (read-only for now) -->
          <td class="p-2">
            <span v-if="item.receipt">{{ item.receipt.name }}</span>
            <span v-else class="text-muted-foreground italic">No file</span>
          </td>

          <!-- Action -->
          <td class="p-2 text-center">
            <Button variant="destructive" size="sm" @click="removeNewItem(index)">Remove</Button>
          </td>
        </tr>
      </tbody>

      </table>
    </div>

    <!-- Running Total -->
    <div class="flex justify-end font-semibold text-lg">
      Total: {{ totalAmount.toLocaleString() }}
    </div>

    <!-- Add New Item Form -->
    <div class="border rounded-xl p-4 space-y-3">
      <h3 class="text-lg font-semibold">Add New Item</h3>

      <!-- Radio buttons for Type -->
      <div>
        <Label>Type</Label>
        <RadioGroup v-model="newItem.type" class="flex mt-2 items-center">
          <div class="flex items-center space-x-2">
            <RadioGroupItem id="reimbursement" value="Reimbursement" />
            <Label for="reimbursement">Reimbursement</Label>
          </div>
          <div class="flex items-center space-x-2">
            <RadioGroupItem id="cash-advance" value="Cash Advance" />
            <Label for="cash-advance">Cash Advance</Label>
          </div>
          <div class="flex items-center space-x-2">
            <RadioGroupItem id="liquidation" value="Liquidation" />
            <Label for="liquidation">Liquidation</Label>
          </div>
        </RadioGroup>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label>Particulars</Label>
          <Input v-model="newItem.particulars" />
        </div>
        <div>
          <Label>Date</Label>
          <Input v-model="newItem.date" type="date" />
        </div>
        <div>
          <Label>Amount</Label>
          <Input v-model.number="newItem.amount" type="number" min="0" />
        </div>
        <div>
          <Label>Receipt</Label>
          <input
            :key="fileKey"
            type="file"
            class="block w-full text-sm border rounded-lg p-2"
            @change="e => newItem.receipt = e.target.files[0]"
          />
        </div>
      </div>
      <div class="flex justify-end">
        <Button variant="secondary" @click="addItem">Accept Item</Button>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex justify-end space-x-2">
      <Button @click="submitForm" variant="outline">Update Petty Cash</Button>
      <Button @click="isSubmitDialogOpen = true" class="bg-green-600 text-white hover:bg-green-700">
        Submit Petty Cash
      </Button>
    </div>

    <!-- ✅ Confirmation Dialog -->
    <Dialog v-model:open="isSubmitDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Submit Petty Cash</DialogTitle>
          <DialogDescription>
            Please confirm that all entries are correct before submitting this petty cash voucher for audit.
          </DialogDescription>
        </DialogHeader>

        <div class="flex items-center space-x-2 mt-4">
          <Checkbox id="confirm" v-model:checked="confirmChecked" />
          <Label for="confirm">I have reviewed all items and confirm they are correct.</Label>
        </div>

        <DialogFooter class="mt-4 flex justify-end space-x-2">
          <Button variant="outline" @click="isSubmitDialogOpen = false">Cancel</Button>
          <Button 
            class="bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed" 
            :disabled="!confirmChecked" 
            @click="confirmAndSubmit"
            >
            Confirm & Submit
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>
