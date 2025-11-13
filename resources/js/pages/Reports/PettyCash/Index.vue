<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { FileText, Download, Search, Filter, RotateCcw } from 'lucide-vue-next';
import { formatDate } from '@/lib/utils'
import { exportToExcel, exportToCSV } from '@/lib/pettycashExport'
import { ref, computed, reactive } from 'vue'
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard', },
    { title: 'Petty Cash', href: '#', },
];

const props = defineProps<{
  pettyCash: object
}>()

// Filter states
const filters = reactive({
  search: '',
  startDate: '',
  endDate: '',
  status: 'all'
})

// Reset filters
const resetFilters = () => {
  filters.search = ''
  filters.startDate = ''
  filters.endDate = ''
  filters.status = 'all'
}

// Computed filtered data
const filteredPettyCash = computed(() => {
  if (!props.pettyCash || Object.keys(props.pettyCash).length === 0) return []

  return props.pettyCash.filter((item: any) => {
    // Search filter
    const searchTerm = filters.search.toLowerCase()
    const matchesSearch = !filters.search || 
      item.pcv_no?.toLowerCase().includes(searchTerm) ||
      item.paid_to?.toLowerCase().includes(searchTerm) ||
      item.user?.first_name?.toLowerCase().includes(searchTerm) ||
      item.user?.last_name?.toLowerCase().includes(searchTerm) ||
      item.remarks?.toLowerCase().includes(searchTerm)

    // Date filter
    const itemDate = new Date(item.date)
    const startDate = filters.startDate ? new Date(filters.startDate) : null
    const endDate = filters.endDate ? new Date(filters.endDate) : null
    
    let matchesDate = true
    if (startDate && endDate) {
      matchesDate = itemDate >= startDate && itemDate <= endDate
    } else if (startDate) {
      matchesDate = itemDate >= startDate
    } else if (endDate) {
      matchesDate = itemDate <= endDate
    }

    // Status filter
    const matchesStatus = filters.status === 'all' || item.status === filters.status

    return matchesSearch && matchesDate && matchesStatus
  })
})

const hasItem = computed(() => filteredPettyCash.value.length > 0)

const totalAmount = (items: any[]) => {
    return items.reduce((sum, item) => sum + parseFloat(item.amount), 0).toFixed(2);
};

// Export functions
const exportData = (format: 'excel' | 'csv') => {
  if (!hasItem.value) return

  const exportData = filteredPettyCash.value.map((item: any) => ({
    'PCV No': item.pcv_no,
    'Paid To': item.paid_to,
    'Requested By': `${item.user?.first_name} ${item.user?.last_name}`,
    'Date': formatDate(item.date),
    'Status': item.status,
    'Remarks': item.remarks,
    'Amount': totalAmount(item.items)
  }))

  const options = {
    filename: 'petty_cash_report',
    headers: ['PCV No', 'Paid To', 'Requested By', 'Date', 'Status', 'Remarks', 'Amount']
  }

  if (format === 'excel') {
    exportToExcel(exportData, options)
  } else {
    exportToCSV(exportData, options)
  }
}
</script>

