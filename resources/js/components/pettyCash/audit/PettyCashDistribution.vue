
<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket } from 'lucide-vue-next'
import { formatDate } from '@/lib/utils'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription
} from '@/components/ui/dialog'
import {
  Select,
  SelectTrigger,
  SelectValue,
  SelectContent,
  SelectItem,
} from '@/components/ui/select'

const user = usePage().props.auth.user

const props = defineProps<{
  pettyCash: any
  accounts: any[]
}>()

const existingItems = ref(props.pettyCash.items ?? [])
const toast = useToast()

// Add refs for dialog open states
const normalApprovalDialogOpen = ref(false)
const liquidationDialogOpen = ref(false)

const confirmApproval = ref(false)
const confirmLiquidation = ref(false)

const approval = reactive({
  remarks: ''
})

const distribution = reactive({
  account_id: '',
  amount: '',
  date: new Date().toISOString().slice(0, 10),
})

const submitDistribution = async () => {
  router.post(
    route('audit.petty-cash.distribution', props.pettyCash.id),
    distribution,
    {
      preserveScroll: true,
      only: ['pettyCash'],
      onSuccess: () => {
        toast.success('Distribution added successfully!')
        distribution.account_id = ''
        distribution.amount = ''
        distribution.date = new Date().toISOString().slice(0, 10)
      },
      onError: () => {
        toast.error('Failed to save distribution.')
      },
    }
  )
}

// reactive form items (if you’re adding new ones)
const form = reactive({
  items: [] as any[], // or proper interface if typed
})

// compute total amount including both existing + new items
const totalAmount = computed(() => {
  const allItems = [...existingItems.value, ...form.items]

  return allItems.reduce((sum, item) => {
    if (item.type === 'Liquidation' || item.type === 'Reimbursement') {
      return sum + (Number(item.amount) || 0)
    }
    return sum
  }, 0)
})

// total per type
const totalsByType = computed(() => {
  const totals: Record<string, number> = {}
  existingItems.value.forEach(item => {
    totals[item.type] = (totals[item.type] || 0) + Number(item.amount)
  })
  return totals
})

const hasLiquidation = computed(() => {
  const allItems = [...existingItems.value, ...form.items]
  return allItems.some(item => item.type === 'Liquidation')
})


