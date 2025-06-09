<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogDescription,
    DialogFooter
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { computed, ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Printer, Check, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import FormHeader from '@/components/reports/header/formHeder.vue'
import PasswordVerificationDialog from '@/components/PasswordVerificationDialog.vue';
import { useToast } from 'vue-toastification'



const toast = useToast();
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash, (flash) => {
  if (flash.success) {
    toast.success(flash.success);
  }
  if (flash.error) {
    toast.error(flash.error);
  }
}, { deep: true });

const props = defineProps({
    accounts: {
        type: Array,
        default: () => [],
        required: true,
    },
    voucher: {
        type: Object,
        required: true,
    },
    authUser: {
        type: Object,
        required: true,
    },
    signatories: {
        type: Object,
        required: true,
    }
});

const { accounts, voucher, authUser, signatories } = props;

const executiveDirector = computed(() =>
    props.signatories.find(s => s.position === 'Executive Director')
);

const directorAccounting = computed(() =>
    props.signatories.find(s => s.position === 'Director, Accounting')
);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `Voucher ${voucher.voucher_no}`, href: `/vouchers/${voucher.id}` },
];

const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;

    const months = [
        'January', 'February', 'March', 'April',
        'May', 'June', 'July', 'August',
        'September', 'October', 'November', 'December'
    ];

    return `${months[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
};

const formatCurrency = (value: any) => {
    const num = Number(value);
    return isNaN(num) ? '0.00' : num.toFixed(2);
};

const amountToWords = (amount: number) => {
    const whole = Math.floor(amount);
    const fraction = Math.round((amount - whole) * 100);

    const words = [
        '', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
        'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen',
        'Seventeen', 'Eighteen', 'Nineteen'
    ];

    const tens = [
        '', '', 'Twenty', 'Thirty', 'Forty', 'Fifty',
        'Sixty', 'Seventy', 'Eighty', 'Ninety'
    ];

    const convertLessThanOneThousand = (num: number): string => {
        if (num < 20) return words[num];
        if (num < 100) {
            return tens[Math.floor(num / 10)] +
                (num % 10 ? ' ' + words[num % 10] : '');
        }

        const hundreds = Math.floor(num / 100);
        const remainder = num % 100;

        return words[hundreds] + ' Hundred' +
            (remainder ? ' ' + convertLessThanOneThousand(remainder) : '');
    };

    let result = 'Zero';
    if (whole > 0) {
        if (whole < 1000) {
            result = convertLessThanOneThousand(whole);
        } else if (whole < 1000000) {
            const thousands = Math.floor(whole / 1000);
            const remainder = whole % 1000;

            result = convertLessThanOneThousand(thousands) + ' Thousand' +
                (remainder ? ' ' + convertLessThanOneThousand(remainder) : '');
        } else {
            return 'Amount exceeds conversion limit';
        }
    }

    result = result.trim() + ' Pesos';

    if (fraction > 0) {
        result += ' and ' + convertLessThanOneThousand(fraction) + ' Centavos';
    }

    return result;
};

const printArea = () => {
    const printContents = document.getElementById('print-section')?.innerHTML;
    const originalContents = document.body.innerHTML;

    if (printContents) {
        document.body.innerHTML = printContents;
        window.print();
        document.body.innerHTML = originalContents;
        location.reload();
    } else {
        console.error('Print section not found');
    }
}

const updateVoucherStatus = async (action) => {
    try {
        await router.patch(`/vouchers/${voucher.id}/status`, { action });
        toast.success(`Voucher ${action === 'approve' ? 'Approved' : 'Rejected'} Successfully`);
        router.visit(route('vouchers.view', { voucher: voucher.id }));
    } catch (error) {
        toast.error(`Failed to ${action} voucher`);
        console.error('Error:', error);
    }
}

const approveVoucher = () => updateVoucherStatus('approve');
const rejectVoucher = () => updateVoucherStatus('reject');


</script>

<template>

    <Head :title="`View Voucher - ${voucher.voucher_no}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold">Voucher {{ voucher.voucher_no }}</h2>
                    <p class="text-sm text-muted-foreground">Voucher Details</p>
                </div>
                <div class="flex gap-2">
                    <Button variant="default" @click="printArea" class="flex items-center gap-2">
                        <Printer class="h-4 w-4" />
                        Print
                    </Button>
                    <template v-if="authUser.role === 'executive_director' && voucher.status !== 'approved' && voucher.status !== 'rejected'">
  <PasswordVerificationDialog :voucher-id="voucher.id">
    <template #trigger>
      <Button 
        variant="default" 
        class="flex items-center gap-2 bg-green-500 text-white hover:bg-green-400"
      >
        <Check class="h-4 w-4" />
        Approve
      </Button>
    </template>
  </PasswordVerificationDialog>
  
  <Button 
    variant="default" 
    @click="rejectVoucher"
    class="flex items-center gap-2 bg-red-500 text-white hover:bg-red-400"
  >
    <X class="h-4 w-4" />
    Reject
  </Button>
