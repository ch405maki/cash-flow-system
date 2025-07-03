<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Button } from '@/components/ui/button'
import { Eraser, Printer, Rocket, X   } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { formatCurrency } from '@/lib/utils';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow
} from '@/components/ui/table';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';

const props = defineProps<{
  vouchers: Array<{
    id: number;
    voucher_no: string;
    voucher_date: string;
    payee: string;
    check_amount: number;
    type: string;
  }>;
}>();

// Filter states
const showAlert = ref(true)
const startDate = ref<string>('');
const endDate = ref<string>('');
const selectedType = ref<string>('all');

// Get unique voucher type for filter dropdown
const voucherType = computed(() => {
  const uniqueTypes = new Set<string>();
  props.vouchers.forEach(v => {
    uniqueTypes.add(v.type);
  });
  return Array.from(uniqueTypes).sort();
});

// Filtered purchase orders
const filteredVouchers = computed(() => {
  return props.vouchers.filter(v => {
    // Filter by date range
    const vDate = new Date(v.voucher_date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;
    
    const dateInRange = 
      (!start || vDate >= start) && 
      (!end || vDate <= end);
    
    // Filter by department
    const typeMatch = 
      selectedType.value === 'all' || 
      v.type === selectedType.value;
    
    return dateInRange && typeMatch;
  });
});

// Total amount calculation based on filtered data
const totalAmount = computed(() =>
  filteredVouchers.value.reduce((sum, v) => sum + Number(v.check_amount || 0), 0)
);

function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Voucher Summary', href: '/' },
];

const printArea = () =>{
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

function goToVoucher(id: number) {
  router.visit(`/vouchers/${id}`)
}
</script>

<template>
  <Head title="Voucher Summary" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <Alert v-if="showAlert" variant="success" class="relative pr-10">
        <Rocket class="h-4 w-4 text-green-500" />
        <AlertTitle>Notice</AlertTitle>
        <AlertDescription>
          This is a collection of all Vouchers.
        </AlertDescription>

        <!-- Dismiss Button -->
        <button
          class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
          @click="showAlert = false"
          aria-label="Dismiss"
        >
          <X class="h-4 w-4 text-purple-700" />
        </button>
      </Alert>

    <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Voucher Summary</h1>
        <Button size="sm" @click="printArea"> <Printer />Print</Button>
    </div>
    <!-- Filters Section -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <!-- Start Date -->
        <div class="flex flex-col col-span-3 h-full">
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <Input type="date" v-model="startDate" class="h-8" />
        </div>

        <!-- End Date -->
        <div class="flex flex-col col-span-3 h-full">
            <label class="block text-sm font-medium mb-1">End Date</label>
            <Input type="date" v-model="endDate" class="h-8" />
        </div>
        

        <!-- Voucher Type Dropdown (Wider) -->
        <div class="flex flex-col col-span-5 h-full">
            <label class="block text-sm font-medium mb-1">Voucher Type</label>
            <Select v-model="selectedType" class="w-[250px] h-8">
                <SelectTrigger class="h-8 w-full">
                    <SelectValue placeholder="All Voucher Type" />
                </SelectTrigger>
                <SelectContent class="w-[250px]">
                    <SelectItem value="all">All Voucher Type</SelectItem>
                    <SelectItem 
                        v-for="type in voucherType" 
                        :key="type" 
                        :value="type"
                    >
                        {{ type }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <!-- Clear Button -->
        <div class="flex flex-col col-span-1 justify-end h-full">
            <Button 
                variant="destructive" 
                @click="() => {
                    startDate = '';
                    endDate = '';
                    selectedType = 'all';
                }"
                class="h-8"
            >
                <Eraser /> Clear
            </Button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="text-sm text-muted-foreground">
    Showing {{ filteredVouchers.length }} of {{ props.vouchers.length }} records
    </div>

      <!-- Table Section -->
    <div id="print-section">
    <div class="hidden print:block">
        <FormHeader text="Voucher Summary" :bordered="false" />
    </div>
    <div class="relative flex-1 border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
      <Table>
          <TableBody>
            <TableRow>
              <TableCell>Voucher No</TableCell>
              <TableCell>Voucher Date</TableCell>
              <TableCell>Voucher Type</TableCell>
              <TableCell>Payee</TableCell>
              <TableCell class="text-right">Amount (₱)</TableCell>
            </TableRow>
            <TableRow v-for="v in filteredVouchers" :key="v.id">
              <TableCell><button class="hover:text-purple-700 hover:underline" @click="goToVoucher(v.id)">{{ v.voucher_no }}</button></TableCell>
              <TableCell>{{ formatDate(v.voucher_date) }}</TableCell>
              <TableCell>{{ v.type }}</TableCell>
              <TableCell>{{ v.payee }}</TableCell>
              <TableCell class="text-right">{{ formatCurrency(v.check_amount) }}</TableCell>
            </TableRow>

            <!-- Total Row -->
            <TableRow>
              <TableCell colspan="4" class="text-right font-semibold">Total:</TableCell>
              <TableCell class="text-right font-bold text-foreground">
                <div v-if="totalAmount === 0" class="text-warning">Warning: Total is zero</div>
                {{ formatCurrency(totalAmount) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>
    </div>
  </AppLayout>
</template>

<style scoped>
table tr {
  padding: 0 !important;
  line-height: 1.25 !important;
}
table td {
  padding: 0.45rem !important;
}
</style>