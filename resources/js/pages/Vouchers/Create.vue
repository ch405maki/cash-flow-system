<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Head, 
    useForm, 
    usePage, 
    router 
} from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue
} from '@/components/ui/select'
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import {
  Combobox,
  ComboboxAnchor,
  ComboboxEmpty,
  ComboboxGroup,
  ComboboxInput,
  ComboboxItem,
  ComboboxItemIndicator,
  ComboboxList,
  ComboboxTrigger
} from '@/components/ui/combobox'
import { Plus, Trash2, ArrowLeft, Check, ChevronsUpDown, Search } from 'lucide-vue-next'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: 'Create Voucher', href: '/create' },
];

const toast = useToast();
const { props } = usePage();
const accounts = props.accounts || [];
const accountSearchQuery = ref('');

const filteredAccounts = computed(() => {
  if (!accountSearchQuery.value) return accounts;
  
  const query = accountSearchQuery.value.toLowerCase();
  return accounts.filter(account => 
    account.account_title.toLowerCase().includes(query))
});

const form = useForm({
    issue_date: '',
    payment_date: '',
    check_date: '',
    delivery_date: '',
    voucher_date: '',
    purpose: '',
    payee: '',
    check_no: null,
    check_payable_to: '',
    check_amount: null,
    status: 'pending',
    type: '',
    user_id: props.auth.user.id,
    check: [
        {
            amount: 0,
            charging_tag: null,
            hours: null,
            rate: null,
            account_id: ''
        }
    ]
});

const addDetailItem = () => {
    form.check.push({
        amount: 0,
        charging_tag: null,
        hours: null,
        rate: null,
        account_id: ''
    });
};

const removeDetailItem = (index) => {
    if (form.check.length > 1) {
        form.check.splice(index, 1);
        calculateTotalAmount();
    }
};

const calculateTotalAmount = () => {
    form.check_amount = form.check.reduce((sum, item) => {
        return sum + (parseFloat(item.amount) || 0);
    }, 0);
};

const calculateAmountFromRate = (index) => {
    const detail = form.check[index];
    if (detail.hours && detail.rate) {
        detail.amount = parseFloat(detail.hours) * parseFloat(detail.rate);
        calculateTotalAmount();
    }
};

const isCashVoucher = computed(() => form.type === 'cash');

