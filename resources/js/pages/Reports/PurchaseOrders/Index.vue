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
import { Label } from '@/components/ui/label'
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
  purchaseOrders: Array<{
    id: number;
    po_no: string;
    date: string;
    payee: string;
    amount: number;
    department: { department_name: string };
  }>;
}>();

// Filter states
const showAlert = ref(true)
const startDate = ref<string>('');
const endDate = ref<string>('');
const selectedDepartment = ref<string>('all');

// Get unique departments for filter dropdown
const departments = computed(() => {
  const uniqueDepartments = new Set<string>();
  props.purchaseOrders.forEach(po => {
    uniqueDepartments.add(po.department.department_name);
  });
  return Array.from(uniqueDepartments).sort();
});

// Filtered purchase orders
const filteredPurchaseOrders = computed(() => {
  return props.purchaseOrders.filter(po => {
    // Filter by date range
    const poDate = new Date(po.date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;
    
    const dateInRange = 
      (!start || poDate >= start) && 
      (!end || poDate <= end);
    
    // Filter by department
    const departmentMatch = 
      selectedDepartment.value === 'all' || 
      po.department.department_name === selectedDepartment.value;
    
    return dateInRange && departmentMatch;
  });
});

// Total amount calculation based on filtered data
const totalAmount = computed(() =>
  filteredPurchaseOrders.value.reduce((sum, po) => sum + Number(po.amount || 0), 0)
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
  { title: 'Purchase Order Summary', href: '/' },
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

function goToPO(id: number) {
  router.visit(`/purchase-orders/${id}`)
}
</script>

<template>
  <Head title="Purchase Order Summary" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <Alert v-if="showAlert" variant="success" class="relative pr-10">
        <Rocket class="h-4 w-4 text-green-500" />
        <AlertTitle>Notice</AlertTitle>
        <AlertDescription>
          This is a collection of approved purchase orders.
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
        <h1 class="text-xl font-bold">Purchase Order Summary</h1>
        <Button size="sm" @click="printArea"> <Printer />Print</Button>
    </div>
    <!-- Filters Section -->
    <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
        <!-- Start Date -->
        <div class="flex flex-col col-span-3 h-full">
            <Label class="block text-sm font-medium mb-1">Start Date</Label>
            <Input type="date" v-model="startDate" class="h-8" />
        </div>

        <!-- End Date -->
        <div class="flex flex-col col-span-3 h-full">
            <Label class="block text-sm font-medium mb-1">End Date</Label>
            <Input type="date" v-model="endDate" class="h-8" />
        </div>

        <!-- Department Dropdown (Wider) -->
        <div class="flex flex-col col-span-5 h-full">
            <Label class="block text-sm font-medium mb-1">Department</Label>
            <Select v-model="selectedDepartment" class="w-[250px] h-8">
                <SelectTrigger class="h-8 w-full">
                    <SelectValue placeholder="All Departments" />
                </SelectTrigger>
                <SelectContent class="w-[250px]">
                    <SelectItem value="all">All Departments</SelectItem>
                    <SelectItem 
                        v-for="dept in departments" 
                        :key="dept" 
                        :value="dept"
                    >
                        {{ dept }}
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
                    selectedDepartment = 'all';
                }"
                class="h-8"
            >
                <Eraser /> Clear
            </Button>
        </div>
    </div>

    <!-- Results Count -->
    <div class="text-sm text-muted-foreground">
    Showing {{ filteredPurchaseOrders.length }} of {{ props.purchaseOrders.length }} records
    </div>

      <!-- Table Section -->
    <div id="print-section">
    <div class="hidden print:block">
        <FormHeader text="Purchase Order Summary" :bordered="false" />
    </div>
    <Table>
      <TableBody>
        <TableRow>
          <TableCell>PO No</TableCell>
          <TableCell>Date</TableCell>
          <TableCell>Department</TableCell>
          <TableCell>Payee</TableCell>
          <TableCell class="text-right">Amount (₱)</TableCell>
        </TableRow>
        <TableRow v-for="po in filteredPurchaseOrders" :key="po.id">
          <TableCell><button class="hover:text-purple-700 hover:underline" @click="goToPO(po.id)">{{ po.po_no }}</button></TableCell>
          <TableCell>{{ formatDate(po.date) }}</TableCell>
          <TableCell>{{ po.department.department_name }}</TableCell>
          <TableCell>{{ po.payee }}</TableCell>
          <TableCell class="text-right">₱{{ Number(po.amount || 0).toFixed(2) }}</TableCell>
        </TableRow>

        <!-- Total Row -->
        <TableRow>
          <TableCell colspan="4" class="text-right font-semibold">Total:</TableCell>
          <TableCell class="text-right font-bold text-foreground">
            <div v-if="totalAmount === 0" class="text-warning">Warning: Total is zero</div>
            ₱{{ totalAmount.toFixed(2) }}
          </TableCell>
        </TableRow>
      </TableBody>
    </Table>
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