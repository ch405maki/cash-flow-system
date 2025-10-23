<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3';
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
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"
import { Separator } from '@/components/ui/separator'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket } from 'lucide-vue-next'

const user = usePage().props.auth.user;
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
  liquidation_for_date: '',
  amount: 0,
  receipt: null as File | null
})

const fileKey = ref(0)

const totalAmount = computed(() => {
  return existingItems.value
    .filter(item => ['Liquidation', 'Reimbursement'].includes(item.type))
    .reduce((sum, item) => sum + Number(item.amount || 0), 0)
})


/**
 * Compute totals per date (cash advance + liquidation combined)
 */
const groupedByDate = computed(() => {
  const grouped: Record<string, { cashAdvance: number; liquidation: number }> = {}

  // Only include saved items
  existingItems.value.forEach(item => {
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
  const allItems = existingItems.value

  allItems.forEach(item => {
    const key =
      item.type === 'Liquidation'
        ? item.liquidation_for_date || item.date
        : item.date

    if (!map[key]) map[key] = 0
  })

  Object.entries(groupedByDate.value).forEach(([date, totals]) => {
    if (totals.cashAdvance > 0 && totals.liquidation < totals.cashAdvance) {
      map[date] = totals.cashAdvance - totals.liquidation
    } else {
      map[date] = 0
    }
  })

  return map
})


/**
 * Returns background class for any item based on date totals
 */
const getRowClass = (item: any) => {
  const key = item.type === 'Liquidation'
    ? item.liquidation_for_date || item.date
    : item.date

  const totals = groupedByDate.value[key]

  if (!totals || totals.cashAdvance === 0) return ''

  if (totals.liquidation >= totals.cashAdvance) {
    return 'bg-green-100'
  }
  if (totals.liquidation > 0 && totals.liquidation < totals.cashAdvance) {
    return 'bg-yellow-100'
  }
  if (item.type?.toLowerCase() === 'cash advance') {
    return 'bg-rose-100'
  }
  return ''
}

const addItem = () => {
  if (!newItem.type || !newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please select type and fill all fields before adding.')
    return
  }

  // Require receipt for liquidation
  if (newItem.type === 'Liquidation' && !newItem.receipt) {
    toast.error('Receipt attachment is required for Liquidation items.')
    return
  }

  // Prevent adding Liquidation if no matching Cash Advance exists
  if (newItem.type === 'Liquidation') {
    if (!newItem.liquidation_for_date) {
      toast.warning('Please select a Liquidation Dated value.')
      return
    }

    // Normalize to local YYYY-MM-DD in Asia/Manila
    const normalizeToManilaDate = (dateStr: string | null | undefined) => {
      if (!dateStr) return null
      try {
        // If it's already a plain date like '2025-10-14', Date will parse it as local.
        // If it's an ISO UTC string like '2025-10-13T16:00:00.000000Z', this will convert it to Manila date.
        const d = new Date(dateStr)
        // 'en-CA' gives YYYY-MM-DD format
        return d.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' })
      } catch (e) {
        return null
      }
    }

    const incomingNorm = normalizeToManilaDate(newItem.liquidation_for_date)

    const hasMatchingCashAdvance = existingItems.value.some((item) => {
      if (item.type !== 'Cash Advance') return false

      // check both item.date and item.liquidation_for_date (if needed)
      const itemDateNorm = normalizeToManilaDate(item.date)
      const itemLiquidationForDateNorm = normalizeToManilaDate(item.liquidation_for_date)

      // Compare incoming against the field where cash advance date is stored (likely item.date)
      return itemDateNorm === incomingNorm
    })

    if (!hasMatchingCashAdvance) {
      toast.error(
        `No Cash Advance found for ${newItem.liquidation_for_date} (Manila). You cannot add a Liquidation for this date.`
      )
      return
    }
  }

  // All validations passed
  form.items.push({ ...newItem })
  newItem.type = ''
  newItem.particulars = ''
  newItem.date = ''
  newItem.liquidation_for_date = ''
  newItem.amount = 0
  newItem.receipt = null
  fileKey.value++
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
        `Liquidation for ${date} is less than cash advance (₱${totals.liquidation.toLocaleString()} / ₱${totals.cashAdvance.toLocaleString()}).`
      )
    }
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
    data.append(`items[${i}][liquidation_for_date]`, item.liquidation_for_date)
    data.append(`items[${i}][amount]`, item.amount.toString())
    if (item.receipt) data.append(`items[${i}][receipt]`, item.receipt)
  })

  await router.post(`/petty-cash/${props.pettyCash.id}`, data, {
    onSuccess: () => {
      toast.success('Petty Cash Voucher updated successfully!')
      window.location.reload()
    },
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

const isUpdateDialogOpen = ref(false)
const updateConfirmChecked = ref(false)

const confirmAndSubmit = async () => {
  if (!confirmChecked.value) {
    toast.warning('Please confirm before submitting.')
    return
  }

  await router.put(`/petty-cash/${props.pettyCash.id}/submit`, {}, {
    onSuccess: () => {
      toast.success('Petty Cash submitted for audit!')

      // Close dialog and reset checkbox
      isSubmitDialogOpen.value = false
      confirmChecked.value = false

      // Redirect after 2 seconds
      setTimeout(() => {
        router.visit(route('petty-cash.index'))
      }, 2000)
    },
    onError: () => toast.error('Failed to submit petty cash.')
  })
}


const totalsByType = computed(() => {
  const allItems = existingItems.value
  return allItems.reduce(
    (totals, item) => {
      const type = item.type || 'Unknown'
      totals[type] = (totals[type] || 0) + Number(item.amount || 0)
      return totals
    },
    {} as Record<string, number>
  )
})


const handleUpdateConfirm = async () => {
  if (!updateConfirmChecked.value) {
    toast.warning('Please confirm before updating.')
    return
  }

  isUpdateDialogOpen.value = false
  updateConfirmChecked.value = false
  await submitForm()
}

</script>

<template>
  <div class="space-y-6">
    <!-- Voucher Header -->
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
                <div>{{ props.pettyCash.paid_to }}</div>
              </div>
            </TableCell>
            <TableCell class="w-[20%]">
              <div>
                <div class="text-xs text-muted-foreground mb-1">Date</div>
                <div>{{ formatDate(props.pettyCash.date) }}</div>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="md:col-span-2">
          <Alert>
            <Rocket class="h-4 w-4" />
            <AlertTitle>Remarks!</AlertTitle>
            <AlertDescription>{{ props.pettyCash.remarks || 'No remarks yet.' }}</AlertDescription>
          </Alert>
        </div>
      </div>

    <!-- Existing Items Table -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-2">Existing Items</h3>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[20%]">Type</TableHead>
            <TableHead class="w-[30%]">Particulars</TableHead>
            <TableHead class="w-[15%]">Date</TableHead>
            <TableHead class="w-[15%] text-right">Amount</TableHead>
            <TableHead class="w-[20%]">Receipt</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="item in existingItems"
            :key="item.id"
            :class="getRowClass(item)"
          >
            <TableCell class="font-semibold">
              {{ item.type }}
              <span 
                v-if="item?.liquidation_for_date != null" 
                class="text-sm font-normal text-muted-foreground block"
              >
                ({{ formatDate(item.liquidation_for_date) }})
              </span>
            </TableCell>
            <TableCell>{{ item.particulars }}</TableCell>
            <TableCell>{{ formatDate(item.date) }}</TableCell>
            <TableCell class="text-right font-medium">
              {{ item.amount.toLocaleString() }}
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
              <span v-else class="text-muted-foreground italic text-sm">No file</span>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
      
      <!-- Running Totals per Type -->
        <div class="mt-4 flex justify-end">
          <div class="grid grid-cols-1">
            <h3 class="text-md font-semibold">Running Totals</h3>
            <div v-for="(amount, type) in totalsByType" :key="type" class="flex space-x-2">
              <h1 class="font-medium w-48">{{ type }}:</h1>
              <h1 class="font-bold w-48 text-right">₱{{ amount.toLocaleString() }}</h1>
            </div>

            <!-- Show change per date - Only if there are liquidation items -->
            <div 
              v-for="(change, date) in changeByDate" 
              :key="date" 
              class="flex space-x-2 text-rose-600"
              v-if="existingItems.some(item => item.type === 'Liquidation')"
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
      </div>

    <Separator />
    <!-- New Items Table -->
    <div v-if="form.items.length">
      <h3 class="text-lg font-semibold mb-3">New Items (Unsubmitted)</h3>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Type</TableHead>
            <TableHead>Particulars</TableHead>
            <TableHead>Date</TableHead>
            <TableHead>Liquidation Dated</TableHead>
            <TableHead class="text-right">Amount</TableHead>
            <TableHead>Receipt</TableHead>
            <TableHead class="text-center">Action</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="(item, index) in form.items"
            :key="index"
            :class="getRowClass(item)"
          >
            <TableCell class="py-3">{{ item.type }}</TableCell>
            <TableCell 
              @dblclick="startEditing(index, 'particulars')"
              class="cursor-pointer py-3"
            >
              <template v-if="isEditing(index, 'particulars')">
                <Input
                  v-model="editValue"
                  @blur="saveEdit(index, 'particulars')"
                  @keyup.enter="saveEdit(index, 'particulars')"
                  class="w-full"
                />
              </template>
              <template v-else>{{ item.particulars }}</template>
            </TableCell>
            <TableCell 
              @dblclick="startEditing(index, 'date')"
              class="cursor-pointer py-3"
            >
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
            </TableCell>
            <TableCell 
              @dblclick="startEditing(index, 'liquidation_for_date')"
              class="cursor-pointer py-3"
            >
              <template v-if="isEditing(index, 'liquidation_for_date')">
                <Input
                  v-model="editValue"
                  type="date"
                  @blur="saveEdit(index, 'liquidation_for_date')"
                  @keyup.enter="saveEdit(index, 'liquidation_for_date')"
                  class="w-full"
                />
              </template>
              <template v-else>{{ formatDate(item.liquidation_for_date) }}</template>
            </TableCell>
            <TableCell 
              @dblclick="startEditing(index, 'amount')"
              class="text-right cursor-pointer py-3"
            >
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
              <template v-else class="font-medium">{{ item.amount.toLocaleString() }}</template>
            </TableCell>
            <TableCell class="py-3">
              <span v-if="item.receipt" class="text-sm">{{ item.receipt.name }}</span>
              <span v-else class="text-muted-foreground italic text-sm">No file</span>
            </TableCell>
            <TableCell class="text-center py-3">
              <Button variant="destructive" size="sm" @click="removeNewItem(index)">
                Remove
              </Button>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Add New Item Form -->
    <div class="space-y-3">
      <h3 class="text-lg font-semibold">Add New Item</h3>

      <!-- Radio buttons for Type -->
      <div>
        <Label>Type</Label>
        <RadioGroup v-model="newItem.type">
          <div  v-if=" props.pettyCash.status != 'for liquidation'" class="flex mt-2 items-center space-x-4">
            <div class="flex items-center space-x-2">
              <RadioGroupItem id="reimbursement" value="Reimbursement" />
              <Label for="reimbursement">Reimbursement</Label>
            </div>
            <div class="flex items-center space-x-4" v-if="user.is_cash_advance === 1">
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="cash-advance" value="Cash Advance" />
                <Label for="cash-advance">Cash Advance</Label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="liquidation" value="Liquidation" />
                <Label for="liquidation">Liquidation</Label>
              </div>
            </div>
          </div>
          <div v-else class="flex mt-2 items-center space-x-4">
            <div class="flex items-center space-x-4" v-if="user.is_cash_advance === 1">
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="liquidation" value="Liquidation" />
                <Label for="liquidation">Liquidation</Label>
              </div>
            </div>
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
        <div v-if="newItem.type === 'Liquidation'">
          <Label>Liquidation Dated</Label>
          <Input v-model="newItem.liquidation_for_date" type="date" />
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
      <Button
        @click="isUpdateDialogOpen = true"
        variant="outline"
        :disabled="props.pettyCash.status == 'submitted' || form.items.length === 0"
      >
        Update Petty Cash
      </Button>

      <Button
        v-if="user.access_id == 3"
        @click="isSubmitDialogOpen = true"
        class="bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed"
        :disabled="props.pettyCash.status === 'submitted' || form.items.some(item => !item.id)"
      >
        Submit Petty Cash
      </Button>
    </div>

    <!-- Confirmation Dialog -->
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
            class="disabled:opacity-50 disabled:cursor-not-allowed" 
            :disabled="!confirmChecked" 
            @click="confirmAndSubmit"
            >
            Confirm & Submit
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Update Confirmation Dialog -->
    <Dialog v-model:open="isUpdateDialogOpen">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Confirm Update</DialogTitle>
          <DialogDescription>
            You are about to update this Petty Cash Voucher.  
            Please review your entries before confirming.
          </DialogDescription>
        </DialogHeader>

        <div class="flex items-center space-x-2 mt-4">
          <Checkbox id="update-confirm" v-model:checked="updateConfirmChecked" />
          <Label for="update-confirm">
            I have reviewed all details and wish to proceed with the update.
          </Label>
        </div>

        <DialogFooter class="mt-4 flex justify-end space-x-2">
          <Button variant="outline" @click="isUpdateDialogOpen = false">
            Cancel
          </Button>
          <Button
            class="disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!updateConfirmChecked"
            @click="handleUpdateConfirm"
          >
            Confirm & Update
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

  </div>
</template>
