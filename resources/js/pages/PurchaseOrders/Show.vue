<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head } from '@inertiajs/vue3'
import PageHeader from '@/components/PageHeader.vue'

// ── Sub-components ──────────────────────────────────────────────
import POActionButtons    from '@/components/purchaseOrder/POActionButtons.vue'
import POAlerts           from '@/components/purchaseOrder/POAlerts.vue'
import POInfoTable        from '@/components/purchaseOrder/POInfoTable.vue'
import POItemsTable       from '@/components/purchaseOrder/POItemsTable.vue'
import POPrintSection     from '@/components/purchaseOrder/POPrintSection.vue'
import POFilePreviewDialog from '@/components/purchaseOrder/POFilePreviewDialog.vue'

// ── Props ────────────────────────────────────────────────────────
const props = defineProps<{
  purchaseOrder: {
    id: number
    canvas_id: number
    tin_no: string
    po_no: string
    date: string
    payee: string
    status: string
    amount: number
    remarks?: string
    check_payable_to?: string
    purpose?: string
    created_at?: string
    department?: { department_name: string }
    canvas?: {
      id: number
      remarks: string
      selected_files?: Array<{
        id: number
        file?: { original_filename?: string; file_path?: string; type?: string }
      }>
      approvals?: Array<{
        id: number
        comments?: string
        user?: { username?: string }
      }>
    }
    details: Array<{
      id: number
      quantity: number
      unit: string
      item_description: string
      unit_price: number
      amount: number
    }>
    approvals?: Array<{
      id: number
      approved?: boolean
      remarks?: string
      created_at?: string
      user?: { username?: string }
    }>
    voucher?: any
  }
  firstFileId: number | null
  authUser: {
    id: number
    name: string
    email: string
    role: string
    access?: number
  }
  signatories: Array<{
    full_name: string
    position: string
  }>
}>()

// ── Breadcrumbs ──────────────────────────────────────────────────
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Purchase Orders', href: '/purchase-orders' },
  { title: props.purchaseOrder.po_no, href: '' },
]

// ── Formatters ───────────────────────────────────────────────────
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

// ── Alert state ──────────────────────────────────────────────────
const showAlert = ref(true)

// ── File preview ─────────────────────────────────────────────────
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

// ── Print ────────────────────────────────────────────────────────
function printArea() {
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

// ── Signatories ──────────────────────────────────────────────────
const executiveDirector = computed(() =>
  props.signatories.find((s) => s.position === 'Executive Director') ?? null
)
</script>

<template>
  <Head title="Purchase Order Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">

      <!-- Header row -->
      <div class="flex items-center justify-between">
        <PageHeader
          :title="`Purchase Order: # ${purchaseOrder.po_no}`"
          subtitle="Purchase order details"
        />
        <POActionButtons
          :purchase-order="purchaseOrder"
          :auth-user="authUser"
          @print="printArea"
        />
      </div>

      <!-- Alerts -->
      <POAlerts
        :show-alert="showAlert"
        :selected-files="purchaseOrder.canvas?.selected_files"
        :remarks="purchaseOrder.remarks"
        :canvas-approvals="purchaseOrder.canvas?.approvals"
        @dismiss="showAlert = false"
        @preview-file="openPreview"
      />

      <!-- Body -->
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

    <!-- File Preview Dialog -->
    <POFilePreviewDialog
      v-model:open="previewOpen"
      :file="previewFile"
    />
  </AppLayout>

  <!-- Print Section (outside AppLayout so it prints cleanly) -->
  <POPrintSection
    :purchase-order="purchaseOrder"
    :auth-user="authUser"
    :executive-director="executiveDirector"
    :format-date="formatDate"
    :format-currency="formatCurrency"
  />
</template>