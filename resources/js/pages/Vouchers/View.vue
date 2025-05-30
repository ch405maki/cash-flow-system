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
import { type BreadcrumbItem } from '@/types';
import { ArrowLeft, Printer } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'

const { props } = usePage();
const accounts = props.accounts || [];
const voucher = props.voucher;
const roles = props.roles || {};
const isSalary = props.isSalary || true;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Vouchers', href: '/vouchers' },
    { title: `View Voucher: ${voucher.voucher_no}`, href: `/vouchers/${voucher.id}` },
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

const printStyles = `
  body {
    font-family: sans-serif;
    margin: 20px;
    font-size: 0.9em;
    -webkit-print-color-adjust: exact;
    color-adjust: exact;
  }
  table {
    width: 100%;
    border-collapse: collapse;
  }
  td {
    padding: 5px;
    vertical-align: top;
  }
  .header {
    text-align: center;
    font-weight: bold;
    margin-bottom: 20px;
  }
  .section-title {
    font-weight: bold;
    margin-top: 15px;
    margin-bottom: 5px;
  }
  .line-item {
    border-bottom: 1px solid black;
    padding-bottom: 3px;
    margin-bottom: 3px;
  }
  .signature-line {
    border-bottom: 1px solid black;
    width: 70%;
    margin-top: 50px;
  }
  .footer-notes {
    margin-top: 50px;
    font-size: 0.8em;
  }
  .align-right {
    text-align: right;
  }
  .total-box {
    border: 1px solid black;
    padding: 5px;
    width: 150px;
    text-align: right;
    font-weight: bold;
  }
`;

const printVoucher = () => {
    const printContent = document.getElementById('printable-voucher')?.innerHTML;
    if (!printContent) {
        console.error('Print section not found');
        return;
    }

    const printWindow = window.open('', '_blank');
    if (printWindow) {
        printWindow.document.write(`
      <!DOCTYPE html>
      <html>
        <head>
          <title>Voucher ${voucher.voucher_no}</title>
          <style>${printStyles}</style>
        </head>
        <body>
          ${printContent}
          <script>
            setTimeout(() => {
              window.print();
              window.close();
            }, 100);
          <\/script>
        </body>
      </html>
    `);
        printWindow.document.close();
    }
};
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
                    <Button variant="outline" @click="printVoucher" class="flex items-center gap-2">
                        <Printer class="h-4 w-4" />
                        Print
                    </Button>
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
                                <span class="py-1 px-3 rounded-full font-bold capitalize inline-block min-w-[100px]"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': voucher.status === 'pending',
                                        'bg-green-100 text-green-800': voucher.status === 'paid',
                                        'bg-red-100 text-red-800': voucher.status === 'rejected',
                                    }">
                                    {{ voucher.status }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Dates Table -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
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
            <div v-if="voucher.type !== 'cash'" class="space-y-2">
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
            <div id="printable-voucher" class="hidden">
                <div class="header">
                    ARELLANO LAW FOUNDATION, INC. <br>
                    {{ voucher.type.toUpperCase() }} Voucher
                </div>

                <table>
                    <tr>
                        <td width="70%">
                            Voucher No.: {{ voucher.voucher_no }} <br>
                            Voucher Date: {{ formatDate(voucher.voucher_date) }}
                        </td>
                    </tr>
                </table>

                <br>

                <table>
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
                <div class="line-item"></div>
                <div class="line-item"></div>
                <div style="display: flex; justify-content: space-between;">
                    <div>Payment for {{ formatDate(voucher.payment_date) }}</div>
                    <div>₱{{ formatCurrency(voucher.check_amount) }}</div>
                </div>

                <div class="line-item"></div>
                <div class="line-item"></div>
                <!-- ACCOUNT CHARGED section-->
                <div class="section-title">ACCOUNT CHARGED</div>
                <table>
                    <template v-if="isSalary">
                        <!-- Salary Voucher - Detailed Breakdown -->
                        <thead>
                            <tr>
                                <th style="text-align: left; width: 70%;">Account Title</th>
                                <th style="text-align: right; width: 30%;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="detail in voucher.details" :key="detail.id">
                                <td style="text-align: left;">
                                    {{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}
                                </td>
                                <td style="text-align: right;">₱{{ formatCurrency(detail.amount) }}</td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td style="text-align: right;"><strong>TOTAL AMOUNT:</strong></td>
                                <td
                                    style="text-align: right; border: 1px solid black; padding: 5px; font-weight: bold;">
                                    ₱{{formatCurrency(voucher.details.reduce((sum, detail) => sum +
                                    Number(detail.amount), 0)) }}
                                </td>
                            </tr>
                        </tfoot>
                    </template>
                    <template v-else>
                        <!-- Non-Salary Voucher - General Charges -->
                        <tr>
                            <td style="text-align: left; width: 70%;">GENERAL CHARGES</td>
                            <td style="text-align: right; width: 30%;">₱{{ formatCurrency(voucher.check_amount) }}</td>
                        </tr>
                        <tr v-for="i in 5" :key="i">
                            <td><br></td>
                            <td></td>
                        </tr>
                        <tr>
                            <td style="text-align: right;"><strong>TOTAL:</strong></td>
                            <td style="text-align: right; border: 1px solid black; padding: 5px; font-weight: bold;">
                                ₱{{ formatCurrency(voucher.check_amount) }}
                            </td>
                        </tr>
                    </template>
                </table>

                <br><br>
                <div class="line-item"></div>
                <div class="line-item"></div>
                <div class="section-title">RECOMMENDING APPROVAL:</div>
                <br>

                <div>
                    {{ roles.approved_by?.first_name }}
                    <template v-if="roles.approved_by?.middle_name">
                        {{ roles.approved_by.middle_name.charAt(0).toUpperCase() }}.
                        {{ roles.approved_by.last_name }}
                    </template>
                </div>
                <div><strong>Director, Accounting</strong></div>

                <br>

                <table>
                    <tr>
                        <td width="50%">
                            <div class="section-title" style="text-transform: uppercase;">{{ voucher.status }}: </div>
                            <br><br>
                            <div>
                                {{ roles.exec_director?.first_name }}
                                <template v-if="roles.exec_director?.middle_name">
                                    {{ roles.exec_director.middle_name.charAt(0).toUpperCase() }}.
                                    {{ roles.exec_director.last_name }}
                                </template>
                            </div>
                            <div><strong>Executive Director</strong></div>
                        </td>
                        <td width="50%" style="vertical-align: top;">
                            I hereby certify to have received from the ARELLANO LAW FOUNDATION the sum of
                            <strong>{{ amountToWords(Number(voucher.check_amount)) }} </strong>
                            (₱{{ formatCurrency(voucher.check_amount) }}) as payment for the account specified above.
                            <br><br><br><br><br>
                            <div><strong>{{ "Payee Signature:".toUpperCase() }}</strong></div>
                        </td>
                    </tr>
                </table>

                <table style="border-collapse: collapse; width: 100%;">
                    <tr>
                        <td
                            style="border: 1px solid black; width: 33%; padding: 8px; vertical-align: top; height: 100px;">
                            <strong>PREPARED BY:</strong><br>
                            <div style="margin-top: 40px; width: 80%;"></div>
                        </td>
                        <td
                            style="border: 1px solid black; width: 33%; padding: 8px; vertical-align: top; height: 100px;">
                            <strong>APPROVED BY:</strong><br>
                            <div style="margin-top: 40px; width: 80%;"></div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </AppLayout>
</template>