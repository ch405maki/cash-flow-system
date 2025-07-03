<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { computed, ref } from 'vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Button } from '@/components/ui/button'
import { Eraser, Printer, Rocket, X } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { formatCurrency, formatDate } from '@/lib/utils';
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
      name: string;
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
    name: string;
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
  return props.requests.filter(request => {
    // Filter by date range
    const reqDate = new Date(request.request_date);
    const start = startDate.value ? new Date(startDate.value) : null;
    const end = endDate.value ? new Date(endDate.value) : null;
    
    const dateInRange = 
      (!start || reqDate >= start) && 
      (!end || reqDate <= end);
    
    // Filter by status
    const statusMatch = 
      selectedStatus.value === 'all' || 
      request.status === selectedStatus.value;
    
    // Filter by department
    const departmentMatch = 
      selectedDepartment.value === 'all' || 
      request.department.id.toString() === selectedDepartment.value;
    
    // Filter by search query
    const searchMatch = 
      !searchQuery.value ||
      request.request_no.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      request.purpose.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      request.user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      request.details.some(detail => 
        detail.item_description.toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    
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
</script>

<template>
  <Head title="Request Summary" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <Alert v-if="showAlert" variant="success" class="relative pr-10">
        <Rocket class="h-4 w-4 text-green-500" />
        <AlertTitle>Notice</AlertTitle>
        <AlertDescription>
          This is a collection of all Requests.
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
        <h1 class="text-xl font-bold">Request Summary</h1>
        <Button size="sm" @click="printArea"> <Printer />Print</Button>
      </div>

      <!-- Filters Section -->
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
                {{ dept.name }}
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
            <Eraser /> Clear
          </Button>
        </div>
      </div>

      <!-- Results Count -->
      <div class="text-sm text-muted-foreground">
        Showing {{ filteredRequests.length }} of {{ props.requests.length }} requests ({{ totalItems }} items)
      </div>

      <!-- Table Section -->
      <div id="print-section">
        <div class="hidden print:block">
          <FormHeader text="Request Summary" :bordered="false" />
        </div>
        <div class="relative flex-1 border border-sidebar-border/70 dark:border-sidebar-border md:min-h-min">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Request No</TableHead>
                <TableHead>Date</TableHead>
                <TableHead>Department</TableHead>
                <TableHead>Requestor</TableHead>
                <TableHead>Purpose</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Items</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="request in filteredRequests" :key="request.id">
                <TableCell>
                  <button 
                    class="hover:text-purple-700 hover:underline" 
                    @click="goToRequest(request.id)"
                  >
                    {{ request.request_no }}
                  </button>
                </TableCell>
                <TableCell>{{ formatDate(request.request_date) }}</TableCell>
                <TableCell>{{ request.department.name }}</TableCell>
                <TableCell>{{ request.user.name }}</TableCell>
                <TableCell>{{ request.purpose }}</TableCell>
                <TableCell>
                  <span 
                    :class="{
                      'text-yellow-500': request.status === 'pending',
                      'text-green-500': request.status === 'approved' || request.status === 'completed',
                      'text-red-500': request.status === 'rejected'
                    }"
                  >
                    {{ request.status.charAt(0).toUpperCase() + request.status.slice(1) }}
                  </span>
                </TableCell>
                <TableCell>{{ request.details.length }}</TableCell>
              </TableRow>

              <!-- Empty State -->
              <TableRow v-if="filteredRequests.length === 0">
                <TableCell colspan="7" class="text-center py-4 text-muted-foreground">
                  No requests found matching your criteria
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