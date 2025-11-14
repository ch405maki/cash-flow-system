<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Printer, Download } from 'lucide-vue-next'
import { Head } from '@inertiajs/vue3'
import FormHeader from '@/components/reports/header/formHeder.vue'
import Button from '@/components/ui/button/Button.vue'
import { Label } from '@/components/ui/label'
import { Input } from '@/components/ui/input'
import PageHeader from '@/components/PageHeader.vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { exportToExcel, exportToCSV, type ExportColumn } from '@/lib/requestExport'

// Props
const { releases } = defineProps({
  releases: Array
})

console.log(releases)

// Date filters
const startDate = ref<string | null>(null)
const endDate = ref<string | null>(null)

// Helper: simple date formatter
const formatDate = (date: string | Date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}

// Grouping releases by request
const groupedReleases = computed(() => {
  const groups: Record<string, any> = {}

  for (const release of releases) {
    const reqNo = release.request.request_no
    if (!groups[reqNo]) {
      groups[reqNo] = {
        request_no: reqNo,
        department_name: release.request.department?.department_name,
        release_date: release.release_date,
        details: []
      }
    }
    groups[reqNo].details.push(...release.details)
  }

  return Object.values(groups)
})

// Filtered releases by date range
const filteredReleases = computed(() => {
  if (!startDate.value && !endDate.value) return groupedReleases.value

  return groupedReleases.value.filter((group: any) => {
    const releaseDate = new Date(group.release_date)
    const start = startDate.value ? new Date(startDate.value) : null
    const end = endDate.value ? new Date(endDate.value) : null

    if (start && releaseDate < start) return false
    if (end && releaseDate > end) return false
    return true
  })
})

// Print action
const printReport = () => {
  const printContents = document.getElementById('print-section')?.innerHTML
  const originalContents = document.body.innerHTML

  if (printContents) {
    document.body.innerHTML = printContents
    window.print()
    document.body.innerHTML = originalContents
    location.reload()
  } else {
    console.error('Print section not found')
  }
}

// Export functionality
const exportReport = (format: 'excel' | 'csv') => {
  if (!filteredReleases.value.length) return

  const exportData = filteredReleases.value.flatMap((group: any) => 
    group.details.map((detail: any) => ({
      releaseDate: group.release_date,
      requestNo: group.request_no,
      department: group.department_name,
      itemDescription: detail.request_detail.item_description,
      quantity: detail.quantity,
      unit: detail.request_detail.unit
    }))
  )

  const columns: ExportColumn[] = [
    { key: 'releaseDate', label: 'Release Date', format: (value) => formatDate(value) },
    { key: 'requestNo', label: 'Request Number' },
    { key: 'department', label: 'Department' },
    { key: 'itemDescription', label: 'Item Description' },
    { key: 'quantity', label: 'Quantity' },
    { key: 'unit', label: 'Unit' },
  ]

  if (format === 'excel') {
    exportToExcel(exportData, columns, {
      filename: 'released_items_report',
      title: 'Released Items Report'
    })
  } else {
    exportToCSV(exportData, columns, {
      filename: 'released_items_report'
    })
  }
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Released Items Report', href: '#' },
]
</script>

