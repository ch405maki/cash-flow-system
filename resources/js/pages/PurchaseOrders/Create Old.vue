<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from 'vue-toastification';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Create Purchase Order',
        href: '/',
    },
];

const toast = useToast();
const props = defineProps({
  request: Object,
  accounts: Array,
});

// Auto-generate PO number
function generatePONumber(): string {
  const prefix = 'PO-' + new Date().toISOString().slice(0, 10).replace(/-/g, '') + '-';
  return prefix + Math.floor(1000 + Math.random() * 9000);
}

// Step 1: Create initial items from request
const initialItems = props.request.details.map(detail => ({
  quantity: detail.quantity,
  unit: detail.unit,
  item_description: detail.item_description,
  unit_price: 0,
  amount: 0
}));

// Step 2: Create form (without relying on form itself)
const form = useForm({
  po_no: generatePONumber(),
  payee: '',
  check_payable_to: '',
  date: new Date().toISOString().split('T')[0],
  amount: 0, // set later
  purpose: props.request.purpose,
  status: 'draft',
  remarks: '',
  user_id: props.request.user_id,
  department_id: props.request.department_id,
  account_id: '',
  items: initialItems,
});

// Step 3: Now calculate the amount
form.amount = form.items.reduce((sum, item) => sum + (item.amount || 0), 0);

// Update item amount when unit price or quantity changes
function updateItemAmount(index: number) {
  form.items[index].amount = form.items[index].quantity * form.items[index].unit_price;
  form.amount = form.items.reduce((sum, item) => sum + item.amount, 0);
}

// Submit the PO form
async function submitPO() {
  try {
    const response = await axios.post('/api/purchase-orders', form);

    toast.success(response.data.message || 'Purchase Order created successfully');
    // optionally reset the form or redirect
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach((msg) => {
        toast.error(msg[0]);
      });
    } else {
      toast.error('Failed to create Purchase Order');
    }
  }
}
</script>

<template>
  <Head title="Create Purchase Order" />

<AppLayout :breadcrumbs="breadcrumbs">
  <div class="p-6 space-y-6">
    <h1 class="text-2xl font-bold">Create Purchase Order</h1>

    <!-- Request Summary -->
    <div class="bg-gray-50 dark:bg-gray-800 p-4 rounded-lg">
      <h2 class="text-lg font-semibold mb-2">Request Information</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p><strong>Request No:</strong> {{ request.request_no }}</p>
          <p><strong>Purpose:</strong> {{ request.purpose }}</p>
        </div>
        <div>
          <p><strong>Department:</strong> {{ request.department.department_name }}</p>
          <p><strong>Requested By:</strong> {{ request.user.first_name }} {{ request.user.last_name }}</p>
        </div>
      </div>
    </div>

    <!-- PO Form -->
    <form @submit.prevent="submitPO" class="space-y-6">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- PO Number -->
        <div>
          <Label for="po_no">PO Number</Label>
          <Input id="po_no" v-model="form.po_no" readonly />
        </div>

        <!-- Date -->
        <div>
          <Label for="date">Date</Label>
          <Input id="date" v-model="form.date" type="date" required />
        </div>

        <!-- Payee -->
        <div>
          <Label for="payee">Payee</Label>
          <Input id="payee" v-model="form.payee" required />
        </div>

        <!-- Check Payable To -->
        <div>
          <Label for="check_payable_to">Check Payable To</Label>
          <Input id="check_payable_to" v-model="form.check_payable_to" required />
        </div>

        <!-- Account -->
        <div>
          <Label for="account_id">Account</Label>
          <Select v-model="form.account_id" required>
            <SelectTrigger>
              <SelectValue placeholder="Select account" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem
                v-for="account in accounts"
                :key="account.id"
                :value="account.id"
              >
                {{ account.account_title }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Status -->
        <div>
          <Label for="status">Status</Label>
          <Select v-model="form.status" required>
            <SelectTrigger>
              <SelectValue placeholder="Select status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="approved">Approved</SelectItem>
              <SelectItem value="rejected">Rejected</SelectItem>
            </SelectContent>
          </Select>
        </div>

        <!-- Amount -->
        <div>
          <Label for="amount">Total Amount</Label>
          <Input id="amount" v-model="form.amount" type="number" min="0" step="0.01" readonly />
        </div>
      </div>

      <!-- Purpose -->
      <div>
        <Label for="purpose">Purpose</Label>
        <Textarea id="purpose" v-model="form.purpose" required />
      </div>

      <!-- Remarks -->
      <div>
        <Label for="remarks">Remarks</Label>
        <Textarea id="remarks" v-model="form.remarks" />
      </div>

      <!-- Items List -->
      <div class="space-y-4">
        <h2 class="text-lg font-semibold">Items</h2>
        <div v-for="(item, index) in form.items" :key="index" class="border p-4 rounded-lg">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Quantity -->
            <div>
              <Label :for="`quantity-${index}`">Quantity</Label>
              <Input
                :id="`quantity-${index}`"
                v-model.number="item.quantity"
                type="number"
                min="1"
                @change="updateItemAmount(index)"
                required
              />
            </div>

            <!-- Unit -->
            <div>
              <Label :for="`unit-${index}`">Unit</Label>
              <Input
                :id="`unit-${index}`"
                v-model="item.unit"
                readonly
              />
            </div>

            <!-- Description -->
            <div>
              <Label :for="`description-${index}`">Description</Label>
              <Input
                :id="`description-${index}`"
                v-model="item.item_description"
              />
            </div>

            <!-- Unit Price -->
            <div>
              <Label :for="`unit-price-${index}`">Unit Price</Label>
              <Input
                :id="`unit-price-${index}`"
                v-model.number="item.unit_price"
                type="number"
                min="0"
                step="0.01"
                @change="updateItemAmount(index)"
                required
              />
            </div>

            <!-- Amount -->
            <div>
              <Label :for="`amount-${index}`">Amount</Label>
              <Input
                :id="`amount-${index}`"
                v-model.number="item.amount"
                type="number"
                min="0"
                step="0.01"
                readonly
              />
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <Button type="submit" :disabled="form.processing">
          Create Purchase Order
        </Button>
      </div>
    </form>
  </div>
</AppLayout>
</template>