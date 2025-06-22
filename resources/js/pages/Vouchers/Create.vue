<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { reactive, computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { useToast } from 'vue-toastification';
import { Plus, Trash2, Check, ChevronsUpDown, Search } from 'lucide-vue-next';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import axios from 'axios'

interface VoucherItem {
  amount: number;
  charging_tag: string | null;
  hours: number | null;
  rate: number | null;
  account_id: string;
}

interface Props {
  accounts?: Array<{ id: number; account_title: string }>;
  auth: {
    user: {
      id: number;
    };
  };
  voucher_number: String
}

const props = defineProps<Props>();
const toast = useToast();
const accountSearchQuery = ref('');

const filteredAccounts = computed(() => {
  if (!accountSearchQuery.value) return props.accounts || [];
  return (props.accounts || []).filter(account =>
    account.account_title.toLowerCase().includes(accountSearchQuery.value.toLowerCase())
  );
});

const form = reactive({
    voucher_no: props.voucher_number,
    issue_date: '',
    payment_date: '',
    check_date: '',
    delivery_date: '',
    voucher_date: '',
    purpose: '',
    payee: '',
    check_no: null,
    check_payable_to: '',
    check_amount: 0,
    status: 'draft',
    type: '',
    user_id: props.auth.user.id,
    items: [] as VoucherItem[],
});

const newItem = ref<VoucherItem>({
  amount: 0,
  charging_tag: null,
  hours: null,
  rate: null,
  account_id: '',
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Vouchers', href: '/vouchers' },
  { title: 'Create Voucher', href: '/vouchers/create' },
];

const isCashVoucher = computed(() => form.type === 'cash');

const addItem = () => {
  if (!newItem.value.account_id) {
    toast.error('Account selection is required');
    return;
  }

  if (newItem.value.amount <= 0) {
    toast.error('Amount must be greater than 0');
    return;
  }

  form.items.push({ ...newItem.value });
  calculateTotalAmount();
  resetNewItem();
};

const removeItem = (index: number) => {
  form.items.splice(index, 1);
  calculateTotalAmount();
};

const resetNewItem = () => {
  newItem.value = {
    amount: 0,
    charging_tag: null,
    hours: null,
    rate: null,
    account_id: '',
  };
};

const calculateTotalAmount = () => {
  form.check_amount = form.items.reduce((sum, item) => sum + item.amount, 0);
};

const calculateItemAmount = () => {
  if (isCashVoucher.value) {
    // For cash vouchers, amount is entered directly
    return;
  }
  // For salary vouchers, calculate from hours and rate
  if (newItem.value.hours && newItem.value.rate) {
    newItem.value.amount = newItem.value.hours * newItem.value.rate;
  }
};

async function submitVoucher() {
  try {
    if (form.items.length === 0 && !isCashVoucher.value) {
      toast.error('Please add at least one item for non-cash vouchers');
      return;
    }

    const payload = {
      ...form,
      check: form.items,
    };

    const response = await axios.post('/api/vouchers', payload);
    toast.success(`Voucher created successfully! Number: ${response.data.voucher_no}`);
    router.visit('/vouchers');
  } catch (error) {
    if (axios.isAxiosError(error) && error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach((msg) => {
        toast.error(msg[0]);
      });
    } else {
      toast.error('Failed to create Voucher');
    }
  }
}
</script>

<template>
  <Head title="Create Voucher" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6 mx-auto w-full">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold">Create Voucher</h1>
          <p class="text-sm text-muted-foreground">Complete all required fields</p>
        </div>
        <div>
          <h1 class="text-xl font-bold" title="voucher number"># {{ voucher_number }}</h1>
        </div>
      </div>

      <form @submit.prevent="submitVoucher" class="space-y-6">
        <!-- Voucher Header Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Column 1 -->
          <div class="space-y-4">
            <div class="grid gap-2">
              <Label for="voucher_type">Voucher Type *</Label>
              <Select 
                v-model="form.type" 
                required
                @update:modelValue="form.items = isCashVoucher ? [] : form.items"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select Voucher Type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="salary">Salary</SelectItem>
                  <SelectItem value="cash">Cash</SelectItem>
                </SelectContent>
              </Select>
            </div>
            <div class="grid gap-2">
              <Label for="payee">Payee *</Label>
              <Input id="payee" v-model="form.payee" required />
            </div>
            <div class="grid gap-2">
              <Label for="check_payable_to">Check Pay To *</Label>
              <Input id="check_payable_to" v-model="form.check_payable_to" required />
            </div>
          </div>

          <!-- Column 2 -->
          <div class="space-y-4">
            <div class="grid gap-2">
              <Label for="check_amount">Check Amount</Label>
              <Input 
                id="check_amount" 
                type="number" 
                step="0.01" 
                v-model="form.check_amount" 
                :disabled="true"
              />
            </div>
            <div class="grid gap-2">
              <Label for="voucher_date">Voucher Date *</Label>
              <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
            </div>
            <div class="grid gap-2">
              <Label for="check_date">Check Date</Label>
              <Input id="check_date" type="date" v-model="form.check_date" required />
            </div>
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="purpose">Purpose *</Label>
          <Textarea id="purpose" v-model="form.purpose" required />
        </div>

        <!-- Items Section -->
        <div class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium capitalize"> {{ form.type }} Account Details</h3>
            </div>

            <!-- New Item Form -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 pb-4 border-b">
                <!-- Account Selection (3 columns) -->
                <div class="grid gap-2 md:col-span-3">
                <Label>Account *</Label>
                <Combobox v-model="newItem.account_id" by="id">
                    <ComboboxAnchor as-child>
                    <ComboboxTrigger as-child>
                        <Button variant="outline" class="w-full justify-between h-10">
                        <span class="truncate">
                            {{ props.accounts?.find(a => a.id === parseInt(newItem.account_id))?.account_title || 'Select account' }}
                        </span>
                        <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                        </Button>
                    </ComboboxTrigger>
                    </ComboboxAnchor>
                    
                    <ComboboxList class="max-h-[300px] overflow-y-auto shadow-lg rounded-md border">
                    <div class="relative w-full items-center sticky top-0 bg-white z-10">
                        <ComboboxInput
                        class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10 text-base"
                        placeholder="Search accounts..." 
                        v-model="accountSearchQuery"
                        />
                        <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
                        <Search class="size-4 text-muted-foreground" />
                        </span>
                    </div>

                    <ComboboxEmpty v-if="filteredAccounts.length === 0" class="py-4 text-center text-sm text-gray-500">
                        No accounts found.
                    </ComboboxEmpty>

                    <ComboboxGroup>
                        <div class="max-h-[250px] overflow-y-auto">
                        <ComboboxItem 
                            v-for="account in filteredAccounts" 
                            :key="account.id" 
                            :value="account.id.toString()"
                            class="h-2flex items-center px-4 text-xs text-sm hover:bg-gray-50 cursor-pointer"
                        >
                            {{ account.account_title }}
                            <ComboboxItemIndicator class="ml-auto">
                            <Check class="h-4 w-4 text-primary" />
                            </ComboboxItemIndicator>
                        </ComboboxItem>
                        </div>
                    </ComboboxGroup>
                    </ComboboxList>
                </Combobox>
                </div>

                <!-- Charging Tag (2 columns) -->
                <div class="grid gap-2 md:col-span-2">
                    <Label>Charging Tag</Label>
                    <Select v-model="newItem.charging_tag">
                    <SelectTrigger>
                        <SelectValue placeholder="Select tag" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="C">C</SelectItem>
                        <SelectItem value="D">D</SelectItem>
                    </SelectContent>
                    </Select>
                </div>

                <!-- Hours (2 columns, hidden for cash) -->
                <div class="grid gap-2 md:col-span-2" v-if="!isCashVoucher">
                    <Label>Hours</Label>
                    <Input 
                    type="number" 
                    step="0.01" 
                    v-model.number="newItem.hours"
                    @change="calculateItemAmount"
                    />
                </div>

                <!-- Rate (2 columns, hidden for cash) -->
                <div class="grid gap-2 md:col-span-2" v-if="!isCashVoucher">
                    <Label>Rate</Label>
                    <Input 
                    type="number" 
                    step="0.01" 
                    v-model.number="newItem.rate"
                    @change="calculateItemAmount"
                    />
                </div>

                <!-- Amount (3 columns) -->
                <div class="grid gap-2 md:col-span-3">
                    <Label>Amount *</Label>
                    <div class="flex gap-2">
                    <Input 
                        type="number" 
                        step="0.01" 
                        v-model.number="newItem.amount"
                        required
                        class="flex-1"
                    />
                    <Button 
                        type="button" 
                        variant="default" 
                        size="sm" 
                        @click="addItem"
                        class="h-10"
                    >
                        <Plus class="h-4 w-4" /> Accept
                    </Button>
                    </div>
                </div>
            </div>

          <!-- Items Table -->
          <div class="border rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Account</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tag</th>
                  <th v-if="!isCashVoucher" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                  <th v-if="!isCashVoucher" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rate</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in form.items" :key="index">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ props.accounts?.find(a => a.id === parseInt(item.account_id))?.account_title || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.charging_tag || 'N/A' }}
                  </td>
                  <td v-if="!isCashVoucher" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.hours?.toFixed(2) || 'N/A' }}
                  </td>
                  <td v-if="!isCashVoucher" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.rate?.toFixed(2) || 'N/A' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ item.amount.toFixed(2) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                    <Button
                      variant="destructive"
                      size="sm"
                      @click="removeItem(index)"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </td>
                </tr>
                <tr v-if="form.items.length === 0">
                  <td :colspan="isCashVoucher ? 4 : 6" class="px-6 py-4 text-center text-sm text-gray-500">
                    No items added yet
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Total Amount -->
          <div class="flex justify-start mt-4">
            <div class="text-lg font-semibold">
              Total Amount: ₱{{ form.check_amount.toFixed(2) }}
            </div>
          </div>
        </div>

        <!-- Dates Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="grid gap-2">
            <Label for="issue_date">Issue Date *</Label>
            <Input id="issue_date" type="date" v-model="form.issue_date" />
          </div>

          <div class="grid gap-2">
            <Label for="payment_date">Payment Date *</Label>
            <Input id="payment_date" type="date" v-model="form.payment_date" />
          </div>

          <div class="grid gap-2">
            <Label for="delivery_date">Delivery Date *</Label>
            <Input id="delivery_date" type="date" v-model="form.delivery_date" />
          </div>
        </div>

        <!-- Form Actions -->
        <CardFooter class="flex justify-end gap-4 px-0 pb-0">
          <Button 
            type="button" 
            variant="outline" 
            @click="router.visit('/vouchers')"
          >
            Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="!isCashVoucher && form.items.length === 0"
          >
            Create Voucher
          </Button>
        </CardFooter>
      </form>
    </div>
  </AppLayout>
</template>