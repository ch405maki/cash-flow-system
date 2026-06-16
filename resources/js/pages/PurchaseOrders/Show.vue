<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/components/PageHeader.vue'
import { Skeleton } from '@/components/ui/skeleton'
import { purchaseOrderService } from '@/services/purchaseOrderService'

import POActionButtons    from '@/components/purchaseOrder/POActionButtons.vue'
import POAlerts           from '@/components/purchaseOrder/POAlerts.vue'
import POInfoTable        from '@/components/purchaseOrder/POInfoTable.vue'
import POItemsTable       from '@/components/purchaseOrder/POItemsTable.vue'
import POPrintSection     from '@/components/purchaseOrder/POPrintSection.vue'
import POFilePreviewDialog from '@/components/purchaseOrder/POFilePreviewDialog.vue'

const props = defineProps<{ purchaseOrderId: number }>()

const loading = ref(true)
const purchaseOrder = ref<any>(null)
const authUser = ref<any>(null)
const firstFileId = ref<number | null>(null)
const signatories = ref<any[]>([])

onMounted(async () => {
  try {
    const response = await purchaseOrderService.showData(props.purchaseOrderId)
    purchaseOrder.value = response.data.purchaseOrder
    authUser.value = response.data.authUser
    firstFileId.value = response.data.firstFileId
    signatories.value = response.data.signatories
  } catch (error) {
    console.error('Failed to load purchase order:', error)
  } finally {
    loading.value = false
  }
})

const breadcrumbs = computed((): BreadcrumbItem[] => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Orders', href: '/purchase-order' },
  { title: purchaseOrder.value?.po_no || '', href: '' },
])

function formatDate(dateStr: string): string {
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: '2-digit',
  })
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency', currency: 'PHP',
  }).format(amount)
}

// Computed property to check if there's any alert data to show
const hasAlertData = computed(() => {
  if (!purchaseOrder.value) return false
  
  const hasFiles = purchaseOrder.value.canvas?.selected_files?.some((file: any) => file.file) ?? false
  const hasRemarks = purchaseOrder.value.remarks?.trim() ? true : false
  const hasComments = purchaseOrder.value.canvas?.approvals?.some((approval: any) => approval.comments?.trim()) ?? false
  
  return hasFiles || hasRemarks || hasComments
})

// Only show alert if there's actual data to display
const showAlert = computed(() => {
  return hasAlertData.value
})

const previewOpen = ref(false)
const previewFile = ref<{ name: string; path: string; type: string; path_name: string } | null>(null)

function openPreview(file: any) {
  previewFile.value = {
    name:      file.original_filename,
    path_name: file.file_path,
    path:      `/storage/canvases/${file.file_path}`,
    type:      file.type,
  }
  previewOpen.value = true
}

function printArea() {
  const printContents = document.getElementById('print-section')?.innerHTML
  const originalContents = document.body.innerHTML
  if (printContents) {
    document.body.innerHTML = printContents
    window.print()
    document.body.innerHTML = originalContents
    location.reload()
  }
}

const executiveDirector = computed(() =>
  signatories.value.find((s: any) => s.position === 'Executive Director') ?? null
)

async function reloadData() {
  loading.value = true
  try {
    const response = await purchaseOrderService.showData(props.purchaseOrderId)
    purchaseOrder.value = response.data.purchaseOrder
    authUser.value = response.data.authUser
    firstFileId.value = response.data.firstFileId
    signatories.value = response.data.signatories
  } catch (error) {
    console.error('Failed to reload purchase order:', error)
  } finally {
    loading.value = false
  }
}

</script>

<template>
  <Head :title="loading ? 'Loading...' : `PO ${purchaseOrder?.po_no || ''}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-4 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-32 w-full" />
      <Skeleton class="h-64 w-full" />
    </div>

    <template v-else-if="purchaseOrder">
      <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
        <div class="flex items-center justify-between">
          <PageHeader
            :title="`Purchase Order: # ${purchaseOrder.po_no}`"
            subtitle="Purchase order details"
          />
          <POActionButtons
            :purchase-order="purchaseOrder"
            :auth-user="authUser"
            @print="printArea"
            @status-updated="reloadData"
          />
        </div>

        <!-- Only show POAlerts component if there's data to display -->
        <POAlerts
          v-if="hasAlertData"
          :show-alert="showAlert"
          :selected-files="purchaseOrder.canvas?.selected_files"
          :remarks="purchaseOrder.remarks"
          :canvas-approvals="purchaseOrder.canvas?.approvals"
          @dismiss="showAlert = false"
          @preview-file="openPreview"
        />

        <div class="space-y-4">
          <POInfoTable
            :payee="purchaseOrder.payee"
            :po-no="purchaseOrder.po_no"
            :date="purchaseOrder.date"
            :check-payable-to="purchaseOrder.check_payable_to"
            :tin-no="purchaseOrder.tin_no"
            :format-date="formatDate"
          />

          <POItemsTable
            :details="purchaseOrder.details"
            :total-amount="purchaseOrder.amount"
            :format-currency="formatCurrency"
          />
        </div>
      </div>

      <POFilePreviewDialog
        v-model:open="previewOpen"
        :file="previewFile"
      />
    </template>
  </AppLayout>

  <POPrintSection
    v-if="purchaseOrder"
    :purchase-order="purchaseOrder"
    :auth-user="authUser"
    :executive-director="executiveDirector"
    :format-date="formatDate"
    :format-currency="formatCurrency"
  />
</template>