async function submitVoucher() {
    try {
        // Prepare the base payload
        const payload = {
            issue_date: form.issue_date,
            payment_date: form.payment_date,
            check_date: form.check_date,
            delivery_date: form.delivery_date,
            voucher_date: form.voucher_date,
            purpose: form.purpose,
            payee: form.payee,
            check_no: form.check_no,
            check_payable_to: form.check_payable_to,
            check_amount: form.check_amount,
            status: form.status,
            type: form.type,
            user_id: form.user_id,
        };

        // Only add check details if type is salary
        if (form.type === 'salary') {
            payload.check = form.check.map(item => ({
                account_id: item.account_id,
                amount: item.amount,
                charging_tag: item.charging_tag,
                hours: item.hours,
                rate: item.rate
            }));
        }

        const response = await axios.post('/api/vouchers', payload);
        toast.success(`Voucher created Successfully! Number: ${response.data.data.voucher_no}`);
        router.visit('/vouchers');
    } catch (error) {
        if (error.response?.data?.errors) {
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
        <Card class="mt-6 mx-auto w-full">
            <CardHeader>
                <div class="flex justify-between items-start">
                    <div>
                        <CardTitle>Create Voucher</CardTitle>
                        <CardDescription>Complete all required fields</CardDescription>
                    </div>
                    <Button 
                        variant="outline" 
                        @click="router.visit('/vouchers')"
                        class="flex items-center gap-2"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Button>
                </div>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submitVoucher">
                    <!-- Voucher Header Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                        <!-- Column 1 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="voucher_date">Voucher Date *</Label>
                                <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="voucher_type">Voucher Type *</Label>
                                <Select v-model="form.type" required>
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
                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="check_no">Check No</Label>
                                <Input id="check_no" v-model="form.check_no" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="check_date">Check Date *</Label>
                                <Input id="check_date" type="date" v-model="form.check_date" required />
                            </div>
                            
                            <div class="grid gap-2">
                                <Label for="check_payable_to">Payable To *</Label>
                                <Input id="check_payable_to" v-model="form.check_payable_to" required />
                            </div>
                        </div>

                        <!-- Column 3 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="check_amount">Check Amount *</Label>
                                <Input id="check_amount" type="number" step="0.01" v-model="form.check_amount" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="purpose">Purpose *</Label>
                                <Input id="purpose" v-model="form.purpose" required />
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Details Section (Only shown for Non-Cash Vouchers) -->
                    <div class="border rounded-lg p-4 mb-6" :class="{ 'opacity-50': isCashVoucher }">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-medium">Account Details</h3>
                            <Button 
                                type="button" 
                                variant="outline" 
                                size="sm" 
                                @click="addDetailItem"
                                :disabled="isCashVoucher"
                            >
                                <Plus class="h-4 w-4 mr-2" />
                                Add Item
                            </Button>
                        </div>

                        <div 
                            v-for="(detail, index) in form.check" 
                            :key="index"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0"
                        >
                            <!-- Account Selection with Combobox -->
                            <div class="grid gap-2">
                            <Label :for="`account-${index}`">Account <span v-if="!isCashVoucher">*</span></Label>
                            <Combobox 
                                v-model="detail.account_id" 
                                :disabled="isCashVoucher"
                                by="id"
                            >
                                <ComboboxAnchor as-child>
                                <ComboboxTrigger as-child>
                                    <Button 
                                    variant="outline" 
                                    class="w-full justify-between"
                                    :disabled="isCashVoucher"
                                    >
                                    {{ accounts.find(a => a.id === detail.account_id)?.account_title || 'Select account' }}
                                    <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
                                    </Button>
                                </ComboboxTrigger>
                                </ComboboxAnchor>

                                <ComboboxList class="max-h-[180px] overflow-y-auto">
  <!-- Search Input (unchanged) -->
  <div class="relative w-full items-center">
    <ComboboxInput 
      class="pl-9 focus-visible:ring-0 border-0 border-b rounded-none h-10" 
      placeholder="Search accounts..."
      v-model="accountSearchQuery"
    />
    <span class="absolute start-0 inset-y-0 flex items-center justify-center px-3">
      <Search class="size-4 text-muted-foreground" />
    </span>
  </div>

  <!-- Empty State -->
  <ComboboxEmpty v-if="filteredAccounts.length === 0">
    No accounts found.
  </ComboboxEmpty>

  <!-- Scrollable Group (Limited to 5 items) -->
  <ComboboxGroup>
    <div class="max-h-[150px] overflow-y-auto"> <!-- Scroll container -->
      <ComboboxItem
        v-for="(account, index) in filteredAccounts.slice(0)"
        :key="account.id"
        :value="account.id"
      >
        {{ account.account_title }}
        <ComboboxItemIndicator>
          <Check class="ml-auto h-4 w-4" />
        </ComboboxItemIndicator>
      </ComboboxItem>
    </div>
  </ComboboxGroup>
</ComboboxList>
                            </Combobox>
                            </div>

                            <!-- Charging Tag -->
                            <div class="grid gap-2">
                                <Label :for="`tag-${index}`">Charging Tag <span v-if="!isCashVoucher">*</span></Label>
                                <Select v-model="detail.charging_tag" :disabled="isCashVoucher">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select Charging tag" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="C">C</SelectItem>
                                        <SelectItem value="D">D</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Hours -->
                            <div class="grid gap-2">
                                <Label :for="`hours-${index}`">Hours</Label>
                                <Input 
                                    :id="`hours-${index}`" 
                                    type="number" 
                                    step="0.01" 
                                    v-model="detail.hours"
                                    @blur="!isCashVoucher && calculateAmountFromRate(index)" 
                                    placeholder="Optional"
                                    :disabled="isCashVoucher"
                                />
                            </div>

                            <!-- Rate -->
                            <div class="grid gap-2">
                                <Label :for="`rate-${index}`">Rate</Label>
                                <Input 
                                    :id="`rate-${index}`" 
                                    type="number" 
                                    step="0.01" 
                                    v-model="detail.rate"
                                    @blur="!isCashVoucher && calculateAmountFromRate(index)" 
                                    placeholder="Optional"
                                    :disabled="isCashVoucher"
                                />
                            </div>

                            <!-- Amount -->
                            <div class="grid gap-2">
                                <Label :for="`amount-${index}`">Amount <span v-if="!isCashVoucher">*</span></Label>
                                <div class="flex gap-2">
                                    <Input 
                                        :id="`amount-${index}`" 
                                        type="number" 
                                        step="0.01" 
                                        v-model="detail.amount"
                                        @change="!isCashVoucher && calculateTotalAmount" 
                                        :required="!isCashVoucher" 
                                        class="flex-1"
                                        :disabled="isCashVoucher"
                                    />
                                    <Button 
                                        type="button" 
                                        variant="destructive" 
                                        size="icon"
                                        @click="removeDetailItem(index)" 
                                        :disabled="form.check.length <= 1 || isCashVoucher"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dates Section -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                        <div class="grid gap-2">
                            <Label for="issue_date">Issue Date *</Label>
                            <Input id="issue_date" type="date" v-model="form.issue_date" required />
                        </div>

                        <div class="grid gap-2">
                            <Label for="payment_date">Payment Date *</Label>
                            <Input id="payment_date" type="date" v-model="form.payment_date" required />
                        </div>

                        <div class="grid gap-2">
                            <Label for="delivery_date">Delivery Date *</Label>
                            <Input id="delivery_date" type="date" v-model="form.delivery_date" required />
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <CardFooter class="flex justify-end gap-4 px-0 pb-0">
                        <Button variant="outline" type="button" @click="form.reset()">
                            Reset
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Create Voucher</span>
                        </Button>
                    </CardFooter>
                </form>
            </CardContent>
        </Card>
    </AppLayout>
</template>