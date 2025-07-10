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
import { formatCurrency } from '@/lib/utils';

interface VoucherItem {
  amount: number;
  charging_tag: string | null;
  hours: number | null;
  rate: number | null;
  account_id: string;
  editing?: boolean;
  original?: VoucherItem;
}

interface Props {
  accounts?: Array<{ id: number; account_title: string }>;
  auth: {
    user: {
      id: number;
    };
  };
  voucher_number: String
  purchase_order?: {
    id: number;
    po_no: string;
  };
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
    po_id: props.purchase_order?.id,
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
    // Cancel any active edits before submitting
    form.items.forEach((item, index) => {
      if (item.editing) {
        cancelEdit(index);
      }
    });

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

const editItem = (index: number) => {
  // Exit any other editing modes first
  form.items.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i);
    }
  });
  
  // Create a deep copy for the original state
  const originalItem = JSON.parse(JSON.stringify(form.items[index]));
  
  // Set editing mode for this item
  form.items[index] = {
    ...form.items[index],
    editing: true,
    original: originalItem
  };
};

const saveEdit = (index: number) => {
  if (form.items[index].editing) {
    form.items[index] = {
      ...form.items[index],
      editing: false
    };
    delete form.items[index].original;
    calculateTotalAmount();
    toast.success('Item updated');
  }
};

const cancelEdit = (index: number) => {
  if (form.items[index].original) {
    form.items[index] = {
      ...form.items[index].original,
      editing: false
    };
    delete form.items[index].original;
  }
};
const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Enter') {
    saveEdit(index);
  } else if (event.key === 'Escape') {
    cancelEdit(index);
  }
};
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
        <div class=" text-right">
            <h1 class="text-xl font-bold" title="voucher number"># {{ voucher_number }}</h1>
            <p v-if="purchase_order?.po_no" class="text-sm text-muted-foreground">
              Purchase Order #: <span class="font-medium mr-2">{{ purchase_order?.po_no }}</span> 
              Amount: <span class="font-medium">{{ formatCurrency(purchase_order?.amount) }}</span>
            </p>
        </div>
      </div>

      <form @submit.prevent="submitVoucher" class="space-y-6">
        <!-- Voucher Header Section -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <!-- Column 1 -->
          <div class="space-y-4">
            <div class="grid gap-2">
              <Label for="voucher_type">Voucher Type <span class="text-rose-600">*</span> </Label>
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
              <Label for="voucher_date">Voucher Date *</Label>
              <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
            </div>
            <div class="grid gap-2">
              <Label for="check_amount">Check Amount</Label>
              <Input 
                id="check_amount" 
                type="number" 
                step="1" 
                v-model="form.check_amount" 
                :disabled="true"
              />
            </div>
            <div class="grid gap-2">
              <Label for="check_date">Check Date</Label>
              <Input id="check_date" type="date" v-model="form.check_date" />
            </div>
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="purpose">Purpose *</Label>
          <Textarea id="purpose" v-model="form.purpose" required />
        </div>

        <!-- Items Section -->
        <div v-if="form.type" class="border rounded-lg p-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-medium capitalize"> {{ form.type }} Account Details</h3>
            </div>

            <!-- New Item Form -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 pb-4 border-b">
                <!-- Account Selection (3 columns) -->
                <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-4' : 'md:col-span-3'">
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
                <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-3' : 'md:col-span-2'">
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
                <div class="grid gap-2" :class="isCashVoucher ? 'md:col-span-5' : 'md:col-span-3'">
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
                  <th v-if="form.items.some(item => item.editing)" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr 
                  v-for="(item, index) in form.items" 
                  :key="index"
                  @click="!item.editing && editItem(index)"
                  :class="{
                    'hover:bg-gray-50 cursor-pointer': true,
                    'bg-blue-50': item.editing
                  }"
                >
        <!-- Account -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div v-if="!item.editing">
            {{ props.accounts?.find(a => a.id === parseInt(item.account_id))?.account_title || 'N/A' }}
          </div>
          <Combobox v-else v-model="item.account_id" by="id" @click.stop>
            <ComboboxAnchor as-child>
              <ComboboxTrigger as-child>
                <Button variant="outline" class="w-full justify-between h-10">
                  <span class="truncate">
                    {{ props.accounts?.find(a => a.id === parseInt(item.account_id))?.account_title || 'Select account' }}
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
        </td>
        
        <!-- Tag -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div v-if="!item.editing">{{ item.charging_tag || 'N/A' }}</div>
          <Select v-else v-model="item.charging_tag" @click.stop>
            <SelectTrigger>
              <SelectValue placeholder="Select tag" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="C">C</SelectItem>
              <SelectItem value="D">D</SelectItem>
            </SelectContent>
          </Select>
        </td>
        
        <!-- Hours (hidden for cash) -->
        <td v-if="!isCashVoucher" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div v-if="!item.editing">{{ item.hours?.toFixed(2) || 'N/A' }}</div>
          <Input 
            v-else
            type="number" 
            step="0.01" 
            v-model.number="item.hours"
            @click.stop
            @change="item.amount = item.hours * item.rate"
            class="w-full"
          />
        </td>
        
        <!-- Rate (hidden for cash) -->
        <td v-if="!isCashVoucher" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div v-if="!item.editing">{{ item.rate?.toFixed(2) || 'N/A' }}</div>
          <Input 
            v-else
            type="number" 
            step="0.01" 
            v-model.number="item.rate"
            @click.stop
            @change="item.amount = item.hours * item.rate"
            class="w-full"
          />
        </td>
        
        <!-- Amount -->
        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
          <div v-if="!item.editing">{{ item.amount.toFixed(2) }}</div>
          <Input 
            v-else
            type="number" 
            step="0.01" 
            v-model.number="item.amount"
            @click.stop
            class="w-full"
          />
        </td>
        
        <!-- Actions -->
        <td v-if="form.items.some(i => i.editing)" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
          <div class="flex justify-end space-x-2">
            <template v-if="item.editing">
              <Button
                variant="outline"
                size="sm"
                @click.stop="saveEdit(index)"
              >
                Save
              </Button>
              <Button
                variant="outline"
                size="sm"
                @click.stop="cancelEdit(index)"
              >
                Cancel
              </Button>
              <Button
                variant="destructive"
                size="sm"
                @click.stop="removeItem(index)"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </template>
            <Button
              v-else
              variant="destructive"
              size="sm"
              @click.stop="removeItem(index)"
            >
              <Trash2 class="h-4 w-4" />
            </Button>
          </div>
        </td>
      </tr>
      <tr v-if="form.items.length === 0">
        <td :colspan="isCashVoucher ? (form.items.some(i => i.editing) ? 5 : 4) : (form.items.some(i => i.editing) ? 6 : 5)" class="px-6 py-4 text-center text-sm text-gray-500">
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 hidden">
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