<template>
    <Head title="Petty Cash" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
          <!-- Header -->
          <div class="flex justify-between items-center">
            <PageHeader 
              title="Petty Cash" 
              subtitle="List of created petty cash."
            />
            
            <!-- Export Buttons -->
            <div class="flex gap-2" v-if="hasItem">
              <Button 
                size="sm" 
                @click="exportData('excel')"
                :disabled="!hasItem"
              >
                <Download class="h-4 w-4 mr-2" />
                Export Excel
              </Button>
            </div>
          </div>

          <!-- Filters -->
          <div class="bg-card border rounded-lg p-4">
            <div class="flex items-center gap-4 mb-4">
              <Filter class="h-4 w-4 text-muted-foreground" />
              <h3 class="text-sm font-medium">Filters</h3>
              <Button 
                variant="outline" 
                size="sm" 
                @click="resetFilters"
                class="ml-auto"
              >
                <RotateCcw  />Reset Filters
              </Button>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
              <!-- Start Date -->
              <div class="space-y-2">
                <label class="text-sm font-medium">From Date</label>
                <Input
                  v-model="filters.startDate"
                  type="date"
                />
              </div>

              <!-- End Date -->
              <div class="space-y-2">
                <label class="text-sm font-medium">To Date</label>
                <Input
                  v-model="filters.endDate"
                  type="date"
                />
              </div>

              <!-- Search -->
              <div class="space-y-2 col-span-2">
                <label class="text-sm font-medium">Search</label>
                <div class="relative">
                  <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                  <Input
                    v-model="filters.search"
                    placeholder="Search PCV, name, remarks..."
                    class="pl-8"
                  />
                </div>
              </div>
            </div>

            <!-- Results Count -->
            <div class="mt-4 flex justify-between items-center">
              <p class="text-sm text-muted-foreground">
                Showing {{ filteredPettyCash.length }} of {{ pettyCash?.length || 0 }} records
              </p>
              
              <!-- Active Filters -->
              <div class="flex gap-2" v-if="filters.search || filters.startDate || filters.endDate || filters.status !== 'all'">
                <span class="text-xs text-muted-foreground">Active filters:</span>
                <div class="flex flex-wrap gap-1">
                  <span 
                    v-if="filters.search" 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-primary/10 text-primary"
                  >
                    Search: "{{ filters.search }}"
                  </span>
                  <span 
                    v-if="filters.startDate" 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800"
                  >
                    From: {{ formatDate(filters.startDate) }}
                  </span>
                  <span 
                    v-if="filters.endDate" 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-blue-100 text-blue-800"
                  >
                    To: {{ formatDate(filters.endDate) }}
                  </span>
                  <span 
                    v-if="filters.status !== 'all'" 
                    class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800"
                  >
                    Status: {{ filters.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Table -->
          <Table v-if="hasItem">
            <TableCaption>A list of your petty cash.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[200px]">PCV NO</TableHead>
                <TableHead class="w-[100px]">Paid to</TableHead>
                <TableHead class="w-[150px]">Requested By</TableHead>
                <TableHead class="w-[100px]">Date</TableHead>
                <TableHead class="w-[100px]">Status</TableHead>
                <TableHead class="w-[300px]">Remarks</TableHead>
                <TableHead class="w-[100px] text-right">Amount</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow 
                v-for="item in filteredPettyCash"  
                :key="item.id" 
                @click="router.get(route('petty-cash.view', item.id))"
                class="cursor-pointer hover:bg-muted/50"
                >
                <TableCell class="font-medium">
                  {{ item.pcv_no }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ item.paid_to }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ item.user?.first_name }} {{ item.user?.last_name }}
                </TableCell>
                <TableCell class="capitalize">
                  {{ formatDate(item.date) }}
                </TableCell>
                <TableCell class="capitalize">
                  <span 
                    :class="{
                      'bg-yellow-100 text-yellow-800': item.status === 'pending',
                      'bg-green-100 text-green-800': item.status === 'approved',
                      'bg-red-100 text-red-800': item.status === 'rejected',
                      'bg-blue-100 text-blue-800': item.status === 'completed'
                    }"
                    class="inline-flex px-2 py-1 rounded-full text-xs font-medium"
                  >
                    {{ item.status }}
                  </span>
                </TableCell>
                <TableCell class="capitalize max-w-[300px] truncate">
                  "{{ item.remarks }}"
                </TableCell>
                <TableCell class="text-right font-medium">
                  ₱{{ totalAmount(item.items) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <!-- Empty State -->
          <div v-else class="flex h-48 flex-col items-center justify-center rounded-xl border">
            <FileText class="h-8 w-8 text-muted-foreground" />
            <p class="mt-2 text-sm text-muted-foreground">No petty cash records found</p>
            <p class="text-xs text-muted-foreground mb-4" v-if="filters.search || filters.startDate || filters.endDate || filters.status !== 'all'">
              Try adjusting your filters
            </p>
            <p class="text-xs text-muted-foreground mb-4" v-else>
              List of petty cash will appear here
            </p>
            <Button 
              variant="outline" 
              size="sm" 
              @click="resetFilters"
              v-if="filters.search || filters.startDate || filters.endDate || filters.status !== 'all'"
            >
              Clear Filters
            </Button>
          </div>
        </div>
    </AppLayout>
</template>