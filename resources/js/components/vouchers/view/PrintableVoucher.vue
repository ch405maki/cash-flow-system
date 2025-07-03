<script setup lang="ts">
import FormHeader from '@/components/reports/header/formHeder.vue';
import {formatMonth} from '@/lib/utils';
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
    },
    authUser: {
        type: Object,
        required: true
    }
});
</script>

<template>
    <!-- hidden print:block -->
    <div id="printable-voucher" class="hidden print:block mx-auto px-8 max-w-4xl print:max-w-full font-sans">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <FormHeader
                :text="voucher.type.charAt(0).toUpperCase() + voucher.type.slice(1).toLowerCase() + ' Voucher'"
                :bordered="false"
                subtextSize="xl"
                class="text-2xl font-bold tracking-wide" />
        </div>
        
        <!-- Voucher Details -->
        <div class="flex justify-between mb-6">
            <div class="space-y-1">
                <p><span class="font-semibold">Payee:</span> {{ voucher.payee }}</p>
                <p><span class="font-semibold">Check Payable to:</span> {{ voucher.check_payable_to }}</p>
                <p><span class="font-semibold">Check No./Date:</span> {{ formatDate(voucher.check_date) }}</p>
            </div>
            <div class="space-y-1 text-left">
                <p><span class="font-semibold">Voucher No:</span> {{ voucher.voucher_no }}</p>
                <p><span class="font-semibold">Voucher Date:</span> {{ formatDate(voucher.voucher_date) }}</p>
                <p><span class="font-semibold">Amount:</span> {{ formatCurrency(voucher.check_amount) }}</p>
            </div>
        </div>

        <!-- Payment Information -->
        <div class="border-t-4 border-b-4 border-double border-black py-2 my-4">
            <div class="flex">
                <div class="font-medium w-1/2">Payment for {{ formatMonth(voucher.delivery_date) }}</div>
                <div class="font-bold text-left ml-[200px]">
                    {{ formatCurrency(voucher.check_amount) }}
                </div>
            </div>
        </div>

        <!-- Approval Section -->
        <div class="my-6">
            <h3 class="font-bold uppercase text-sm tracking-wider py-6">Recommending Approval:</h3>
            
            <div class="grid grid-cols-2 gap-8">
                <!-- Left Column - Signatures -->
                <div>
                    <div class="flex items-start py-6">
                        <div class="w-2/3">
                            <div v-if="directorAccounting" class="signature-block">
                                <img v-if="voucher.status !== 'draft' || voucher.status !== 'rejected'" 
                                    src="/images/signatures/sample_signature.png" 
                                    alt="Signature"
                                    class="signature-image" />
                                <div class="signature-line">{{ directorAccounting.full_name.toUpperCase() }}</div>
                                <div class="signature-title">{{ directorAccounting.position }}</div>
                            </div>
                            <div v-else class="text-xs text-gray-500 italic">
                                No Accounting Director assigned.
                            </div>
                        </div>
                    </div>
                    <span class="font-bold uppercase">{{ ['released', 'unreleased'].includes(voucher.status) ? 'Approved' : formatStatus(voucher.status) }}:</span>
                </div>
                
                <!-- Right Column - Certification -->
                <div class="py-9">
                    <p class="text-justify leading-relaxed text-sm indent-8">
                    I hereby certify to have received from the ARELLANO LAW FOUNDATION the sum of
                    <strong>{{ amountToWords(Number(voucher.check_amount)) }}</strong>
                    ({{ formatCurrency(voucher.check_amount) }}) as payment for the account specified
                    above.
                    </p>
                </div>
            </div>
            <div class="flex justify-between items-center pt-4">
                <div class="flex">
                    <div class="w-2/3">
                        <div v-if="executiveDirector" class="signature-block">
                            <img v-if="voucher.status !== 'forEOD' && voucher.status !== 'rejected' && voucher.status !== 'draft'"
                                src="/images/signatures/oed_signature.png" 
                                alt="Signature"
                                class="signature-image" />
                            <div class="signature-line">{{ executiveDirector.full_name.toUpperCase() }}</div>
                            <div class="signature-title">{{ executiveDirector.position }}</div>
                        </div>
                        <div v-else class="text-xs text-gray-500 italic">
                            No Executive Director assigned.
                        </div>
                    </div>
                </div>
                <div class="">
                    <div class="signature-line inline-block min-w-[200px]"></div>
                    <div class="text-xs font-bold">Payee Signature</div>
                </div>
            </div>
        </div>

        <!-- Account Charged Section -->
        <h3 class="border-t-4 border-double border-black py-2 mt-4 font-bold uppercase text-sm tracking-wider text-center my-2 tracking-widest">Account Charged</h3>
        
        <table class="w-full border-collapse mb-6">
            <!-- Non-Salary Voucher - General Charges Only -->
            <tr v-for="detail in voucher.details" :key="detail.id" class="border-b border-t border-black">
                <td class="text-left text-sm py- w-[70%]">{{accounts.find(a => a.id === detail.account_id)?.account_title || 'N/A'}}</td>
                <td class="text-left text-sm w-[30%]">{{ formatCurrency(detail.amount) }}</td>
            </tr>
            <tr>
                <td class="text-left py- w-[70%]"></td>
                <td class="text-left text-sm w-[30%]">TOTAL: {{ formatCurrency(voucher.check_amount) }}</td>
            </tr>
        </table>

        <div class="flex justify-between items-center py-16">
        <p class="text-left text-sm w-[70%]"></p>
        <p class="text-left text-sm w-[30%]">{{ formatCurrency(voucher.check_amount) }}</p>
        </div>

        <!-- Prepared/Approved By Section -->
        <table class="w-full border-collapse border border-black mt-16">
            <tr>
                <td class="border border-black p-2 align-top w-1/2">
                    <div class="font-medium text-sm">PREPARED BY:</div>
                </td>
                <td class="border border-black p-2 align-top w-1/2">
                    <div class="font-medium text-sm">AUDITED BY:</div>
                </td>
            </tr>
        </table>
        <p class="text-sm mt-4">
            {{ authUser.name }} - {{ new Date().toLocaleDateString() }}
        </p>
    </div>
</template>

<style scoped>
    .signature-block {
        @apply relative inline-block text-sm;
    }

    .signature-image {
        @apply w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none;
    }

    .signature-line {
        @apply border-t border-black px-2 whitespace-nowrap min-w-[150px];
    }

    .signature-title {
        @apply text-xs text-left px-2;
    }

    @media print {
        
        body {
            background: white;
            color: black;
        }
        
        #printable-voucher {
            margin: 0;
            padding: 0;
        }
    }
</style>