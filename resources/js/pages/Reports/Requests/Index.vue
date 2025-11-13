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
import { formatCurrency, formatDate } from '@/lib/utils';
import { exportToExcel, exportToCSV, type ExportColumn } from '@/lib/exportHelper'
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
  requests: Array<{
    id: number;
    request_no: string;
    request_date: string;
    purpose: string;
    status: string;
    department: {
      id: number;
      name: string;
    };
    user: {
      id: number;
      first_name: string;
      last_name: string;
    };
    details: Array<{
      id: number;
      quantity: number;
      released_quantity: number;
      unit: string;
      item_description: string;
      tagging: string;
    }>;
  }>;
  departments: Array<{
    id: number;
    department_name: string;    
  }>;
  statuses: string[];
}>();

// Filter states
const showAlert = ref(true);
const startDate = ref<string>('');
const endDate = ref<string>('');
const selectedStatus = ref<string>('all');
const selectedDepartment = ref<string>('all');
const searchQuery = ref<string>('');

// Filtered requests
const filteredRequests = computed(() => {
  const searchTerm = searchQuery.value?.toLowerCase() || '';
  
  return props.requests.filter(request => {
    // Date range filter
    const reqDate = new Date(request.request_date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;
    const dateInRange = (!start || reqDate >= start) && (!end || reqDate <= end);
    
    // Status filter
    const statusMatch = selectedStatus.value === 'all' || request.status === selectedStatus.value;
    
    // Department filter (with null check)
    const departmentMatch = selectedDepartment.value === 'all' || 
                        (request.department && request.department.id.toString() === selectedDepartment.value);
    
    // Search filter
    let searchMatch = false;
    if (!searchTerm) {
      searchMatch = true;
    } else {
      // Check request number
      if (request.request_no && request.request_no.toLowerCase().includes(searchTerm)) {
        searchMatch = true;
      }
      // Check purpose
      else if (request.purpose && request.purpose.toLowerCase().includes(searchTerm)) {
        searchMatch = true;
      }
      // Check user name
      else if (request.user && request.user.name && request.user.name.toLowerCase().includes(searchTerm)) {
        searchMatch = true;
      }
      // Check item descriptions
      else if (request.details) {
        searchMatch = request.details.some(detail => 
          detail.item_description && detail.item_description.toLowerCase().includes(searchTerm)
        );
      }
    }
    
    return dateInRange && statusMatch && departmentMatch && searchMatch;
  });
});

// Total items calculation based on filtered data
const totalItems = computed(() =>
  filteredRequests.value.reduce((sum, req) => sum + req.details.length, 0)
);

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Request Summary', href: '/' },
];

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

function goToRequest(id: number) {
  router.visit(`/requests/${id}`);
}

// Export functionality
const exportReport = (format: 'excel' | 'csv') => {
  if (!filteredRequests.value.length) return

  const exportData = filteredRequests.value.map((request: any) => ({
    requestNo: request.request_no,
    date: request.request_date,
    department: request.department?.department_name || 'N/A',
    requestor: `${request.user?.first_name} ${request.user?.last_name}`,
    purpose: request.purpose,
    status: request.status.charAt(0).toUpperCase() + request.status.slice(1),
    itemsCount: request.details.length,
    totalQuantity: request.details.reduce((sum: number, detail: any) => sum + detail.quantity, 0)
  }))

  const columns: ExportColumn[] = [
    { key: 'requestNo', label: 'Request Number' },
    { key: 'date', label: 'Date', format: (value) => formatDate(value) },
    { key: 'department', label: 'Department' },
    { key: 'requestor', label: 'Requestor' },
    { key: 'purpose', label: 'Purpose' },
    { key: 'status', label: 'Status' },
    { key: 'itemsCount', label: 'Items Count' },
    { key: 'totalQuantity', label: 'Total Quantity' },
  ]

  if (format === 'excel') {
    exportToExcel(exportData, columns, {
      filename: 'request_summary_report',
      title: 'Request Summary Report'
    })
  } else {
    exportToCSV(exportData, columns, {
      filename: 'request_summary_report'
    })
  }
}
</script>

