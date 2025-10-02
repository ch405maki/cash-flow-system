<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { usePage } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { formatDate } from '@/lib/utils';
import { Rocket } from "lucide-vue-next"
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/textarea/Textarea.vue';


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

const totalAmount = computed(() => {
  const allItems = [...existingItems.value, ...form.items]

  return allItems.reduce((sum, item) => {
    if (item.type === 'Liquidation' || item.type === 'Reimbursement') {
      return sum + (Number(item.amount) || 0)
    }
    return sum
  }, 0)
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

const approval = reactive({
  remarks: ''
})

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
  } catch (error) {
    toast.error('Failed to submit approval')
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
            <AlertDescription>
                {{ props.pettyCash.remarks }}.
            </AlertDescription>
        </Alert>
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
            <td class="p-2 font-semibold">{{ item.type }} <span v-if="item?.liquidation_for_date != null" class="text-sm font-normal">({{ formatDate(item.liquidation_for_date) }})</span></td>
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
      <!-- Running Totals per Type -->
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

      <div class="mt-6 border rounded-lg p-4">
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
            v-if="user.role === 'audit'"
            :disabled="!approval.remarks"
            @click="submitApproval"
          >
            Submit Review
          </Button>
          <Button
            v-if="user.role === 'executive_director'"
            :disabled="!approval.remarks"
            @click="submitExecutiveApproval"
          >
            Confirm Approval
          </Button>
        </div>
      </div>
    </div>
</template>
