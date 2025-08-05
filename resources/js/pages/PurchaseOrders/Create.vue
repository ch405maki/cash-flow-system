<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useToast } from 'vue-toastification';
import axios from 'axios';
import { ref } from 'vue';
import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
import {
  Table,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table';


interface PurchaseOrderDetail {
  quantity: number;
  unit: string;
  item_description: string;
  unit_price: number;
  amount: number;
  editing?: boolean;
  original?: PurchaseOrderDetail;
}

interface Props {
  user_id: number;
  departments?: Array<{ id: number; department_name: string }>;
  accounts?: Array<{ id: number; account_title: string }>;
  canvas_id?: string;
}

const props = defineProps<Props>();
const toast = useToast();

type TaggingType = 'with_canvas' | 'no_canvas';

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard', },
  { title: 'Create Purchase Order', href: '/',},
];

const form = ref({
  payee: '',
  check_payable_to: '',
  date: new Date().toISOString().split('T')[0], 
  purpose: '',
  status: 'draft',
  user_id: props.user_id,
  department_id: '',
  account_id: '',
  details: [] as PurchaseOrderDetail[],
  canvas_id: props.canvas_id || null,
  tagging: props.canvas_id ? 'with_canvas' : 'no_canvas' as TaggingType, // Add this line
});

const editItem = (index: number) => {
  // Exit any other editing modes first
  form.value.details.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i);
    }
  });
  
  // Set editing mode for this item
  form.value.details[index].editing = true;
  form.value.details[index].original = { ...form.value.details[index] };
};

const saveEdit = (index: number) => {
  // Recalculate amount before saving
  form.value.details[index].amount = 
    form.value.details[index].quantity * form.value.details[index].unit_price;
  
  form.value.details[index].editing = false;
  delete form.value.details[index].original;
};

const cancelEdit = (index: number) => {
  if (form.value.details[index].original) {
    form.value.details[index] = { ...form.value.details[index].original };
  }
  form.value.details[index].editing = false;
  delete form.value.details[index].original;
};

const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Enter') {
    saveEdit(index);
  } else if (event.key === 'Escape') {
    cancelEdit(index);
  }
};

const newItem = ref<PurchaseOrderDetail>({
  quantity: 1,
  unit: 'pc',
  item_description: '',
  unit_price: 0,
  amount: 0,
});

const addItem = () => {
  if (!newItem.value.item_description) {
    toast.error('Item description is required');
    return;
  }

  // Calculate amount
  newItem.value.amount = newItem.value.quantity * newItem.value.unit_price;

  form.value.details.push({ ...newItem.value });
  resetNewItem();
};

const removeItem = (index: number) => {
  form.value.details.splice(index, 1);
};

const resetNewItem = () => {
  newItem.value = {
    quantity: 1,
    unit: 'pc',
    item_description: '',
    unit_price: 0,
    amount: 0,
  };
};

const calculateTotal = () => {
  return form.value.details.reduce((sum, item) => sum + item.amount, 0);
};

const submitForm = async () => {
  try {
    // Validate tagging and canvas_id
    if (form.value.tagging === 'with_canvas' && !form.value.canvas_id) {
      toast.error('Canvas ID is required when tagging is "With Canvas"');
      return;
    }

    // Calculate total amount
    form.value.amount = calculateTotal();

    const response = await axios.post('/api/purchase-orders', form.value);

    toast.success('Purchase Order created successfully!');
    window.location.href = `/purchase-orders/${response.data.id}`;
  } catch (error) {
    if (axios.isAxiosError(error) && error.response) {
      toast.error(error.response.data.message || 'Failed to create Purchase Order');
    } else {
      toast.error('An unexpected error occurred');
    }
    console.error(error);
  }
};
</script>