</template>

                    <Button variant="outline" @click="router.visit('/vouchers')" class="flex items-center gap-2">
                        <ArrowLeft class="h-4 w-4" />
                        Back
                    </Button>
                </div>
            </div>

            <!-- Main View Content -->
            <!-- Voucher Information Tables -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
                <!-- First Table -->
                <table class="w-full text-sm border border-border rounded-md">
                    <tbody>
                        <tr class="border-b">
                            <td class="p-2 font-medium text-muted-foreground border-r w-48">PAYEE:</td>
                            <td class="p-2 uppercase border-r w-xl">{{ voucher.payee }}</td>
                            <td class="p-2 font-medium text-muted-foreground border-r w-40">VOUCHER NO:</td>
                            <td class="p-2 w-40">{{ voucher.voucher_no }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</td>
                            <td class="p-2 uppercase border-r">{{ voucher.check_payable_to }}</td>
                            <td class="p-2 font-medium text-muted-foreground border-r">VOUCHER DATE:</td>
                            <td class="p-2">{{ formatDate(voucher.voucher_date) }}</td>
                        </tr>
                        <tr class="border-b">
                            <td class="p-2 font-medium text-muted-foreground border-r">PURPOSE:</td>
                            <td class="p-2 uppercase border-r">{{ voucher.purpose }}</td>
                            <td class="p-2 font-medium text-muted-foreground border-r">CHECK AMOUNT:</td>
                            <td class="p-2">₱{{ formatCurrency(voucher.check_amount) }}</td>
                        </tr>
                    </tbody>
                </table>

                <!-- Second Table -->
                <table class="w-full text-sm border border-border rounded-md">
                    <tbody>
                        <tr class="border-b">
                            <td class="p-2 font-medium text-muted-foreground border-r w-48">CHECK NUMBER:</td>
                            <td class="p-2 uppercase border-r">{{ voucher.check_no }}</td>
                            <td class="p-2 font-medium text-muted-foreground border-r">CHECK DATE:</td>
                            <td class="p-2">{{ formatDate(voucher.check_date) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-medium text-muted-foreground border-r">STATUS:</td>
                            <td class="p-2 uppercase" colspan="3">
                                <span class="py-1 rounded-full font-bold capitalize min-w-[100px]">
                                    {{ voucher.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dates Table -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-1 ">
                <table class="w-full text-sm border border-border rounded-md">
                    <tbody>
                        <tr class="border-b">
                            <td class="p-2 font-medium text-muted-foreground border-r w-48">ISSUE DATE:</td>
                            <td class="p-2 border-r">{{ formatDate(voucher.issue_date) }}</td>
                            <td class="p-2 font-medium text-muted-foreground border-r">PAYMENT DATE:</td>
                            <td class="p-2">{{ formatDate(voucher.payment_date) }}</td>
                        </tr>
                        <tr>
                            <td class="p-2 font-medium text-muted-foreground border-r">DELIVERY DATE:</td>
                            <td class="p-2" colspan="3">{{ formatDate(voucher.delivery_date) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Account Details Table (if not cash voucher) -->
            <div v-if="voucher.type !== 'cash'" class="space-y-2 ">
                <h3 class="font-medium">Account Details</h3>
                <Table class="w-full text-sm border border-border rounded-md">
                    <TableHeader>
                        <TableRow>
                            <TableHead>Account</TableHead>
                            <TableHead>Charging Tag</TableHead>
                            <TableHead>Hours</TableHead>
                            <TableHead>Rate</TableHead>
                            <TableHead class="text-right">Amount</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="(detail, index) in voucher.details" :key="index">
                            <TableCell>
                                {{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}
                            </TableCell>
                            <TableCell>{{ detail.charging_tag }}</TableCell>
                            <TableCell>{{ detail.hours || 'N/A' }}</TableCell>
                            <TableCell>{{ detail.rate || 'N/A' }}</TableCell>
                            <TableCell class="text-right">₱{{ formatCurrency(detail.amount) }}</TableCell>
                        </TableRow>
                        <TableRow>
                            <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
                            <TableCell class="text-right font-bold">
                                ₱{{ formatCurrency(voucher.check_amount) }}
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <!-- Hidden Printable Voucher -->
            <div id="print-section">
                <div id="printable-voucher" class="hidden print:block mx-4">
                    <div class="text-center font-bold mb-5">
                        <FormHeader
                            :text="voucher.type.charAt(0).toUpperCase() + voucher.type.slice(1).toLowerCase() + ' Voucher'"
                            :bordered="false" /> <br>
                    </div>

                    <table class="w-full">
                        <tr>
                            <td class="w-[70%]">
                                Voucher No.: {{ voucher.voucher_no }} <br>
                                Voucher Date: {{ formatDate(voucher.voucher_date) }}
                            </td>
                        </tr>
                    </table>

                    <div class="my-4"></div>

                    <table class="w-full">
                        <tr>
                            <td>Payee: {{ voucher.payee }} </td>
                        </tr>
                        <tr>
                            <td>Check Payable to: {{ voucher.check_payable_to }}</td>
                        </tr>
                        <tr>
                            <td>Check No./ Date: {{ formatDate(voucher.check_date) }} </td>
                        </tr>
                        <tr>
                            <td>Amount: ₱{{ formatCurrency(voucher.check_amount) }}</td>
                        </tr>
                    </table>
                    <div class="border-b border-black pb-1 mb-1"></div>
                    <div class="border-b border-black pb-1 mb-1"></div>
                    <div class="flex justify-between">
                        <div>Payment for {{ formatDate(voucher.payment_date) }}</div>
                        <div>₱{{ formatCurrency(voucher.check_amount) }}</div>
                    </div>

                    <div class="border-b border-black pb-1 mb-1"></div>
                    <div class="border-b border-black pb-1 mb-1"></div>
                    <!-- ACCOUNT CHARGED section-->
                    <div class="font-bold mt-4 mb-2 text-center">ACCOUNT CHARGED</div>
                    <table class="w-full">
                        <template v-if="voucher.type === 'salary'">
                            <!-- Salary Voucher - Detailed Breakdown -->
                            <thead>
                                <tr>
                                    <th class="text-left w-[70%]">List of Accounts</th>
                                    <th class="text-right w-[30%]">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="detail in voucher.details" :key="detail.id">
                                    <td class="text-left">
                                        {{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}
                                    </td>
                                    <td class="text-right">₱{{ formatCurrency(detail.amount) }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="text-right"><strong>TOTAL AMOUNT:</strong></td>
                                    <td class="text-right border border-black p-1 font-bold">
                                        ₱{{formatCurrency(voucher.details.reduce((sum, detail) => sum +
                                            Number(detail.amount), 0))}}
                                    </td>
                                </tr>
                            </tfoot>
                        </template>
                        <template v-else>
                            <!-- Non-Salary Voucher - General Charges Only -->
                            <tr>
                                <td class="text-left w-[70%]">GENERAL CHARGES</td>
                                <td class="text-right w-[30%]">₱{{ formatCurrency(voucher.check_amount) }}</td>
                            </tr>
                            <tr v-for="i in 5" :key="i">
                                <td><br></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="text-right"><strong>TOTAL:</strong></td>
                                <td class="text-right border border-black p-1 font-bold">
                                    ₱{{ formatCurrency(voucher.check_amount) }}
                                </td>
                            </tr>
                        </template>
                    </table>

                    <div class="my-8"></div>
                    <div class="border-b border-black pb-1 mb-1"></div>
                    <div class="border-b border-black pb-1 mb-1"></div>
                    <div class="font-bold mt-4 mb-2">RECOMMENDING APPROVAL:</div>

                    <div class="my-4"></div>

                    <table class="w-full">
                        <tr>
                            <td class="w-1/2">
                                <div class="text-right w-1/6">
                                    <div class="my-4"></div>
                                    <div v-if="directorAccounting" class="relative inline-block text-sm uppercase">
                                        <img v-if="voucher.status === 'approved'" src="" alt="Signature"
                                            class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none" />
                                        <div class="border-b border-black px-2 whitespace-nowrap">{{
                                            directorAccounting.full_name.toUpperCase() }}</div>
                                        <div class="text-xs text-center">{{ directorAccounting.position }}</div>
                                    </div>

                                    <div v-else class="text-xs text-gray-500">
                                        No Accounting Director assigned.
                                    </div>
                                </div>
                                <div class="font-bold uppercase">{{ voucher.status }}: </div>
                            </td>
                            <td class="w-1/2 align-top">
                                I hereby certify to have received from the ARELLANO LAW FOUNDATION the sum of
                                <strong>{{ amountToWords(Number(voucher.check_amount)) }} </strong>
                                (₱{{ formatCurrency(voucher.check_amount) }}) as payment for the account specified
                                above.
                            </td>
                        </tr>
                        <tr>
                            <td class="w-1/2">
                                <div class="my-8"></div>
                                <div class="text-right w-1/2">
                                    <div class="my-4"></div>
                                    <div v-if="executiveDirector" class="relative inline-block text-sm uppercase">
                                        <img v-if="voucher.status === 'approved'" src="" alt="Signature"
                                            class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none" />
                                        <div class="border-b border-black px-2 whitespace-nowrap">{{
                                            executiveDirector.full_name.toUpperCase() }}</div>
                                        <div class="text-xs text-center">{{ executiveDirector.position }}</div>
                                    </div>

                                    <div v-else class="text-xs text-gray-500">
                                        No Executive Director assigned.
                                    </div>
                                </div>
                            </td>
                            <td class="w-1/2 align-top">
                                <div class="my-8"></div>
                                <div><strong>{{ "Payee Signature:".toUpperCase() }}</strong></div>
                            </td>
                        </tr>
                    </table>

                    <table class="w-full border-collapse border border-black mt-4">
                        <tr>
                            <td class="border border-black w-1/3 p-2 align-top h-[100px]">
                                <strong>PREPARED BY:</strong><br>
                                <div class="mt-10 w-4/5"></div>
                            </td>
                            <td class="border border-black w-1/3 p-2 align-top h-[100px]">
                                <strong>APPROVED BY:</strong><br>
                                <div class="mt-10 w-4/5"></div>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </AppLayout>
</template>