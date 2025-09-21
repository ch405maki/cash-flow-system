<script setup lang="ts">
import { reactive, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

const props = defineProps<{
  nextPcvNo: string
}>()

const toast = useToast()

const form = reactive({
  pcv_no: props.nextPcvNo,
  paid_to: '',
  type: '',
  status: 'draft',
  date: '',
  remarks: '',
  items: []
})

// Temporary input holder for adding new items
const newItem = reactive({
  particulars: '',
  date: '',
  amount: 0,
  receipt: null as File | null
})

// 🟢 Computed total
const totalAmount = computed(() => {
  return form.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
})

const addItem = () => {
  if (!newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please fill in all item fields before adding.')
    return
  }

  form.items.push({ ...newItem }) // push copy
  // reset newItem fields
  newItem.particulars = ''
  newItem.date = ''
  newItem.amount = 0
  newItem.receipt = null
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = async () => {
  try {
    const data = new FormData()
    data.append('pcv_no', form.pcv_no)
    data.append('paid_to', form.paid_to)
    data.append('type', form.type)
    data.append('status', form.status)
    data.append('date', form.date)
    data.append('remarks', form.remarks)

    form.items.forEach((item, i) => {
      data.append(`items[${i}][particulars]`, item.particulars)
      data.append(`items[${i}][date]`, item.date)
      data.append(`items[${i}][amount]`, item.amount.toString())
      if (item.receipt) {
        data.append(`items[${i}][receipt]`, item.receipt)
      }
    })

    await router.post('/petty-cash', data, {
      onSuccess: () => toast.success('Petty Cash Voucher created successfully!'),
      onError: () => toast.error('Please check your form fields.')
    })
  } catch (error) {
    toast.error('Something went wrong.')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Main Fields -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <Label>PCV No</Label>
        <Input v-model="form.pcv_no" disabled />
      </div>
      <div>
        <Label>Paid To</Label>
        <Input v-model="form.paid_to" placeholder="Enter recipient name" />
      </div>
      <div>
        <Label>Type</Label>
        <Input v-model="form.type" placeholder="Reimbursement, etc." />
      </div>
      <div>
        <Label>Date</Label>
        <Input v-model="form.date" type="date" />
      </div>
      <div class="md:col-span-2">
        <Label>Remarks</Label>
        <Input v-model="form.remarks" placeholder="Optional remarks" />
      </div>
    </div>

    <!-- Fixed Input for New Item -->
    <div class="border rounded-xl p-4 space-y-3">
      <h3 class="text-lg font-semibold">Add Item</h3>
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <Label>Particulars</Label>
          <Input v-model="newItem.particulars" placeholder="e.g., Office Supplies" />
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
          <Input type="file" @change="e => newItem.receipt = e.target.files[0]" />
        </div>
      </div>
      <div class="flex justify-end">
        <Button variant="secondary" @click="addItem">Accept Item</Button>
      </div>
    </div>

    <!-- Table of Added Items -->
    <div v-if="form.items.length > 0" class="border rounded-xl p-4">
      <h3 class="text-lg font-semibold mb-3">Item List</h3>
      <table class="w-full border-collapse">
        <thead class="bg-muted">
          <tr>
            <th class="text-left p-2 border-b">Particulars</th>
            <th class="text-left p-2 border-b">Date</th>
            <th class="text-right p-2 border-b">Amount</th>
            <th class="p-2 border-b">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="(item, index) in form.items" :key="index" class="border-b">
            <td class="p-2">{{ item.particulars }}</td>
            <td class="p-2">{{ item.date }}</td>
            <td class="p-2 text-right">{{ item.amount.toLocaleString() }}</td>
            <td class="p-2 text-center">
              <Button variant="destructive" size="sm" @click="removeItem(index)">Remove</Button>
            </td>
          </tr>
        </tbody>
      </table>

      <div class="flex justify-end mt-3 font-semibold">
        Total: {{ totalAmount.toLocaleString() }}
      </div>
    </div>

    <!-- Submit -->
    <div class="flex justify-end">
      <Button @click="submitForm">Save Voucher</Button>
    </div>
  </div>
</template>
