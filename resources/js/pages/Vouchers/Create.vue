<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { reactive, computed, ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { CardFooter } from '@/components/ui/card';
import { useToast } from 'vue-toastification';
import { Plus, Trash2, FilePlus2, Ban, Check, RefreshCcw, ChevronsUpDown, Search, TriangleAlert } from 'lucide-vue-next';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox';
import axios from 'axios'
import { formatCurrency } from '@/lib/utils';
import PageHeader from '@/components/PageHeader.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table'
import { onMounted } from "vue";

interface VoucherItem {
  amount: number;
  charging_tag: string | null;
  hours: number | null;
  rate: number | null;
  account_id: string;
  editing?: boolean;
  original?: VoucherItem;
}

interface DistributionExpense {
  id: number;
  account_name: string;
  amount: number;
  date: string;
  petty_cash_id: number;
  account_id?: number;
}

interface Props {
  accounts?: Array<{ id: number; account_title: string }>;
  auth: {
    user: {
      id: number;
    };
  };
  voucher_number: String;
  purchase_order?: {
    id: number;
    po_no: string;
    tin_no: string;
    payee?: string;
    check_payable_to?: string;
  };
  // Add these new props for distribution expenses
  distribution_expenses?: DistributionExpense[];
  petty_cash?: {
    id: number;
    pcv_no: string;
    paid_to: string;
    remarks?: string;
  };
  prefilled_data?: {
    paid_to?: string;
    total_amount?: number;
    remarks?: string;
  };
}

const props = defineProps<Props>();
const toast = useToast();
const accountSearchQuery = ref('');

const voucherNumberError = ref('');
const checkingVoucher = ref(false);
let checkTimeout = null;

const filteredAccounts = computed(() => {
  if (!accountSearchQuery.value) return props.accounts || [];
  return (props.accounts || []).filter(account =>
    account.account_title.toLowerCase().includes(accountSearchQuery.value.toLowerCase())
  );
});

const form = reactive({
    po_id: props.purchase_order?.id,
    voucher_no: props.voucher_number,
    tin_no: props.purchase_order?.tin_no,
    issue_date: '',
    payment_date: '',
    delivery_date: '',
    voucher_date: new Date().toISOString().split('T')[0],
    purpose: '',
    payee: '',
    check_no: null,
    check_payable_to: '',
    check_amount: 0,
    status: 'forAudit',
    type: 'cash',
    user_id: props.auth.user.id,
    items: [] as VoucherItem[],
});

if (props.purchase_order) {
  if (props.purchase_order.payee) {
    form.payee = props.purchase_order.payee;
  }
  if (props.purchase_order.check_payable_to) {
    form.check_payable_to = props.purchase_order.check_payable_to;
  }
}

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
  { title: 'Create Voucher', href: '#' },
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
  form.check_amount = form.items.reduce((sum, item) => {
    if (item.charging_tag === 'C') {
      return sum + item.amount;
    } else if (item.charging_tag === 'D') {
      return sum - item.amount;
    }
    return sum; // if no tag is selected
  }, 0);
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

const calculateTotal = (tag: string) => {
  return form.items.reduce((sum, item) => {
    if (item.charging_tag === tag) {
      return sum + item.amount;
    }
    return sum;
  }, 0);
};

// Simple debounced function using setTimeout
const checkVoucherNumber = (voucherNo) => {
    // Clear previous timeout
    if (checkTimeout) {
        clearTimeout(checkTimeout);
    }
    
    if (!voucherNo || voucherNo.trim() === '') {
        voucherNumberError.value = 'Voucher number is required';
        return;
    }

    // Set new timeout
    checkTimeout = setTimeout(async () => {
        checkingVoucher.value = true;
        
        try {
            // If editing, pass the current voucher ID to ignore
            const params = new URLSearchParams({
                voucher_no: voucherNo,
                // ignore_id: props.voucher?.id // Uncomment if you're editing
            });
            
            const response = await axios.get(`/api/vouchers/check-number?${params}`);
            console.log('API Response:', response.data);
            
            if (!response.data.available) {
                voucherNumberError.value = 'Voucher number already exists';
                // Show toast notification
                toast.error('Voucher number already exists in the system', {
                    timeout: 3000,
                    position: 'top-right'
                });
            } else {
                voucherNumberError.value = '';
                // Optional: Show success toast when available
                // toast.success('Voucher number is available');
            }
        } catch (error) {
            console.error('Error checking voucher number:', error);
            // Show error toast for API failures
            toast.error('Unable to validate voucher number', {
                timeout: 3000,
                position: 'top-right'
            });
        } finally {
            checkingVoucher.value = false;
        }
    }, 500); // Wait 500ms after user stops typing
};

// Watch for changes in voucher number
watch(() => form.voucher_no, (newVal) => {
    checkVoucherNumber(newVal);
});

// Function to generate a new voucher number suggestion
const suggestVoucherNumber = async () => {
    try {
        const response = await axios.get('/api/vouchers/generate-number');
        if (response.data.success) {
            form.voucher_no = response.data.voucher_number;
        }
    } catch (error) {
        console.error('Error generating voucher number:', error);
    }
};

async function submitVoucher() {
  try {

    // Check if voucher number is provided
    if (!form.voucher_no || form.voucher_no.trim() === '') {
      toast.error('Voucher number is required');
      return;
    }

    if (voucherNumberError.value) {
        toast.error(voucherNumberError.value);
        return;
    }
    
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


onMounted(() => {
    if (props.distribution_expenses && props.distribution_expenses.length > 0) {
        props.distribution_expenses.forEach(d => {
            form.items.push({
                account_id: findAccountIdByName(d.account_name),
                charging_tag: "C",
                amount: Number(d.amount),
                hours: null,
                rate: null,
                editing: false
            });
        });

        calculateTotalAmount(); // ← update totals after populating
    }
});


const findAccountIdByName = (name) => {
    const acc = props.accounts.find(a => a.account_title === name);
    return acc ? acc.id.toString() : null;
};

import { onUnmounted } from 'vue';

onUnmounted(() => {
    if (checkTimeout) {
        clearTimeout(checkTimeout);
    }
});
</script>

<template>
  <Head title="Create Voucher" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-6 mx-auto w-full">
      <div class="flex justify-between items-center">
        <PageHeader 
          title="Create Voucher" 
          subtitle="Complete all required fields"
        />
        <div class="text-right">
          <div class="flex items-center justify-end gap-2">
            <div class="relative">
                <!-- Prefix # symbol inside input -->
                <div class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none">
                    #
                </div>
                
                <!-- Input with left padding for the # symbol -->
                <Input 
                    v-model="form.voucher_no"
                    class="w-48 text-right font-bold text-lg h-9 pl-6 pr-8"
                    :class="{ 'border-destructive': voucherNumberError }"
                    placeholder="Voucher number"
                    title="voucher number"
                    @blur="checkVoucherNumber(form.voucher_no)"
                />
                
                <!-- Loading spinner -->
                <div v-if="checkingVoucher" class="absolute right-2 top-1/2 -translate-y-1/2">
                    <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
                </div>
                
                <!-- Success indicator -->
                <div v-else-if="form.voucher_no && !voucherNumberError" class="absolute right-2 top-1/2 -translate-y-1/2">
                    <Check class="h-4 w-4 text-green-600" />
                </div>
                <div v-else-if="form.voucher_no && voucherNumberError" class="absolute right-2 top-1/2 -translate-y-1/2">
                    <TriangleAlert class="h-4 w-4 text-red-600" />
                </div>
            </div>
            
            <!-- Suggest button -->
            <Button 
                type="button" 
                variant="ghost" 
                size="sm"
                @click="suggestVoucherNumber"
                class="h-8 px-2"
                title="Generate next available number"
            >
                <RefreshCcw class="h-4 w-4" />
            </Button>
        </div>
          
          <p v-if="purchase_order?.po_no" class="text-sm text-muted-foreground mt-1">
              Purchase Order #: <span class="font-medium mr-2">{{ purchase_order?.po_no }}</span> 
              Amount: <span class="font-medium">{{ formatCurrency(purchase_order?.amount) }}</span>
          </p>
      </div>
      </div>

      <form @submit.prevent="submitVoucher" class="space-y-6">
        <!-- Voucher Header Section -->
        <div class="grid grid-cols-3 gap-4">
          <div class="...">
            <div class="grid gap-2">
              <Label for="payee">Payee *</Label>
              <Input id="payee" v-model="form.payee" required />
            </div>
          </div>
          <div class="...">
            <div class="grid gap-2">
              <Label for="tin_no">TIN</Label>
              <Input id="tin_no" v-model="form.tin_no" />
            </div>
          </div>
          <div class="...">
            <div class="grid gap-2">
              <Label for="voucher_date">Voucher Date *</Label>
              <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="...">
            <div class="grid gap-2">
              <Label for="check_payable_to">Check Pay To *</Label>
              <Input id="check_payable_to" v-model="form.check_payable_to" required />
            </div>
          </div>
          <div class="...">
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
                    @click="addItem"
                    :disabled="!newItem.charging_tag || !newItem.amount"
                  >
                    <Plus class="h-4 w-4" /> Accept
                  </Button>
                  </div>
              </div>
          </div>

          <!-- Items Table -->
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="text-left">Account</TableHead>
                <TableHead class="text-left">Tag</TableHead>
                <TableHead class="text-left">Amount</TableHead>
                <TableHead 
                  v-if="form.items.some(item => item.editing)" 
                  class="text-right"
                >
                  Actions
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(item, index) in form.items" 
                :key="index"
                @click="!item.editing && editItem(index)"
                :class="{
                  'cursor-pointer': true
                }"
              >
                <!-- Account -->
                <TableCell class="whitespace-nowrap">
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
                </TableCell>
                
                <!-- Tag -->
                <TableCell class="whitespace-nowrap">
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
                </TableCell>
                
                <!-- Amount -->
                <TableCell class="whitespace-nowrap">
                  <div v-if="!item.editing">{{ item.amount.toFixed(2) }}</div>
                  <Input 
                    v-else
                    type="number" 
                    step="0.01" 
                    v-model.number="item.amount"
                    @click.stop
                    class="w-full"
                  />
                </TableCell>
                
                <!-- Actions -->
                <TableCell v-if="form.items.some(i => i.editing)" class="whitespace-nowrap text-right">
                  <div class="flex justify-end space-x-2">
                    <template v-if="item.editing">
                      <Button
                        variant="default"
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
                </TableCell>
              </TableRow>
              <TableRow v-if="form.items.length === 0">
                <TableCell 
                  :colspan="isCashVoucher ? (form.items.some(i => i.editing) ? 5 : 4) : (form.items.some(i => i.editing) ? 6 : 5)"
                  class="text-center text-sm text-gray-500"
                >
                  No items added yet
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <!-- Total Amount -->
        <div class="flex justify-between mt-4">
          <p class="text-sm text-muted-foreground ml-2">
            (C: ₱{{ calculateTotal('C').toFixed(2) }} - D: ₱{{ calculateTotal('D').toFixed(2) }})
          </p>
          <div class="text-lg font-semibold text-right">
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
        <CardFooter class="flex justify-end gap-2 px-0 pb-0">
          <Button 
            type="button" 
            variant="outline" 
            @click="router.visit('/vouchers')"
          >
            <Ban />Cancel
          </Button>
          <Button 
            type="submit" 
            :disabled="!isCashVoucher && form.items.length === 0"
          ><FilePlus2 class="h-4 w-4" />
            Create Voucher
          </Button>
        </CardFooter>
      </form>
    </div>
  </AppLayout>
</template>