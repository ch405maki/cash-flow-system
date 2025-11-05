<script setup lang="ts">
import { reactive, computed, ref } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { PencilLine } from 'lucide-vue-next';
import { Textarea } from '@/components/ui/textarea'
import {
  RadioGroup,
  RadioGroupItem
} from '@/components/ui/radio-group'
import { Save } from 'lucide-vue-next';

const user = usePage().props.auth.user;
const toast = useToast()

const form = reactive({
  paid_to: '',
  status: 'draft',
  date: '',
  remarks: '',
  items: []
})

const newItem = reactive({
  particulars: '',
  type: '',
  date: '',
  amount: 0,
  receipt: null as File | null
})

const fileKey = ref(0)

const totalAmount = computed(() => {
  return form.items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
})

// Reset form function
const resetForm = () => {
  form.paid_to = ''
  form.status = 'draft'
  form.date = ''
  form.remarks = ''
  form.items = []
  
  resetNewItem()
}

// Reset new item function
const resetNewItem = () => {
  newItem.type = ''
  newItem.particulars = ''
  newItem.date = ''
  newItem.amount = 0
  newItem.receipt = null
  fileKey.value++
}

const addItem = () => {
  if (!newItem.type || !newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please fill in all item fields before adding.')
    return
  }

  form.items.push({ ...newItem })
  resetNewItem()
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
}

const submitForm = async () => {
  try {
    const data = new FormData()
    data.append('paid_to', form.paid_to)
    data.append('status', form.status)
    data.append('date', form.date)
    data.append('remarks', form.remarks)

    form.items.forEach((item, i) => {
      data.append(`items[${i}][type]`, item.type)
      data.append(`items[${i}][particulars]`, item.particulars)
      data.append(`items[${i}][date]`, item.date)
      data.append(`items[${i}][amount]`, item.amount.toString())
      if (item.receipt) {
        data.append(`items[${i}][receipt]`, item.receipt)
      }
    })

    await router.post('/petty-cash', data, {
      onSuccess: () => {
        toast.success('Petty Cash Voucher created successfully!')
        resetForm() // Reset the form after successful submission
      },
      onError: (errors) => {
        if (errors.threshold) {
          toast.error(`${errors.threshold}`, {
            timeout: 5000,
          })
        } else {
          toast.error('Please check your form fields.', {
            timeout: 4000,
          })
        }
      },
    })

  } catch (error) {
    toast.error('Something went wrong.')
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Main Fields -->
    <div>
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
      <div class="md:col-span-2">
        <Label>Paid To</Label>
        <Input v-model="form.paid_to" placeholder="Enter recipient name" />
      </div>
      <div>
        <Label>Date</Label>
        <Input v-model="form.date" type="date" />
      </div>
    </div>
    <div>
      <Label>Remarks</Label>
      <Textarea v-model="form.remarks" placeholder="Optional remarks" />
    </div>
    </div>

    <!-- Add Item Section -->
    <div class="border rounded-xl p-4 space-y-3">
      <h3 class="text-lg font-semibold">Add Item</h3>
      <div class="space-y-2">
        <!-- Type as Radio Group -->
        <div>
        <Label>Type</Label>
        <RadioGroup v-model="newItem.type">
          <div class="flex mt-2 items-center space-x-4">
            <div class="flex items-center space-x-2">
              <RadioGroupItem id="reimbursement" value="Reimbursement" />
              <Label for="reimbursement">Reimbursement</Label>
            </div>
            <div class="flex items-center space-x-4" v-if="user.is_cash_advance === 1">
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="cash-advance" value="Cash Advance" />
                <Label for="cash-advance">Cash Advance</Label>
              </div>
            </div>
          </div>
        </RadioGroup>
      </div>

        <div class="flex flex-col md:flex-row md:items-end md:space-x-2">
          <div
            class="grid w-full gap-4 md:grid-cols-[2fr_1fr_1fr_2fr]"
          >
            <!-- Particulars - grows 2x -->
            <div>
              <Label>Particulars</Label>
              <Input v-model="newItem.particulars" placeholder="e.g., Office Supplies" />
            </div>

            <!-- Date -->
            <div>
              <Label>Date</Label>
              <Input v-model="newItem.date" type="date" />
            </div>

            <!-- Amount -->
            <div>
              <Label>Amount</Label>
              <Input v-model.number="newItem.amount" type="number" min="0" />
            </div>

            <!-- Receipt -->
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

          <!-- Button (takes minimal space, sits on right in desktop) -->
          <div class="flex justify-end mb-[4px]">
            <Button variant="secondary" @click="addItem">
              <PencilLine class="mr-2 h-4" /> Accept
            </Button>
          </div>
        </div>
      </div>

      <!-- Item List Table -->
      <div v-if="form.items.length > 0" class="">
        <h3 class="text-lg font-semibold mb-3">Item List</h3>
        <table class="w-full border-collapse">
          <thead class="bg-muted">
            <tr>
              <th class="text-left p-2 border-b">Type</th>
              <th class="text-left p-2 border-b">Particulars</th>
              <th class="text-left p-2 border-b">Date</th>
              <th class="text-right p-2 border-b">Amount</th>
              <th class="text-left p-2 border-b">Receipt</th>
              <th class="p-2 border-b text-right">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.items" :key="index" class="border-b">
              <td class="p-2">{{ item.type }}</td>
              <td class="p-2">{{ item.particulars }}</td>
              <td class="p-2">{{ item.date }}</td>
              <td class="p-2 text-right">{{ item.amount.toLocaleString() }}</td>
              <td class="p-2">
                <span v-if="item.receipt">{{ item.receipt.name }}</span>
                <span v-else class="text-muted-foreground italic">No file</span>
              </td>
              <td class="p-2 text-right">
                <Button variant="destructive" size="sm" @click="removeItem(index)">Remove</Button>
              </td>
            </tr>
          </tbody>
        </table>

        <div class="flex justify-end mt-3 font-semibold">
          Total: ₱ {{ totalAmount.toLocaleString() }}
        </div>
      </div>
    </div>

    <!-- Submit -->
    <div class="flex justify-end space-x-2">
      <Button variant="outline" @click="resetForm">Reset Form</Button>
      <Button @click="submitForm"><Save class="mr-2 h-4 w-4" />Save Petty Cash</Button>
    </div>
  </div>
</template>