<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { computed, ref } from 'vue'
import { type BreadcrumbItem } from '@/types'
import { Printer } from 'lucide-vue-next'
import { Head } from '@inertiajs/vue3'
import FormHeader from '@/components/reports/header/formHeder.vue'
import Button from '@/components/ui/button/Button.vue'

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

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Reports', href: '/reports' },
  { title: 'Request Summary', href: '/' },
]
</script>

<template>
  <Head title="Released Items Report" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-xl font-bold">Released Items Report</h1>
        <Button @click="printReport">
          <Printer class="mr-2 h-4 w-4" />
          Print
        </Button>
      </div>

      <!-- Filters -->
      <div class="flex gap-4 mb-6">
        <div>
          <label class="block text-xs font-medium">Start Date</label>
          <input
            type="date"
            v-model="startDate"
            class="border rounded px-2 py-1 text-sm"
          />
        </div>
        <div>
          <label class="block text-xs font-medium">End Date</label>
          <input
            type="date"
            v-model="endDate"
            class="border rounded px-2 py-1 text-sm"
          />
        </div>
      </div>

      <!-- Table -->
      <div id="print-section">
        <div class="hidden print:block mb-4">
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
/* ✅ Force landscape orientation during printing */
@media print {
  @page {
    size: A4 landscape;
    margin: 1cm;
  }
  table {
    font-size: 11px !important;
  }
}
</style>