// group by date to color rows
const groupedByDate = computed(() => {
  const map: Record<string, { cashAdvance: number; liquidation: number }> = {}
  existingItems.value.forEach(item => {
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

const handleApproval = async (type: 'normal' | 'liquidation') => {
  try {
    if (type === 'normal') {
      await submitApproval()
      confirmApproval.value = false
      normalApprovalDialogOpen.value = false // Close the dialog
    } else {
      await submitApprovalLiquidate()
      confirmLiquidation.value = false
      liquidationDialogOpen.value = false // Close the dialog
    }
  } catch (error) {
    toast.error('Approval failed')
  }
}

const submitApproval = async () => {
  try {
    await router.post(`/petty-cash/${props.pettyCash.id}/approve`, {  
      remarks: approval.remarks
    })
    toast.success('Approval submitted!')
    approval.remarks = ''
    return true // Return success
  } catch (error) {
    toast.error('Failed to submit approval')
    throw error // Re-throw to be caught in handleApproval
  }
}

const submitApprovalLiquidate = async () => {
  try {
    await router.post(`/petty-cash/${props.pettyCash.id}/approveLiquidate`, {  
      remarks: approval.remarks
    })
    toast.success('Approval submitted!')
    approval.remarks = ''
    return true // Return success
  } catch (error) {
    toast.error('Failed to submit approval')
    throw error // Re-throw to be caught in handleApproval
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Voucher Header -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label>PCV No: {{ props.pettyCash.pcv_no }}</Label><br />
        <Label>Paid To: {{ props.pettyCash.paid_to }}</Label><br />
        <Label>Status: <span class="capitalize">{{ props.pettyCash.status }}</span></Label><br />
        <Label>Date: {{ props.pettyCash.date }}</Label>
      </div>
      <div class="md:col-span-2">
        <Alert>
          <Rocket class="h-4 w-4" />
          <AlertTitle>Remarks!</AlertTitle>
          <AlertDescription>{{ props.pettyCash.remarks || 'No remarks yet.' }}</AlertDescription>
        </Alert>
      </div>
    </div>
    
    <!-- Existing Items -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">Existing Items</h3>
      <table class="w-full border-collapse text-sm">
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
            <td class="p-2 font-semibold">
              {{ item.type }}
              <span v-if="item.liquidation_for_date" class="text-xs text-gray-500">
                ({{ formatDate(item.liquidation_for_date) }})
              </span>
            </td>
            <td class="p-2">{{ item.particulars }}</td>
            <td class="p-2">{{ formatDate(item.date) }}</td>
            <td class="p-2 text-right">₱{{ Number(item.amount).toLocaleString() }}</td>
            <td class="p-2">
              <a
                v-if="item.receipt"
                :href="`/storage/${item.receipt}`"
                target="_blank"
                class="text-blue-600 underline"
              >
                View
              </a>
              <span v-else class="italic text-gray-400">No file</span>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="mt-4 flex justify-end">
        <div class="grid grid-cols-1 gap-2">
            <h3 class="text-md font-semibold">Running Totals</h3>
            <div v-for="(amount, type) in totalsByType" :key="type" class="flex space-x-2">
              <h1 class="font-medium w-48">{{ type }}:</h1>
              <h1 class="font-bold w-48 text-right">₱{{ amount.toLocaleString() }}</h1>
            </div>
            <div class="font-bold flex space-x-2">
              <h1 class="w-48">
                Grand Total: 
              </h1>
              <h1 class="w-48 text-right">₱{{ totalAmount.toLocaleString() }}</h1>
            </div>
          </div>
        </div>
      </div>

    <!-- Distribution of Expense -->
    <div class="mt-6 border rounded-lg p-4">
      <h3 class="text-lg font-semibold mb-2">Distribution of Expense</h3>

      <form @submit.prevent="submitDistribution" class="space-y-4">
        <div class="grid grid-cols-[1fr_1fr_1fr_auto] gap-4 items-end">
          <!-- Account Name -->
              <div>
                <Label>Account Name</Label>
                <Select v-model="distribution.account_id">
                  <SelectTrigger class="w-full">
                    <SelectValue placeholder="Select Account" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem
                      v-for="account in props.accounts"
                      :key="account.id"
                      :value="account.id"
                    >
                      {{ account.account_title }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>

          <!-- Amount -->
          <div>
            <Label>Amount</Label>
            <Input v-model="distribution.amount" type="number" step="0.01" class="w-full" />
          </div>

          <!-- Date -->
          <div>
            <Label>Date</Label>
            <Input v-model="distribution.date" type="date" class="w-full" />
          </div>

          <!-- Save Button -->
          <div class="flex justify-end">
            <Button
              type="submit"
              class="px-4 whitespace-nowrap"
              :disabled="!distribution.account_id || !distribution.amount || !distribution.date"
            >
              Save
            </Button>
          </div>
        </div>
      </form>
    </div>

    <!-- Existing Distribution Records -->
    <div v-if="props.pettyCash.distribution_expenses?.length" class="mt-6 border rounded-lg p-4">
      <h3 class="text-lg font-semibold mb-2">Existing Distribution Records</h3>
      <table class="w-full border-collapse text-sm">
        <thead class="bg-muted">
          <tr>
            <th class="p-2 border-b text-left">Account Name</th>
            <th class="p-2 border-b text-right">Amount</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="record in props.pettyCash.distribution_expenses"
            :key="record.id"
            class="border-b"
          >
            <td class="p-2">{{ record.account_name }}</td>
            <td class="p-2 text-right">₱{{ Number(record.amount).toLocaleString() }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Remarks -->
    <div class="mt-6 border rounded-lg p-4" v-if="user.role === 'audit'">
      <div class="mb-4">
        <Label for="approvalRemarks">Remarks</Label>
        <Textarea
          id="approvalRemarks"
          v-model="approval.remarks"
          rows="3"
          class="block w-full border rounded-md p-2"
          placeholder="Add optional remarks for this approval..."
        ></Textarea>
      </div>

      <div class="flex justify-end space-x-2">
        <div class="flex justify-end space-x-2">
          <!-- Normal Approval -->
          <Dialog v-if="user.role === 'audit' && !hasLiquidation" v-model:open="normalApprovalDialogOpen">
            <DialogTrigger as-child>
              <Button :disabled="!approval.remarks">
                Confirm Approval
              </Button>
            </DialogTrigger>

            <DialogContent>
              <DialogHeader>
                <DialogTitle>Confirm Approval</DialogTitle>
                <DialogDescription>
                  Please confirm that you've reviewed this petty cash request before approving.
                </DialogDescription>
              </DialogHeader>

              <div class="flex items-center space-x-2 mt-4">
                <Checkbox id="confirmApproval" v-model:checked="confirmApproval" />
                <Label for="confirmApproval">I confirm the accuracy of this request.</Label>
              </div>

              <DialogFooter class="mt-4">
                <Button variant="outline" @click="normalApprovalDialogOpen = false">Cancel</Button>
                <Button :disabled="!confirmApproval" @click="handleApproval('normal')">
                  Approve
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <!-- Liquidation Approval -->
          <Dialog v-if="user.role === 'audit' && hasLiquidation" v-model:open="liquidationDialogOpen">
            <DialogTrigger as-child>
              <Button :disabled="!approval.remarks">
                Confirm Liquidation Approval
              </Button>
            </DialogTrigger>

            <DialogContent>
              <DialogHeader>
                <DialogTitle>Confirm Liquidation Approval</DialogTitle>
                <DialogDescription>
                  This petty cash request contains liquidation items. Please confirm before final approval.
                </DialogDescription>
              </DialogHeader>

              <div class="flex items-center space-x-2 mt-4">
                <Checkbox id="confirmLiquidation" v-model:checked="confirmLiquidation" />
                <Label for="confirmLiquidation">I confirm liquidation details are correct.</Label>
              </div>

              <DialogFooter class="mt-4">
                <Button variant="outline" @click="liquidationDialogOpen = false">Cancel</Button>
                <Button :disabled="!confirmLiquidation" @click="handleApproval('liquidation')">
                  Approve
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

        </div>
      </div>
    </div>
  </div>
</template>