<template>
  <Head title="Create Purchase Order" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <form @submit.prevent="submitForm" class="space-y-2">
        <h1 class="text-2xl font-bold">Create Purchase Order</h1>
        <!-- Basic Information Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex items-center">
          <!-- Payee Field -->
          <div class="space-y-2 md:col-span-2">
            <Label for="payee">Company Name</Label>
            <Input id="payee" v-model="form.payee" required />
          </div>
          <!-- add 2 check box with canvas, no canvas -->
          <div class="space-y-2">
            <Label for="tagging">Tagging</Label>
            <RadioGroup v-model="form.tagging" class="flex">
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="with_canvas" value="with_canvas" :disabled="true" />
                <Label for="with_canvas">With Canvas</Label>
              </div>
              <div class="flex items-center space-x-2">
                <RadioGroupItem id="no_canvas" value="no_canvas" :disabled="true" />
                <Label for="no_canvas">No Canvas</Label>
              </div>
            </RadioGroup>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-2 md:col-span-2">
            <Label for="check_payable_to">Check Payable To</Label>
            <Input id="check_payable_to" v-model="form.check_payable_to" required />
          </div>

          <!-- Date Field -->
          <div class="space-y-2 md:col-span-1">
            <Label for="date">P. O. Date</Label>
            <Input id="date" type="date" v-model="form.date" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <Label for="department_id">Department</Label>
            <Select v-model="form.department_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Select department" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()">
                  {{ dept.department_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label for="account_id">Account</Label>
            <Select v-model="form.account_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Select account" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="account in accounts" :key="account.id" :value="account.id.toString()">
                  {{ account.account_title }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
          <div class="space-y-2">
            <Label for="purpose">Purpose</Label>
            <Textarea id="purpose" v-model="form.purpose" required />
          </div>
        </div>

        <!-- Items Section -->
        <div class="space-y-2">
          <h2 class="text-xl font-semibold mt-4">Items</h2>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
              <div class="space-y-2 md:col-span-6">
                <Label for="item_description">Description</Label>
                <Input id="item_description" v-model="newItem.item_description" />
              </div>

              <div class="space-y-2 md:col-span-1">
                <Label for="quantity">Quantity</Label>
                <Input id="quantity" type="number" v-model.number="newItem.quantity" min="1" />
              </div>

              <div class="md:col-span-2 space-y-2">
              <Label for="quantity">Unit</Label>
              <Select v-model="newItem.unit">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select a unit" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem value="pc">pc/s</SelectItem>
                    <SelectItem value="box">box/es</SelectItem>
                    <SelectItem value="kg">kg/s</SelectItem>
                    <SelectItem value="pack">pack/s</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

              <div class="space-y-2 md:col-span-2">
                <Label for="unit_price">Unit Price</Label>
                <Input id="unit_price" type="number" step="0.01" v-model.number="newItem.unit_price" min="0" />
              </div>

              <Button type="button" @click="addItem" class="w-full md:col-span-1 px">
                Add Item
              </Button>
            </div>

            <!-- Items Table -->
            <div class="border rounded-lg overflow-hidden">
              <Table class="w-full">
                <TableHeader>
                  <TableRow>
                    <TableHead>Description</TableHead>
                    <TableHead>Quantity</TableHead>
                    <TableHead>Unit</TableHead>
                    <TableHead>Unit Price</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead
                      v-if="form.details.some(item => item.editing)"
                      class="text-right"
                    >
                      Actions
                    </TableHead>
                  </TableRow>
                </TableHeader>

                <TableBody>
                  <TableRow
                    v-for="(item, index) in form.details"
                    :key="index"
                    @click="editItem(index)"
                    :class="{
                      'cursor-pointer hover:bg-muted': true,
                      'bg-blue-50': item.editing
                    }"
                  >
                    <!-- Description -->
                    <TableCell>
                      <div v-if="!item.editing">{{ item.item_description }}</div>
                      <Input
                        v-else
                        v-model="item.item_description"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        class="w-full"
                      />
                    </TableCell>

                    <!-- Quantity -->
                    <TableCell>
                      <div v-if="!item.editing">{{ item.quantity }}</div>
                      <Input
                        v-else
                        type="number"
                        v-model.number="item.quantity"
                        min="1"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        @change="item.amount = item.quantity * item.unit_price"
                        class="w-full"
                      />
                    </TableCell>

                    <!-- Unit -->
                    <TableCell>
                      <div v-if="!item.editing">{{ item.unit }}</div>
                      <Select
                        v-else
                        v-model="item.unit"
                        @click.stop
                      >
                        <SelectTrigger class="w-full">
                          <SelectValue placeholder="Select unit" />
                        </SelectTrigger>
                        <SelectContent>
                          <SelectGroup>
                            <SelectItem value="pc">pc/s</SelectItem>
                            <SelectItem value="box">box/es</SelectItem>
                            <SelectItem value="kg">kg/s</SelectItem>
                            <SelectItem value="pack">pack/s</SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                    </TableCell>

                    <!-- Unit Price -->
                    <TableCell>
                      <div v-if="!item.editing">{{ item.unit_price.toFixed(2) }}</div>
                      <Input
                        v-else
                        type="number"
                        step="0.01"
                        v-model.number="item.unit_price"
                        min="0"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        @change="item.amount = item.quantity * item.unit_price"
                        class="w-full"
                      />
                    </TableCell>

                    <!-- Amount -->
                    <TableCell>
                      {{ item.amount.toFixed(2) }}
                    </TableCell>

                    <!-- Actions -->
                    <TableCell class="text-right">
                      <div class="flex justify-end space-x-2" v-if="item.editing">
                        <Button variant="outline" size="sm" @click.stop="saveEdit(index)">
                          Save
                        </Button>
                        <Button variant="outline" size="sm" @click.stop="cancelEdit(index)">
                          Cancel
                        </Button>
                        <Button variant="destructive" size="sm" @click.stop="removeItem(index)">
                          Remove
                        </Button>
                      </div>
                    </TableCell>
                  </TableRow>

                  <TableRow v-if="form.details.length === 0">
                    <TableCell colspan="6" class="text-center text-sm text-muted-foreground">
                      No items added yet
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>

            </div>

            <!-- Total Amount -->
            <div class="flex justify-end">
              <div class="text-lg font-semibold">
                Total Amount: {{ calculateTotal().toFixed(2) }}
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end space-x-4">
          <Button type="button" variant="outline">
            Cancel
          </Button>
          <Button type="submit">
            Create Purchase Order
          </Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>