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
import { formatDate, formatCurrency, amountToWords } from '@/lib/utils';

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

const voucherRef = ref({ ...props.voucher })
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

function formatStatus(status: string): string {
    switch (status) {
        case 'forEOD':
            return 'For EOD Approval';
        case 'forCheck':
            return '';
        default:
            return status; 
    }
}

const handleCheckUpdated = (updatedData) => {
  Object.assign(voucherRef.value, updatedData);
  toast.success('Check details updated successfully');
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
                :voucher="voucherRef" 
                :auth-user="authUser"
                @print="printArea"
                @check-updated="handleCheckUpdated"  
            />

            <VoucherInfoTables 
                :voucher="voucherRef"
                :format-date="formatDate"
                :format-currency="formatCurrency"
                :format-status="formatStatus"
            />
            
            <VoucherDatesTable 
                :voucher="voucher"
                :format-date="formatDate"
            /> 
            
            <VoucherAccountDetails 
                :voucher="voucher"
                :accounts="accounts"
                :format-currency="formatCurrency"
            />
            
            <PrintableVoucher 
                id="print-section"
                :voucher="voucherRef"
                :auth-user="authUser"
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