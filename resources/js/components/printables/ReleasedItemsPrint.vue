<template>
<!-- class="hidden" -->
<div class="hidden">
    <div ref="printableArea" class="printable-area bg-white p-8">
        <!-- Header -->
        <Header text='STOCK ISSUANCE FORM' :bordered='false' />

        <!-- Issued To Information -->
        <div class="flex justify-between text-sm mb-6">
            <div>
                <p><strong>Issued To:</strong> {{ request.department?.department_name }}</p>
            </div>
            <div>
                <p><strong>Request Date:</strong> {{ formatDate(request.request_date) }}</p>
            </div>
        </div>

        <!-- Released Items Table -->
        <table class="w-full border-collapse border border-gray-300 text-sm">
            <thead>
            <tr class="bg-gray-200">
                <th class="border border-gray-300 px-3 py-2 text-center w-12">#</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Quantity</th>
                <th class="border border-gray-300 px-3 py-2 text-center">Unit</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Description</th>
                <th class="border border-gray-300 px-3 py-2 text-left">Remarks</th>
            </tr>
            </thead>
            <tbody>
            <template v-for="(release, releaseIndex) in request.releases" :key="release.id">
                <tr 
                v-for="(detail, index) in release.details" 
                :key="detail.id"
                class="hover:bg-gray-50"
                >
                <td class="border border-gray-300 px-3 py-2 text-center">
                    {{ index + 1 }}
                </td>
                <td class="border border-gray-300 px-3 py-2 text-center">
                    {{ detail.quantity }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2 text-center">
                        {{ getItemUnit(detail) }}
                    </td>
                    <td class="border border-gray-300 px-3 py-2">
                    {{ getItemDescription(detail) }}
                    </td>
                <td class="border border-gray-300 px-3 py-2">
                    {{ getItemTagging(detail) }}
                </td>
                </tr>
            </template>
            
            <tr v-if="!request.releases || request.releases.length === 0 || getAllReleasedItems().length === 0">
                <td colspan="5" class="border border-gray-300 px-3 py-4 text-center text-gray-500">
                No items have been released yet.
                </td>
            </tr>
            </tbody>
        </table>
        

        <!-- Signatures -->
        <div class="mt-16 flex items-center justify-between px-4">
            <div class="text-center">
                <div class="pt-2 mx-auto">
                    <p class="text-sm font-semibold">Prepared By : <span class="border-b-2 border-gray-400">{{ getLatestRelease()?.user?.first_name }} {{ getLatestRelease()?.user?.last_name }}</span></p>
                </div>
            </div>
            <div class="text-center">
            <div class="mt-12 pt-2 mx-auto">
                <p class="text-sm font-semibold">Received By : <span class="border-b-2 border-gray-400"></span></p>
            </div>
            </div>
        </div>
    </div>
</div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import Header from '@/components/reports/header/formHeder.vue'

interface ReleaseDetail {
  id: number
  quantity: number
  request_detail_id: number
  requestDetail?: {
    id: number
    quantity: string
    released_quantity: string
    unit: string
    item_description: string
    tagging?: string
  }
}

interface Release {
  id: number
  release_date: string
  notes?: string
  user: {
    id: number
    first_name: string
    last_name: string
  }
  details: ReleaseDetail[]
}

interface Department {
  id: number
  department_name: string
  department_description: string
}

interface User {
  id: number
  first_name: string
  last_name: string
  email: string
}

interface Request {
  id: number
  request_no: string
  request_date: string
  purpose: string
  status: string
  user?: User
  department?: Department
  releases?: Release[]
}

interface Props {
  request: Request
  user: any
}

const props = defineProps<Props>()
const printableArea = ref<HTMLDivElement>()

const currentDate = computed(() => new Date().toLocaleDateString())
const currentDateTime = computed(() => new Date().toLocaleString())

function printArea() {
  if (!printableArea.value) return

  const printContents = printableArea.value.innerHTML
  const originalContents = document.body.innerHTML

  document.body.innerHTML = printContents
  window.print()
  document.body.innerHTML = originalContents
  window.location.reload()
}

function formatDate(dateString: string) {
  if (!dateString) return 'N/A'
  return new Date(dateString).toLocaleDateString()
}

function getAllReleasedItems() {
  if (!props.request.releases) return []
  
  const allItems: ReleaseDetail[] = []
  props.request.releases.forEach(release => {
    if (release.details && release.details.length > 0) {
      allItems.push(...release.details)
    }
  })
  
  return allItems
}

function getItemDescription(detail: ReleaseDetail) {
  // Check if requestDetail exists and has item_description
  if (detail.requestDetail && detail.requestDetail.item_description) {
    return detail.requestDetail.item_description
  }
  
  // Fallback: try to find the item description from the main request details
  if (props.request.details) {
    const requestDetail = props.request.details.find((d: any) => d.id === detail.request_detail_id)
    if (requestDetail && requestDetail.item_description) {
      return requestDetail.item_description
    }
  }
  
  return 'No description available'
}

function getItemUnit(detail: ReleaseDetail) {
  // Check if requestDetail exists and has unit
  if (detail.requestDetail && detail.requestDetail.unit) {
    return detail.requestDetail.unit
  }
  
  // Fallback: try to find the unit from the main request details
  if (props.request.details) {
    const requestDetail = props.request.details.find((d: any) => d.id === detail.request_detail_id)
    if (requestDetail && requestDetail.unit) {
      return requestDetail.unit
    }
  }
  
  return 'N/A'
}

function getItemTagging(detail: ReleaseDetail) {
  // Check if requestDetail exists and has tagging
  if (detail.requestDetail && detail.requestDetail.tagging) {
    return detail.requestDetail.tagging
  }
  
  // Fallback: try to find the tagging from the main request details
  if (props.request.details) {
    const requestDetail = props.request.details.find((d: any) => d.id === detail.request_detail_id)
    if (requestDetail && requestDetail.tagging) {
      return requestDetail.tagging
    }
  }
  
  return 'N/A'
}

function getLatestRelease(): Release | null {
  if (!props.request.releases || props.request.releases.length === 0) return null
  return props.request.releases[props.request.releases.length - 1]
}

defineExpose({
  printArea
})
</script>

<style scoped>
@media print {
  @page {
    margin: 0.5in;
    size: letter;
  }
  
  body {
    margin: 0;
    padding: 0;
    font-size: 12px;
  }
  
  .printable-area {
    display: block !important;
    width: 100%;
    height: auto;
  }
  
  .hidden {
    display: none;
  }
}

.hidden {
  display: none;
}

.printable-area {
  font-family: 'Arial', sans-serif;
  line-height: 1.4;
}

table {
  page-break-inside: auto;
}

tr {
  page-break-inside: avoid;
  page-break-after: auto;
}
</style>