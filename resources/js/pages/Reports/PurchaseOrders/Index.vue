<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Button } from '@/components/ui/button'
import { Eraser, Printer, Rocket, X, Download } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Label } from '@/components/ui/label'
import PageHeader from '@/components/PageHeader.vue';
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
import { exportToExcel, quickExport } from '@/lib/poExport';

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

// Statistics
const stats = computed(() => {
  const total = filteredPurchaseOrders.value.length
  const averageAmount = total > 0 ? totalAmount.value / total : 0
  
  return {
    total,
    totalAmount: totalAmount.value,
    averageAmount
  }
})

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

// Export functionality
const exportReport = () => {
  if (!filteredPurchaseOrders.value.length) return

  const exportData = filteredPurchaseOrders.value.map((po: any) => ({
    poNumber: po.po_no,
    date: po.date,
    department: po.department.department_name,
    payee: po.payee,
    amount: Number(po.amount || 0) // Make sure this is a number, not string
  }))

  const columns = [
    { key: 'poNumber', label: 'PO Number' },
    { key: 'date', label: 'Date', format: (value) => formatDate(value) },
    { key: 'department', label: 'Department' },
    { key: 'payee', label: 'Payee' },
    { 
      key: 'amount', 
      label: 'Amount', 
      format: (value) => `₱${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`,
      total: true // ← THIS IS MISSING!
    },
  ]

  exportToExcel(exportData, columns, {
    filename: 'purchase_order_summary',
    title: 'Purchase Order Summary Report',
    totalLabel: `Total (${filteredPurchaseOrders.value.length} POs)`
  })
}
</script>

<template>
  <Head title="Purchase Order Summary" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <PageHeader 
          title="Purchase Order Summary" 
          subtitle="Comprehensive overview of all purchase orders with financial tracking"
        />
        
        <!-- Action Buttons -->
        <div class="flex gap-2">
          <Button variant="outline" size="sm" @click="exportReport" :disabled="!filteredPurchaseOrders.length">
            <Download class="mr-2 h-4 w-4" />
            Export Excel
          </Button>
          <Button size="sm" @click="printArea" :disabled="!filteredPurchaseOrders.length">
            <Printer class="mr-2 h-4 w-4" />
            Print
          </Button>
        </div>
      </div>

      <!-- Alert Section -->
      <Alert v-if="showAlert" variant="success" class="relative pr-10">
        <Rocket class="h-4 w-4 text-green-500" />
        <AlertTitle>Notice</AlertTitle>
        <AlertDescription>
          This is a collection of approved purchase orders with financial tracking and export capabilities.
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

      <!-- Filters Section -->
      <div class="bg-card border rounded-lg p-4">
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

          <!-- Department Dropdown -->
          <div class="flex flex-col col-span-5 h-full">
            <Label class="block text-sm font-medium mb-1">Department</Label>
            <Select v-model="selectedDepartment">
              <SelectTrigger class="h-8">
                <SelectValue placeholder="All Departments" />
              </SelectTrigger>
              <SelectContent>
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
              <Eraser class="h-4 w-4 mr-1" /> Clear
            </Button>
          </div>
        </div>

        <!-- Results Count -->
        <div class="mt-4 pt-4 border-t">
          <p class="text-sm text-muted-foreground">
            Showing {{ filteredPurchaseOrders.length }} of {{ props.purchaseOrders.length }} purchase orders
            <span v-if="startDate || endDate || selectedDepartment !== 'all'">
              - Filtered results
            </span>
          </p>
        </div>
      </div>

      <!-- Table Section -->
      <div  id="print-section" class="print-section">
        <div class="hidden print:block print-header">
          <FormHeader text="Purchase Order Summary" :bordered="false" />
        </div>
        
        <div class="overflow-hidden print-table-container">
          <Table class="print-table">
            <TableHeader>
              <TableRow class="bg-muted/50">
                <TableHead class="font-semibold">PO Number</TableHead>
                <TableHead class="font-semibold">Date</TableHead>
                <TableHead class="font-semibold">Department</TableHead>
                <TableHead class="font-semibold">Payee</TableHead>
                <TableHead class="font-semibold text-right">Amount (₱)</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="po in filteredPurchaseOrders" 
                :key="po.id"
                class="hover:bg-muted/50 transition-colors"
              >
                <TableCell>
                  <button 
                    class="hover:text-purple-700 hover:underline font-medium" 
                    @click="goToPO(po.id)"
                  >
                    {{ po.po_no }}
                  </button>
                </TableCell>
                <TableCell>{{ formatDate(po.date) }}</TableCell>
                <TableCell>{{ po.department.department_name }}</TableCell>
                <TableCell class="max-w-[200px] truncate">{{ po.payee }}</TableCell>
                <TableCell class="text-right font-medium">
                  ₱{{ Number(po.amount || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
                </TableCell>
              </TableRow>

              <!-- Empty State -->
              <TableRow v-if="filteredPurchaseOrders.length === 0">
                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                  <div class="flex flex-col items-center justify-center">
                    <p class="text-lg font-medium mb-2">No purchase orders found</p>
                    <p class="text-sm">
                      <span v-if="startDate || endDate || selectedDepartment !== 'all'">
                        No purchase orders match your current filters
                      </span>
                      <span v-else>
                        No purchase orders found in the system
                      </span>
                    </p>
                  </div>
                </TableCell>
              </TableRow>

              <!-- Total Row -->
              <TableRow v-if="filteredPurchaseOrders.length > 0" class="bg-muted/30">
                <TableCell colspan="4" class="text-right font-semibold">Total Amount:</TableCell>
                <TableCell class="text-right font-bold text-foreground">
                  <div v-if="totalAmount === 0" class="text-yellow-600 text-sm">Total is zero</div>
                  <div v-else>₱{{ totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Print Summary -->
        <div class="hidden print:block mt-4 text-sm text-muted-foreground">
          <p>Generated on: {{ new Date().toLocaleDateString() }}</p>
          <p>Total purchase orders: {{ filteredPurchaseOrders.length }}</p>
          <p>Total amount: ₱{{ totalAmount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}</p>
          <p v-if="startDate || endDate">
            Date range: 
            <span v-if="startDate">{{ formatDate(startDate) }}</span>
            <span v-if="startDate && endDate"> to </span>
            <span v-if="endDate">{{ formatDate(endDate) }}</span>
          </p>
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

@media print {
  .print-section {
    font-size: 9px !important;
  }
  
  .print-header {
    margin-bottom: 0.5rem !important;
  }
  
  .print-title {
    font-size: 11px !important;
  }
  
  .print-table-container {
    margin: 0 !important;
    padding: 0 !important;
  }
  
  .print-table {
    font-size: 8px !important;
  }
  
  .print-summary {
    font-size: 8px !important;
    margin-top: 0.3rem !important;
  }
}
</style>