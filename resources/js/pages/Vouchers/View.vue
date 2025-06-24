<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { useToast } from 'vue-toastification'
import VoucherHeader from '@/components/vouchers/view/VoucherHeader.vue';
import VoucherInfoTables from '@/components/vouchers/view/VoucherInfoTables.vue';
import VoucherDatesTable from '@/components/vouchers/view/VoucherDatesTable.vue';
import VoucherAccountDetails from '@/components/vouchers/view/VoucherAccountDetails.vue';
import PrintableVoucher from '@/components/vouchers/view/PrintableVoucher.vue';

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
    {
        title: 'Vouchers',
        href: authUser.role == 'executive_director'
            ? '/voucher-approval'
            : authUser.role == 'accounting' && authUser.acces_id == '3'
                ? '/approved-voucher'
                : '/vouchers'
    },
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

function formatStatus(status: string): string {
    switch (status) {
        case 'forEOD':
            return 'For EOD Approval';
        case 'forCheck':
            return 'For Check Releasing';
        default:
            return status; 
    }
}

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
</script>

<template>
    <Head :title="`View Voucher - ${voucher.voucher_no}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <VoucherHeader 
                :voucher="voucher" 
                :auth-user="authUser"
                @print="printArea"
            />
            
            <VoucherInfoTables 
                :voucher="voucher"
                :format-date="formatDate"
                :format-currency="formatCurrency"
                :format-status="formatStatus"
            />
            
                <!-- <VoucherDatesTable 
                    :voucher="voucher"
                    :format-date="formatDate"
                /> -->
            
            <VoucherAccountDetails 
                :voucher="voucher"
                :accounts="accounts"
                :format-currency="formatCurrency"
            />
            
            <PrintableVoucher 
                id="print-section"
                :voucher="voucher"
                :accounts="accounts"
                :director-accounting="directorAccounting"
                :executive-director="executiveDirector"
                :format-date="formatDate"
                :format-currency="formatCurrency"
                :amount-to-words="amountToWords"
                :format-status="formatStatus"
            />
        </div>
    </AppLayout>
</template>