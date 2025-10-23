<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { formatDate } from '@/lib/utils';
import { Rocket } from "lucide-vue-next"
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/textarea/Textarea.vue';
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
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table"

const user = usePage().props.auth.user;
const props = defineProps<{
  pettyCash: any
}>()

const toast = useToast()

// Add refs for dialog open states
const normalApprovalDialogOpen = ref(false)
const liquidationDialogOpen = ref(false)

const form = reactive({
  paid_to: props.pettyCash.paid_to,
  status: props.pettyCash.status,
  date: props.pettyCash.date,
  remarks: props.pettyCash.remarks,
  items: [] as any[]
})

const existingItems = ref(props.pettyCash.items ?? [])

const totalAmount = computed(() => {
  const allItems = [...existingItems.value, ...form.items]

  return allItems.reduce((sum, item) => {
    if (item.type === 'Liquidation' || item.type === 'Reimbursement') {
      return sum + (Number(item.amount) || 0)
    }
    return sum
  }, 0)
})

const hasLiquidation = computed(() => {
  const allItems = [...existingItems.value, ...form.items]
  return allItems.some(item => item.type === 'Liquidation')
})


const groupedByDate = computed(() => {
  const map: Record<string, { cashAdvance: number; liquidation: number }> = {}
  const allItems = [...existingItems.value, ...form.items]

  allItems.forEach(item => {
    // Group by *cash advance date* (or liquidation_for_date if type is liquidation)
    const key = item.type === 'Liquidation'
      ? item.liquidation_for_date || item.date
      : item.date

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

const totalsByType = computed(() => {
  const allItems = [...existingItems.value, ...form.items]
  return allItems.reduce(
    (totals, item) => {
      const type = item.type || 'Unknown'
      totals[type] = (totals[type] || 0) + Number(item.amount || 0)
      return totals
    },
    {} as Record<string, number>
  )
})

const changeAmount = computed(() => {
  const cashAdvance = totalsByType.value['Cash Advance'] || 0
  const liquidation = totalsByType.value['Liquidation'] || 0
  const difference = cashAdvance - liquidation

  // Only show if positive
  return difference > 0 ? difference : null
})



const approval = reactive({
  remarks: ''
})

const confirmApproval = ref(false)
const confirmLiquidation = ref(false)

const handleApproval = async (type: 'normal' | 'liquidation') => {
  try {
    if (type === 'normal') {
      await submitExecutiveApproval()
      confirmApproval.value = false
      normalApprovalDialogOpen.value = false // Close the dialog
    } else {
      await submitExecutiveApprovalLiquidate()
      confirmLiquidation.value = false
      liquidationDialogOpen.value = false // Close the dialog
    }
  } catch (error) {
    toast.error('Approval failed')
  }
}

const submitApproval = async () => {
  try {
    await router.post(`/petty-cash/${props.pettyCash.id}/remarks`, {  
      remarks: approval.remarks
    })
    toast.success('Remarks submitted!')
    approval.remarks = ''
  } catch (error) {
    toast.error('Failed to submit approval')
  }
}

const submitExecutiveApproval = async () => {
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

const submitExecutiveApprovalLiquidate = async () => {
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
      <div class="grid grid-cols-1 md:grid-cols-1 gap-4">
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
      <div class="md:col-span-2">
        <Alert>
            <Rocket class="h-4 w-4" />
            <AlertTitle>Remarks!</AlertTitle>
            <AlertDescription>
                {{ props.pettyCash.remarks }}.
            </AlertDescription>
        </Alert>
      </div>
    </div>

    <!-- Existing Items Table -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">Existing Items</h3>
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
        <div class="grid grid-cols-1 gap-2">
          <h3 class="text-md font-semibold">Running Totals</h3>

          <div v-for="(amount, type) in totalsByType" :key="type" class="flex space-x-2">
            <h1 class="font-medium w-48">{{ type }}:</h1>
            <h1 class="font-bold w-48 text-right">₱{{ amount.toLocaleString() }}</h1>
          </div>

          <div
            v-if="changeAmount"
            class="font-bold flex space-x-2 text-red-600"
          >
            <h1 class="w-48">
              Change Amount:
            </h1>
            <h1 class="w-48 text-right">₱{{ changeAmount.toLocaleString() }}</h1>
          </div>
          <div class="flex items-center justify-between mt-2 border-t border-gray-400 pt-2 font-bold">
            <h1>Grand Total</h1>
            <h1 class="text-right">₱{{ totalAmount.toLocaleString() }}</h1>
          </div>
        </div>
      </div>
      </div>

      <!-- Existing Distribution Records -->
    <div v-if="props.pettyCash.distribution_expenses?.length" class="border rounded-lg p-4">
      <h3 class="text-lg font-semibold mb-2">Distribution of Expense</h3>
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead class="w-[70%]">Account Name</TableHead>
            <TableHead class="w-[30%] text-right">Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow
            v-for="record in props.pettyCash.distribution_expenses"
            :key="record.id"
          >
            <TableCell>{{ record.account_name }}</TableCell>
            <TableCell class="text-right font-medium">
              ₱{{ Number(record.amount).toLocaleString() }}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <div class="mt-6 border rounded-lg p-4" v-if="user.role === 'audit'">
      <!-- Remarks -->
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

      <!-- Submit Button -->
      <div class="flex justify-end space-x-2">
        <Button
          :disabled="!approval.remarks"
          @click="submitApproval"
        >
          Submit Review
        </Button>
      </div>
    </div>
    <div class="mt-6 border rounded-lg p-4" v-if="user.role === 'executive_director'">
      <!-- Remarks -->
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

      <!-- Submit Button -->
      <div class="flex justify-end space-x-2">
        <!-- Normal Approval -->
        <Dialog v-if="user.role === 'executive_director' && !hasLiquidation" v-model:open="normalApprovalDialogOpen">
          <DialogTrigger as-child>
            <Button :disabled="!approval.remarks || props.pettyCash.status == 'approved'">
              Confirm Approval
            </Button>
          </DialogTrigger>

          <DialogContent>
            <DialogHeader>
              <DialogTitle>Confirm Executive Approval</DialogTitle>
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
        <Dialog v-if="user.role === 'executive_director' && hasLiquidation" v-model:open="liquidationDialogOpen">
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
</template>