<template>
  <Head title="Released Items Report" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <!-- Header Section -->
      <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <PageHeader 
          title="Released Items Report" 
          subtitle="Track and manage all released inventory items across departments"
        />
        
        <!-- Action Buttons -->
        <div class="flex gap-2">
          <Button variant="outline" @click="exportReport('excel')" :disabled="!filteredReleases.length">
            <Download class="mr-2 h-4 w-4" />
            Export Excel
          </Button>
          <Button @click="printReport" :disabled="!filteredReleases.length">
            <Printer class="mr-2 h-4 w-4" />
            Print
          </Button>
        </div>
      </div>

      <!-- Filters Section -->
      <div class="bg-card border rounded-lg p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-end">
          <div class="space-y-2 flex-1">
            <Label class="text-sm font-medium">Start Date</Label>
            <Input
              type="date"
              v-model="startDate"
              class="w-full"
            />
          </div>
          <div class="space-y-2 flex-1">
            <Label class="text-sm font-medium">End Date</Label>
            <Input
              type="date"
              v-model="endDate"
              class="w-full"
            />
          </div>
          <div class="space-y-2">
            <Label class="text-sm font-medium text-transparent">Actions</Label>
            <div class="flex gap-2">
              <Button 
                variant="outline" 
                @click="() => { startDate = null; endDate = null; }"
                class="whitespace-nowrap"
              >
                Clear Dates
              </Button>
            </div>
          </div>
        </div>
        
        <!-- Results Count -->
        <div class="mt-4 pt-4 border-t">
          <p class="text-sm text-muted-foreground">
            Showing {{ filteredReleases.length }} release group{{ filteredReleases.length !== 1 ? 's' : '' }}
            <span v-if="startDate || endDate"> for selected date range</span>
          </p>
        </div>
      </div>

      <!-- Report Table -->
        <Table>
          <TableHeader>
            <TableRow class="bg-muted/50">
              <TableHead class="w-[15%] font-semibold">Release Date</TableHead>
              <TableHead class="w-[15%] font-semibold">Request No</TableHead>
              <TableHead class="w-[15%] font-semibold">Department</TableHead>
              <TableHead class="w-[40%] font-semibold">Item</TableHead>
              <TableHead class="w-[7.5%] font-semibold">Qty</TableHead>
              <TableHead class="w-[7.5%] font-semibold">Unit</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <template v-if="filteredReleases.length > 0">
              <template v-for="group in filteredReleases" :key="group.request_no">
                <TableRow 
                  v-for="(detail, index) in group.details" 
                  :key="detail.id"
                  class="hover:bg-muted/50 transition-colors"
                >
                  <TableCell 
                    v-if="index === 0"
                    :rowspan="group.details.length"
                    class="font-medium"
                  >
                    {{ formatDate(group.release_date) }}
                  </TableCell>
                  <TableCell 
                    v-if="index === 0"
                    :rowspan="group.details.length"
                    class="font-medium"
                  >
                    {{ group.request_no }}
                  </TableCell>
                  <TableCell 
                    v-if="index === 0"
                    :rowspan="group.details.length"
                  >
                    {{ group.department_name }}
                  </TableCell>
                  <TableCell class="max-w-[300px] truncate">
                    {{ detail.request_detail.item_description }}
                  </TableCell>
                  <TableCell class="font-medium">
                    {{ detail.quantity }}
                  </TableCell>
                  <TableCell>
                    {{ detail.request_detail.unit }}
                  </TableCell>
                </TableRow>
              </template>
            </template>
            
            <TableRow v-else>
              <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                <div class="flex flex-col items-center justify-center">
                  <p class="text-lg font-medium mb-2">No data available</p>
                  <p class="text-sm">
                    <span v-if="startDate || endDate">
                      No released items found for the selected date range
                    </span>
                    <span v-else>
                      No released items found in the system
                    </span>
                  </p>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <!-- Summary for Print -->
      <div id="print-section">
        <div class="hidden print:block">
          <div class="mb-4">
            <FormHeader text="Released Items" :bordered="false" />
          </div>
          <table class="table-auto border-collapse border w-full text-sm">
            <thead>
              <tr class="bg-gray-100">
                <th class="border px-2 h-[35px] w-[15%] text-left">Release Date</th>
                <th class="border px-2 w-[15%] text-left">Request No</th>
                <th class="border px-2 w-[15%] text-left">Department</th>
                <th class="border px-2 w-[40%]">Item</th>
                <th class="border px-2 w-[7.5%] text-left">Qty</th>
                <th class="border px-2 w-[7.5%] text-left">Unit</th>
              </tr>
            </thead>
            <tbody v-if="filteredReleases.length > 0">
              <template v-for="group in filteredReleases" :key="group.request_no">
                <tr v-for="(detail, index) in group.details" :key="detail.id">
                  <td
                    v-if="index === 0"
                    :rowspan="group.details.length"
                    class="border px-2"
                  >
                    {{ formatDate(group.release_date) }}
                  </td>
                  <td
                    v-if="index === 0"
                    :rowspan="group.details.length"
                    class="border px-2"
                  >
                    {{ group.request_no }}
                  </td>
                  <td
                    v-if="index === 0"
                    :rowspan="group.details.length"
                    class="border px-2"
                  >
                    {{ group.department_name }}
                  </td>
  
                  <td class="border px-2">{{ detail.request_detail.item_description }}</td>
                  <td class="border px-2">{{ detail.quantity }}</td>
                  <td class="border px-2">{{ detail.request_detail.unit }}</td>
                </tr>
              </template>
            </tbody>
  
            <tbody v-else>
              <tr>
                <td colspan="6" class="text-center py-4 text-gray-500">
                  No data available for the selected date range
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  </AppLayout>
</template>

<style>
/* Force landscape orientation during printing */
@media print {
  @page {
    size: A4 landscape;
    margin: 1cm;
  }
  table {
    font-size: 11px !important;
  }
  
  /* Hide non-print elements */
  .print\:hidden {
    display: none !important;
  }
  
  /* Ensure print visibility */
  .print\:block {
    display: block !important;
  }
}
</style>