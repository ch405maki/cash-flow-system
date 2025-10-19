<script setup lang="ts">
import { Download } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { useToast } from 'vue-toastification';
import { ref } from 'vue';
import {
    Table,
    TableBody,
    TableCaption,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from "@/components/ui/table"

const toast = useToast();
const props = defineProps({
    voucher: {
        type: Object,
        required: true
    },
    formatDate: {
        type: Function,
        required: true
    },
    formatCurrency: {
        type: Function,
        required: true
    },
    formatStatus: {
        type: Function,
        required: true
    }
});

const isDownloading = ref(false);

const downloadReceipt = () => {
  const link = document.createElement('a');
  link.href = route('vouchers.download.receipt', { voucher: props.voucher.id });
  link.click();
};
</script>

<template>
    <!-- First Table -->
    <div class="space-y-2">
        <div class="flex items-center justify-between">
            <p class="font-medium">Voucher Details</p>
            <div v-if="voucher.receipt">
                <Button 
                    size="sm"
                    v-if="voucher.receipt" 
                    variant="success" 
                    @click="downloadReceipt"
                    :disabled="isDownloading"
                >
                    <Download class="h-4 w-4 mr-2" />
                    {{ isDownloading ? 'Downloading...' : 'Download Receipt' }}
                </Button>
            </div>
        </div>
        <Table>
        <TableHeader>
            <TableRow class="border-b">
            <TableHead colspan="4" class="p-2 text-left text-muted-foreground">
                VOUCHER TYPE:
                <span class="uppercase font-normal">{{ voucher.type }}</span> &nbsp;&nbsp;&nbsp;| &nbsp;&nbsp;&nbsp;
                <span class="uppercase font-bold tracking-wide"
                    :class="{
                        'text-green-600': voucher.status === 'completed',
                        'text-red-600': voucher.status === 'unreleased',
                        'text-purple-600': voucher.status === 'released',
                        'text-yellow-500': voucher.status === 'forCheck',
                        'text-blue-500': voucher.status === 'forEOD',
                        'text-gray-500': voucher.status === 'draft'
                    }">
                <span class="text-muted-foreground" v-if="voucher.status != 'forCheck'">STATUS: </span>{{ formatStatus(voucher.status) }}
                </span>
                <span v-if="voucher.status != 'forCheck'">
                &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
                </span>
                VOUCHER DATE:
                <span class="uppercase font-normal">{{ formatDate(voucher.voucher_date) }}</span>
            </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow class="border-b">
            <TableCell class="p-2 font-medium text-muted-foreground border-r w-48">PAYEE:</TableCell>
            <TableCell class="p-2 uppercase border-r">{{ voucher.payee }}</TableCell>
            <TableCell class="p-2 font-medium text-muted-foreground border-r w-40">CHECK DATE:</TableCell>
            <TableCell class="p-2">{{ voucher.check_date ? formatDate(voucher.check_date) : '' }}</TableCell>
            </TableRow>
            <TableRow class="border-b">
            <TableCell class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</TableCell>
            <TableCell class="p-2 uppercase border-r">{{ voucher.check_payable_to }}</TableCell>
            <TableCell class="p-2 font-medium text-muted-foreground border-r">CHECK NUMBER:</TableCell>
            <TableCell class="p-2">
                <span>{{ voucher.check_no || '' }}</span>
            </TableCell>
            </TableRow>
            <TableRow class="border-b">
            <TableCell class="p-2 font-medium text-muted-foreground border-r">PURPOSE:</TableCell>
            <TableCell class="p-2 uppercase border-r">{{ voucher.purpose }}</TableCell>
            <TableCell class="p-2 font-medium text-muted-foreground border-r">CHECK AMOUNT:</TableCell>
            <TableCell class="p-2">{{ formatCurrency(voucher.check_amount) }}</TableCell>
            </TableRow>
        </TableBody>
        </Table>
    </div>
</template>