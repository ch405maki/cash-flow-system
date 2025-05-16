<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
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
import { Plus, Trash2 } from 'lucide-vue-next'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, onMounted} from 'vue'
import { type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: 'Create Voucher', href: '/create' },
];

const toast = useToast();
const { props } = usePage();
const accounts = props.accounts || [];
const voucherNo = ref('Loading...');

// Fetch the next voucher number from the backend
const fetchNextVoucherNumber = async () => {
    try {
        const response = await axios.get('/api/vouchers/next-number');
        voucherNo.value = response.data.voucher_no;
    } catch (error) {
        toast.error('Failed to load voucher number');
        voucherNo.value = 'Error loading number';
    }
};

onMounted(() => {
    fetchNextVoucherNumber();
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
    voucher_no: voucherNo.value,
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

async function submitVoucher() {
    try {
        // Prepare payload based on voucher type
        const payload = {
            ...form,
            voucher_no: form.voucher_no,
            // For Salary vouchers, don't send check details
            check: form.type === 'salary' ? [] : form.check
        };

        const response = await axios.post('/api/vouchers', payload);

        toast.success(response.data.message || 'Voucher created successfully');
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
        <Card class="mt-6 max-w-6xl mx-auto">
            <CardHeader>
                <CardTitle>Create Voucher</CardTitle>
                <CardDescription>Complete all required fields</CardDescription>
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
                                <Input id="check_payable_to" v-model="form.check_payable_to" 
                                     required />
                            </div>
                           
                        </div>

                        <!-- Column 3 -->
                        <div class="space-y-4">
                            <div class="grid gap-2">
                                <Label for="check_amount">Check Amount *</Label>
                                <Input id="check_amount" type="number" step="0.01" v-model="form.check_amount"
                                 />
                            </div>

                            <div class="grid gap-2">
                                <Label for="purpose">Purpose *</Label>
                                <Input id="purpose" v-model="form.purpose" required />
                            </div>

                        </div>
                    </div>

                    <!-- Voucher Details Section (Only shown for Non-Salary Vouchers) -->
                    <div v-if="form.type === 'cash'" class="border rounded-lg p-4 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="font-medium">Add/Remove Accounts</h3>
                            <Button type="button" variant="outline" size="sm" @click="addDetailItem">
                                <Plus class="h-4 w-4 mr-2" />
                                Add Item
                            </Button>
                        </div>

                        <div v-for="(detail, index) in form.check" :key="index"
                            class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4 pb-4 border-b last:border-0">
                            <!-- Account Selection -->
                            <div class="grid gap-2">
                                <Label :for="`account-${index}`">Account *</Label>
                                <Select v-model="detail.account_id">
                                    <SelectTrigger>
                                        <SelectValue placeholder="Select account" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="account in accounts" :key="account.id" :value="account.id">
                                            {{ account.account_title }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <!-- Charging Tag -->
                            <div class="grid gap-2">
                                <Label :for="`tag-${index}`">Charging Tag *</Label>
                                <Select v-model="detail.charging_tag" >
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
                                <Input :id="`hours-${index}`" type="number" step="0.01" v-model="detail.hours"
                                    @blur="calculateAmountFromRate(index)" placeholder="Optional" />
                            </div>

                            <!-- Rate -->
                            <div class="grid gap-2">
                                <Label :for="`rate-${index}`">Rate</Label>
                                <Input :id="`rate-${index}`" type="number" step="0.01" v-model="detail.rate"
                                    @blur="calculateAmountFromRate(index)" placeholder="Optional" />
                            </div>

                            <!-- Amount -->
                            <div class="grid gap-2">
                                <Label :for="`amount-${index}`">Amount *</Label>
                                <div class="flex gap-2">
                                    <Input :id="`amount-${index}`" type="number" step="0.01" v-model="detail.amount"
                                        @change="calculateTotalAmount" required class="flex-1" />
                                    <Button type="button" variant="destructive" size="icon"
                                        @click="removeDetailItem(index)" :disabled="form.check.length <= 1">
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