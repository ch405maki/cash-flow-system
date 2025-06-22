<script setup lang="ts">
import FormHeader from '@/components/reports/header/formHeder.vue';

defineProps({
    voucher: {
        type: Object,
        required: true
    },
    accounts: {
        type: Array,
        required: true
    },
    directorAccounting: {
        type: Object,
        required: false
    },
    executiveDirector: {
        type: Object,
        required: false
    },
    formatDate: {
        type: Function,
        required: true
    },
    formatCurrency: {
        type: Function,
        required: true
    },
    amountToWords: {
        type: Function,
        required: true
    },
    formatStatus: {
        type: Function,
        required: true
    }
});
</script>

<template>
    <div id="printable-voucher" class="hidden print:block mx-4 print:text-[9pt]">
        <div class="text-center font-bold mb-5">
            <FormHeader
                :text="voucher.type.charAt(0).toUpperCase() + voucher.type.slice(1).toLowerCase() + ' Voucher'"
                :bordered="false" /><br>
        </div>

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
                            <img v-if="voucher.status === 'forCheck'" src="" alt="Signature"
                                class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none" />
                            <div class="border-b border-black px-2 whitespace-nowrap">{{
                                directorAccounting.full_name.toUpperCase() }}</div>
                            <div class="text-xs text-center">{{ directorAccounting.position }}</div>
                        </div>

                        <div v-else class="text-xs text-gray-500">
                            No Accounting Director assigned.
                        </div>
                    </div>
                    <div class="font-bold uppercase">{{ formatStatus(voucher.status) }}: </div>
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
                            <img v-if="voucher.status === 'forCheck'" src="" alt="Signature"
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
</template>