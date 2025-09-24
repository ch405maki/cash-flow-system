<script setup lang="ts">
import { reactive, ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{
  pettyCash: any
}>()

const toast = useToast()

// Pre-fill form fields
const form = reactive({
  paid_to: props.pettyCash.paid_to,
  type: props.pettyCash.type,
  status: props.pettyCash.status,
  date: props.pettyCash.date,
  remarks: props.pettyCash.remarks,
  items: [] as any[]
})

const existingItems = ref(props.pettyCash.items ?? [])

// Input for new items
const newItem = reactive({
  particulars: '',
  date: '',
  amount: 0,
  receipt: null as File | null
})

const fileKey = ref(0)

// ✅ Combined running total (existing + new items)
const totalAmount = computed(() => {
  const existingTotal = existingItems.value.reduce((sum, i) => sum + (Number(i.amount) || 0), 0)
  const newTotal = form.items.reduce((sum, i) => sum + (Number(i.amount) || 0), 0)
  return existingTotal + newTotal
})

const addItem = () => {
  if (!newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please fill in all item fields before adding.')
    return
  }

  form.items.push({ ...newItem })
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
  const data = new FormData()
  data.append('paid_to', props.pettyCash.paid_to)
  data.append('type', props.pettyCash.type)
  data.append('status', props.pettyCash.status)
  data.append('date', props.pettyCash.date)
  data.append('remarks', form.remarks || '')

  form.items.forEach((item, i) => {
    data.append(`items[${i}][particulars]`, item.particulars)
    data.append(`items[${i}][date]`, item.date)
    data.append(`items[${i}][amount]`, item.amount.toString())
    if (item.receipt) {
      data.append(`items[${i}][receipt]`, item.receipt)
    }
  })

  await router.put(`/petty-cash/${props.pettyCash.id}`, data, {
    onSuccess: () => toast.success('Petty Cash Voucher updated successfully!'),
    onError: () => toast.error('Failed to update voucher.')
  })
}




</script>

<template>
  <div class="space-y-6">
    <!-- Voucher Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div class="space-y-2">
        <Label>PCV No: {{ props.pettyCash.pcv_no }}</Label><br />
        <Label>Paid To: {{ props.pettyCash.paid_to }}</Label><br />
        <Label>Type: {{ props.pettyCash.type }}</Label><br />
        <Label>Date: {{ props.pettyCash.date }}</Label>
      </div>
      <div class="md:col-span-2">
        <Label>Remarks</Label>
        <Input v-model="form.remarks" />
      </div>
    </div>

    <!-- Existing Items -->
    <div v-if="existingItems.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">Existing Items</h3>
      <table class="w-full border-collapse">
        <thead class="bg-muted">
          <tr>
            <th class="text-left p-2 border-b">Particulars</th>
            <th class="text-left p-2 border-b">Date</th>
            <th class="text-right p-2 border-b">Amount</th>
            <th class="text-left p-2 border-b">Receipt</th>
            <th class="p-2 border-b text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in existingItems" :key="item.id" class="border-b">
            <td class="p-2">{{ item.particulars }}</td>
            <td class="p-2">{{ item.date }}</td>
            <td class="p-2 text-right">{{ item.amount.toLocaleString() }}</td>
            <td class="p-2">
              <a v-if="item.receipt" :href="`/storage/${item.receipt}`" target="_blank" class="text-blue-600 underline">
                View
              </a>
              <span v-else class="text-muted-foreground italic">No file</span>
            </td>
            <td class="p-2 text-center">
              <Button variant="destructive" size="sm" @click="removeExistingItem(item.id)">Remove</Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Table for New Items -->
    <div v-if="form.items.length" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">New Items (Unsubmitted)</h3>
      <table class="w-full border-collapse">
        <thead class="bg-muted">
          <tr>
            <th class="text-left p-2 border-b">Particulars</th>
            <th class="text-left p-2 border-b">Date</th>
            <th class="text-right p-2 border-b">Amount</th>
            <th class="text-left p-2 border-b">Receipt</th>
            <th class="p-2 border-b text-center">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.items" :key="index" class="border-b">
            <td class="p-2">{{ item.particulars }}</td>
            <td class="p-2">{{ item.date }}</td>
            <td class="p-2 text-right">{{ item.amount.toLocaleString() }}</td>
            <td class="p-2">
              <span v-if="item.receipt">{{ item.receipt.name }}</span>
              <span v-else class="text-muted-foreground italic">No file</span>
            </td>
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

    <!-- Add New Item -->
    <div class="border rounded-xl p-4 space-y-3">
      <h3 class="text-lg font-semibold">Add New Item</h3>
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
    <div class="flex justify-end">
      <Button @click="submitForm">Update Voucher</Button>
    </div>
  </div>
</template>
