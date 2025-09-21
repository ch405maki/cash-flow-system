<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, reactive } from 'vue'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card'
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Petty Cash Voucher',
        href: '#',
    },
];

const toast = useToast()

const form = reactive({
  pcv_no: '',
  paid_to: '',
  type: '',
  status: 'draft',
  date: '',
  remarks: '',
  items: [
    { particulars: '', date: '', amount: 0, receipt: null }
  ]
})

const addItem = () => {
  form.items.push({ particulars: '', date: '', amount: 0, receipt: null })
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
      onError: (errors) => toast.error('Please check your form fields.')
    })
  } catch (error) {
    toast.error('Something went wrong.')
  }
}

</script>

<template>
    <Head title="Petty Cash Voucher" />

    <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <h1 class="text-xl font-bold">Create Petty Cash Voucher</h1>
        <div class="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min p-4">
        <!-- Main Fields -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <Label>PCV No</Label>
            <Input v-model="form.pcv_no" placeholder="Enter PCV No" />
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

        <!-- Dynamic Items -->
        <div class="space-y-2">
          <h3 class="text-lg font-semibold">Items</h3>
          <div v-for="(item, index) in form.items" :key="index" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end border p-3 rounded-lg">
            <div>
              <Label>Particulars</Label>
              <Input v-model="item.particulars" placeholder="e.g., Office Supplies" />
            </div>
            <div>
              <Label>Date</Label>
              <Input v-model="item.date" type="date" />
            </div>
            <div>
              <Label>Amount</Label>
              <Input v-model.number="item.amount" type="number" min="0" />
            </div>
            <div>
              <Label>Receipt</Label>
              <Input type="file" @change="e => item.receipt = e.target.files[0]" />
            </div>
            <div class="md:col-span-4 flex justify-end">
              <Button v-if="form.items.length > 1" variant="destructive" size="sm" @click="removeItem(index)">Remove</Button>
            </div>
          </div>
          <Button variant="secondary" @click="addItem">+ Add Item</Button>
        </div>

        <div class="flex justify-end">
          <Button @click="submitForm">Save Voucher</Button>
        </div>
        </div>
    </div>
  </AppLayout>
</template>