<template>
  <Head title="Request Summary" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
          <h1 class="text-2xl font-bold tracking-tight">Request Summary</h1>
          <p class="text-sm text-muted-foreground mt-1">
            Comprehensive overview of all requests with filtering and export capabilities
          </p>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex gap-2">
          <Button variant="outline" size="sm" @click="exportReport('csv')" :disabled="!filteredRequests.length">
            <Download class="mr-2 h-4 w-4" />
            Export CSV
          </Button>
          <Button variant="outline" size="sm" @click="exportReport('excel')" :disabled="!filteredRequests.length">
            <Download class="mr-2 h-4 w-4" />
            Export Excel
          </Button>
          <Button size="sm" @click="printArea" :disabled="!filteredRequests.length">
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
          This is a collection of all Requests with advanced filtering and export options.
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
          <div class="flex flex-col col-span-2 h-full">
            <label class="block text-sm font-medium mb-1">Start Date</label>
            <Input type="date" v-model="startDate" class="h-8" />
          </div>

          <!-- End Date -->
          <div class="flex flex-col col-span-2 h-full">
            <label class="block text-sm font-medium mb-1">End Date</label>
            <Input type="date" v-model="endDate" class="h-8" />
          </div>

          <!-- Status Filter -->
          <div class="flex flex-col col-span-2 h-full">
            <label class="block text-sm font-medium mb-1">Status</label>
            <Select v-model="selectedStatus" class="h-8">
              <SelectTrigger class="h-8">
                <SelectValue placeholder="All Statuses" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Statuses</SelectItem>
                <SelectItem 
                  v-for="status in statuses" 
                  :key="status" 
                  :value="status"
                >
                  {{ status.charAt(0).toUpperCase() + status.slice(1) }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Department Filter -->
          <div class="flex flex-col col-span-3 h-full">
            <label class="block text-sm font-medium mb-1">Department</label>
            <Select v-model="selectedDepartment" class="h-8">
              <SelectTrigger class="h-8">
                <SelectValue placeholder="All Departments" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Departments</SelectItem>
                <SelectItem 
                  v-for="dept in departments" 
                  :key="dept.id" 
                  :value="dept.id.toString()"
                >
                  {{ dept.department_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Search Filter -->
          <div class="flex flex-col col-span-2 h-full">
            <label class="block text-sm font-medium mb-1">Search</label>
            <Input 
              type="text" 
              v-model="searchQuery" 
              placeholder="Search requests..." 
              class="h-8"
            />
          </div>

          <!-- Clear Button -->
          <div class="flex flex-col col-span-1 justify-end h-full">
            <Button 
              variant="destructive" 
              @click="() => {
                startDate = '';
                endDate = '';
                selectedStatus = 'all';
                selectedDepartment = 'all';
                searchQuery = '';
              }"
              class="h-8"
            >
              <Eraser class="h-4 w-4" /> Clear
            </Button>
          </div>
        </div>

        <!-- Results Count -->
        <div class="mt-4 pt-4 border-t">
          <p class="text-sm text-muted-foreground">
            Showing {{ filteredRequests.length }} of {{ props.requests.length }} requests ({{ totalItems }} items)
            <span v-if="startDate || endDate || selectedStatus !== 'all' || selectedDepartment !== 'all' || searchQuery">
              - Filtered results
            </span>
          </p>
        </div>
      </div>

      <!-- Table Section -->
      <div id="print-section">
        <div class="hidden print:block">
          <FormHeader text="Request Summary" :bordered="false" />
        </div>
        
        <div class="relative flex-1 border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min rounded-lg overflow-hidden">
          <Table>
            <TableHeader>
              <TableRow class="bg-muted/50">
                <TableHead class="font-semibold">Request No</TableHead>
                <TableHead class="font-semibold">Date</TableHead>
                <TableHead class="font-semibold">Department</TableHead>
                <TableHead class="font-semibold">Requestor</TableHead>
                <TableHead class="font-semibold">Purpose</TableHead>
                <TableHead class="font-semibold">Status</TableHead>
                <TableHead class="font-semibold">Items</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="request in filteredRequests" 
                :key="request.id"
                class="hover:bg-muted/50 transition-colors"
              >
                <TableCell>
                  <button 
                    class="hover:text-purple-700 hover:underline font-medium" 
                    @click="goToRequest(request.id)"
                  >
                    {{ request.request_no }}
                  </button>
                </TableCell>
                <TableCell>{{ formatDate(request.request_date) }}</TableCell>
                <TableCell>{{ request.department.department_name }}</TableCell>
                <TableCell>{{ request.user.first_name }} {{ request.user.last_name }}</TableCell>
                <TableCell class="max-w-[200px] truncate">{{ request.purpose }}</TableCell>
                <TableCell>
                  <span 
                    :class="{
                      'text-yellow-600 bg-yellow-100 px-2 py-1 rounded-full text-xs': request.status === 'pending',
                      'text-green-600 bg-green-100 px-2 py-1 rounded-full text-xs': request.status === 'approved' || request.status === 'completed',
                      'text-red-600 bg-red-100 px-2 py-1 rounded-full text-xs': request.status === 'rejected'
                    }"
                    class="font-medium"
                  >
                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                  </span>
                </TableCell>
                <TableCell class="text-center font-medium">
                  {{ request.details.length }}
                </TableCell>
              </TableRow>

              <!-- Empty State -->
              <TableRow v-if="filteredRequests.length === 0">
                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                  <div class="flex flex-col items-center justify-center">
                    <p class="text-lg font-medium mb-2">No requests found</p>
                    <p class="text-sm">
                      <span v-if="startDate || endDate || selectedStatus !== 'all' || selectedDepartment !== 'all' || searchQuery">
                        No requests match your current filters
                      </span>
                      <span v-else>
                        No requests found in the system
                      </span>
                    </p>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>

        <!-- Print Summary -->
        <div class="hidden print:block mt-4 text-sm text-muted-foreground">
          <p>Generated on: {{ new Date().toLocaleDateString() }}</p>
          <p>Total requests: {{ filteredRequests.length }} | Total items: {{ totalItems }}</p>
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
  @page {
    margin: 1cm;
  }
  
  .print\:hidden {
    display: none !important;
  }
  
  .print\:block {
    display: block !important;
  }
}
</style>