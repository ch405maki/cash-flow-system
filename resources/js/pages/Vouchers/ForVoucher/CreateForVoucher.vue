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
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, computed } from 'vue'
import { type BreadcrumbItem } from '@/types';


const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/for-voucher' },
    { title: 'Create Voucher', href: '/create' },
];

const toast = useToast();
const { props } = usePage();


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
    status: 'draft',
    type: 'cash',
    user_id: props.auth.user.id,
});



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
        <div class="mt-6 mx-auto w-full">
            <CardHeader>
                <div class="flex justify-between items-start">
                    <div>
                        <CardTitle>Create Voucher</CardTitle>
                        <CardDescription>Complete all required fields</CardDescription>
                    </div>
                </div>
            </CardHeader>

            <CardContent>
                <form @submit.prevent="submitVoucher">
                    <!-- Voucher Header Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <!-- Column 1 -->
                        <div class="space-y-4">

                            <div class="grid gap-2">
                                <Label for="payee">Payee *</Label>
                                <Input id="payee" v-model="form.payee" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="check_payable_to">Check Pay To *</Label>
                                <Input id="check_payable_to" v-model="form.check_payable_to" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="purpose">Purpose *</Label>
                                <Input id="purpose" v-model="form.purpose" required />
                            </div>

                        </div>

                        <!-- Column 2 -->
                        <div class="space-y-4">

                            <div class="grid gap-2">
                                <Label for="check_amount">Check Amount *</Label>
                                <Input id="check_amount" type="number" step="0.01" v-model="form.check_amount" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="voucher_date">Voucher Date *</Label>
                                <Input id="voucher_date" type="date" v-model="form.voucher_date" required />
                            </div>

                            <div class="grid gap-2">
                                <Label for="check_date">Check Date *</Label>
                                <Input id="check_date" type="date" v-model="form.check_date" required />
                            </div>
                        </div>
                    </div>

                    <!-- Voucher Details Section (Only shown for Non-Cash Vouchers) -->
                    

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
        </div>
    </AppLayout>
</template>