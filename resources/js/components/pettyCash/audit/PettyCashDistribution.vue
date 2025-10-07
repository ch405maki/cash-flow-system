<script setup lang="ts">
import { reactive } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket } from 'lucide-vue-next'
import { formatDate } from '@/lib/utils'

const user = usePage().props.auth.user

const props = defineProps<{
  pettyCash: any
  accounts: any[]
}>()

const toast = useToast()

const approval = reactive({
  remarks: ''
})

// ✅ Correct naming (amount not ammount)
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

const submitApproval = async () => {
  try {
    await router.post(`/petty-cash/${props.pettyCash.id}/remarks`, { remarks: approval.remarks })
    toast.success('Remarks submitted!')
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
          <AlertDescription>{{ props.pettyCash.remarks || 'No remarks yet.' }}</AlertDescription>
        </Alert>
      </div>
    </div>
    
    
    <!-- Distribution of Expense -->
    <div class="mt-6 border rounded-lg p-4">
      <h3 class="text-lg font-semibold mb-2">Distribution of Expense</h3>

      <form @submit.prevent="submitDistribution">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <Label>Account Name</Label>
                <select v-model="distribution.account_id" class="w-full border p-2 rounded">
                <option disabled value="">Select Account</option>
                <option 
                    v-for="account in props.accounts" 
                    :key="account.id" 
                    :value="account.id"
                >
                    {{ account.account_title }}
                </option>
                </select>
          </div>

          <div>
            <Label>Amount</Label>
            <Input v-model="distribution.amount" type="number" step="0.01" class="w-full" />
          </div>

          <div>
            <Label>Date</Label>
            <Input v-model="distribution.date" type="date" class="w-full" />
          </div>
        </div>

        <div class="flex justify-end mt-4">
          <Button type="submit">Save Distribution</Button>
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
    <div class="mt-6 border rounded-lg p-4">
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
        <Button v-if="user.role === 'audit'" :disabled="!approval.remarks" @click="submitApproval">
          Submit Review
        </Button>
      </div>
    </div>
  </div>
</template>
