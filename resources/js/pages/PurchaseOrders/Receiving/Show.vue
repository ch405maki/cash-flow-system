<script setup lang="ts">
import { ref, computed } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import { Label } from '@/components/ui/label'
import PageHeader from '@/components/PageHeader.vue'
import {
  Table, TableBody, TableCell,
  TableHead, TableHeader, TableRow,
} from '@/components/ui/table'
import {
  Select, SelectContent, SelectItem,
  SelectTrigger, SelectValue,
} from '@/components/ui/select'
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { useToast } from 'vue-toastification'
import {
  ArrowLeft, PackageCheck, CheckCircle2,
  AlertTriangle, Info, Save,
} from 'lucide-vue-next'

const toast = useToast()

// ── Types ────────────────────────────────────────────────────────
interface ReceivingRecord {
  id: number
  quantity_received: number
  condition: string
  received_date: string
  remarks: string | null
  receiver?: { username: string }
  created_at: string
}

interface PODetail {
  id: number
  quantity: number
  unit: string
  item_description: string
  unit_price: number
  amount: number
  receivings: ReceivingRecord[]
}

interface PurchaseOrder {
  id: number
  po_no: string
  payee: string
  date: string
  amount: number
  status: string
  department?: { department_name: string }
  user?: { name: string }
  details: PODetail[]
}

// ── Props ────────────────────────────────────────────────────────
const props = defineProps<{
  purchaseOrder: PurchaseOrder
  authUser: { id: number; name: string }
}>()

// ── Breadcrumbs ──────────────────────────────────────────────────
const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Purchase Orders', href: '/purchase-orders' },
  { title: 'Receiving', href: '/purchase-orders/receiving' },
  { title: props.purchaseOrder.po_no, href: '' },
]

// ── Formatters ───────────────────────────────────────────────────
function formatDate(d: string) {
  return new Date(d).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: '2-digit',
  })
}

function formatCurrency(v: number) {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(v)
}

function today() {
  return new Date().toISOString().split('T')[0]
}

// ── Build inline row state ───────────────────────────────────────
// Each detail gets its own editable row, pre-filled with defaults
interface RowState {
  po_detail_id: number
  quantity_ordered: number
  quantity_received: number | ''
  condition: string
  received_date: string
  remarks: string
  dirty: boolean
}

const rows = ref<RowState[]>(
  props.purchaseOrder.details.map((d) => ({
    po_detail_id:       d.id,
    quantity_ordered:   parseInt(d.quantity), 
    quantity_received:  '',
    condition:          'good',
    received_date:      today(),
    remarks:            '',
    dirty:              false,
  }))
)

// ── Computed helpers ─────────────────────────────────────────────
function totalReceivedForDetail(detail: PODetail): number {
  return detail.receivings.reduce((s, r) => s + r.quantity_received, 0)
}

function receivingStatus(detail: PODetail): 'complete' | 'partial' | 'none' {
  const received = totalReceivedForDetail(detail)
  if (received === 0) return 'none'
  if (received >= detail.quantity) return 'complete'
  return 'partial'
}

const conditionOptions = [
  { value: 'good',       label: 'Good',       class: 'text-green-600' },
  { value: 'damaged',    label: 'Damaged',     class: 'text-red-600' },
  { value: 'incomplete', label: 'Incomplete',  class: 'text-yellow-600' },
]

// Only submit rows the user has touched and filled in a quantity
const dirtyRows = computed(() =>
  rows.value.filter((r) => r.dirty && r.quantity_received !== '')
)

const hasChanges = computed(() => dirtyRows.value.length > 0)

// Validation: quantity_received can't exceed remaining
function remaining(index: number): number {
  const detail = props.purchaseOrder.details[index]
  const alreadyReceived = totalReceivedForDetail(detail)
  return detail.quantity - alreadyReceived
}

function rowError(index: number): string | null {
  const row = rows.value[index]
  if (row.quantity_received === '') return null
  const qty = Number(row.quantity_received)
  if (qty < 0) return 'Must be 0 or more'
  if (qty > remaining(index)) return `Max receivable: ${remaining(index)}`
  return null
}

const hasErrors = computed(() =>
  rows.value.some((_, i) => rowError(i) !== null)
)

// ── Submit ───────────────────────────────────────────────────────
const processing = ref(false)

function submit() {
  if (!hasChanges.value || hasErrors.value) return

  const items = dirtyRows.value.map((r) => ({
    po_detail_id:       r.po_detail_id,
    quantity_ordered:   parseInt(r.quantity_ordered), 
    quantity_received:  Number(r.quantity_received),
    condition:          r.condition,
    received_date:      r.received_date,
    remarks:            r.remarks || null,
  }))

  processing.value = true

  // Use router.post directly instead of useForm inside a function
  router.post(
    `/purchase-orders/${props.purchaseOrder.id}/receiving`,
    { items },
    {
      preserveScroll: true,
      onSuccess: () => {
        toast.success('Receiving saved successfully')
        rows.value.forEach((r) => {
          r.quantity_received = ''
          r.remarks = ''
          r.dirty = false
        })
      },
      onError: (errors) => {
        console.error('Receiving errors:', errors)
        toast.error('Failed to save — check quantities and try again')
      },
      onFinish: () => {
        processing.value = false
      },
    }
  )
}
</script>

<template>
  <Head :title="`Receiving — ${purchaseOrder.po_no}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 p-6">

      <!-- Header -->
      <div class="flex items-center justify-between flex-wrap gap-4">
        <PageHeader
          :title="`Receiving: ${purchaseOrder.po_no}`"
          :subtitle="`${purchaseOrder.payee} · ${formatDate(purchaseOrder.date)}`"
        />
      </div>

      <!-- PO Meta -->
      <Card>
        <CardContent class="pt-4 pb-4">
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
            <div>
              <p class="text-xs text-muted-foreground">PO Number</p>
              <p class="font-mono font-semibold">{{ purchaseOrder.po_no }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Payee</p>
              <p class="capitalize font-medium">{{ purchaseOrder.payee }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Department</p>
              <p>{{ purchaseOrder.department?.department_name ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs text-muted-foreground">Total Amount</p>
              <p class="font-semibold">{{ formatCurrency(purchaseOrder.amount) }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Hint -->
      <div class="flex items-start gap-2 text-sm text-muted-foreground bg-blue-50 border border-blue-100 rounded-lg px-4 py-3">
        <Info class="h-4 w-4 mt-0.5 text-blue-500 shrink-0" />
        <p>
          Fill in the <strong>Qty Received</strong> for any items delivered today. 
          Partial quantities are allowed — you can record the rest in a future session.
          Rows left blank will not be saved.
        </p>
      </div>

      <!-- Inline Receiving Table -->
      <div class="flex justify-between items-center">
        <PageHeader
          title="Line Items"
          subtitle='Edit inline, then click "Save Received Items"'
        />
        <div v-if="hasChanges" >
          <Button
            variant="default"
            size="sm"
            :disabled="hasErrors || processing"
            @click="submit"
          >
            <Save class="h-4 w-4" />
            {{ processing ? 'Saving...' : `Confirm Received ${dirtyRows.length} item${dirtyRows.length === 1 ? '' : 's'}` }}
          </Button>
        </div>
      </div>
      <Table>
        <TableHeader>
          <TableRow class="bg-muted/30">
            <TableHead class="pl-6 w-[280px]">Description</TableHead>
            <TableHead class="text-center w-24">Ordered</TableHead>
            <TableHead class="text-center w-24">Received</TableHead>
            <TableHead class="text-center w-24">Remaining</TableHead>
            <TableHead class="text-center w-28">Status</TableHead>
            <TableHead class="text-center w-28">Qty to Receive</TableHead>
            <TableHead class="w-36">Date</TableHead>
            <TableHead class="w-52 pr-6">Remarks</TableHead>
          </TableRow>
        </TableHeader>

        <TableBody>
          <template
            v-for="(detail, index) in purchaseOrder.details"
            :key="detail.id"
          >
            <!-- Main editable row -->
            <TableRow
              :class="[
                'transition-colors',
                rows[index].dirty ? 'bg-blue-50/50' : 'hover:bg-muted/30',
                rowError(index) ? 'bg-red-50/40' : '',
              ]"
            >
              <!-- Description -->
              <TableCell class="pl-6">
                <p class="font-medium text-sm">{{ detail.item_description }}</p>
                <p class="text-xs text-muted-foreground">{{ detail.unit }}</p>
              </TableCell>

              <!-- Ordered -->
              <TableCell class="text-center text-sm font-mono">
                {{ detail.quantity }}
              </TableCell>

              <!-- Total received so far -->
              <TableCell class="text-center text-sm font-mono">
                {{ totalReceivedForDetail(detail) }}
              </TableCell>

              <!-- Remaining -->
              <TableCell class="text-center text-sm font-mono">
                <span :class="remaining(index) === 0 ? 'text-green-600 font-semibold' : 'text-orange-600'">
                  {{ remaining(index) }}
                </span>
              </TableCell>

              <!-- Status badge -->
              <TableCell class="text-center">
                <span
                  v-if="receivingStatus(detail) === 'complete'"
                  class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-700 border border-green-200"
                >
                  <CheckCircle2 class="h-3 w-3" /> Complete
                </span>
                <span
                  v-else-if="receivingStatus(detail) === 'partial'"
                  class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-yellow-100 text-yellow-700 border border-yellow-200"
                >
                  <AlertTriangle class="h-3 w-3" /> Partial
                </span>
                <span
                  v-else
                  class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded-full bg-muted text-muted-foreground border"
                >
                  Pending
                </span>
              </TableCell>

              <!-- Qty to receive (inline input) -->
              <TableCell class="text-center">
                <div class="flex flex-col items-center gap-1">
                  <Input
                    v-model="rows[index].quantity_received"
                    type="number"
                    min="0"
                    :max="remaining(index)"
                    :disabled="remaining(index) === 0"
                    class="w-20 text-center h-8 text-sm"
                    :class="rowError(index) ? 'border-red-400 focus-visible:ring-red-400' : ''"
                    placeholder="0"
                    @input="rows[index].dirty = true"
                  />
                  <p v-if="rowError(index)" class="text-xs text-red-500">
                    {{ rowError(index) }}
                  </p>
                </div>
              </TableCell>

              <!-- Date -->
              <TableCell>
                <Input
                  v-model="rows[index].received_date"
                  type="date"
                  class="h-8 text-xs w-36"
                  :disabled="remaining(index) === 0"
                  @change="rows[index].dirty = true"
                />
              </TableCell>

              <!-- Remarks -->
              <TableCell class="pr-6">
                <Input
                  v-model="rows[index].remarks"
                  type="text"
                  placeholder="Optional remarks…"
                  class="h-8 text-xs"
                  :disabled="remaining(index) === 0"
                  @input="rows[index].dirty = true"
                />
              </TableCell>
            </TableRow>

            <!-- Receiving history sub-rows -->
            <TableRow
              v-if="detail.receivings.length"
              class="bg-muted/10"
            >
              <TableCell colspan="9" class="pl-10 pb-3 pt-1">
                <p class="text-xs font-semibold text-muted-foreground mb-2 uppercase tracking-wide">
                  Receiving History
                </p>
                <div class="space-y-1">
                  <div
                    v-for="rec in detail.receivings"
                    :key="rec.id"
                    class="flex items-center gap-4 text-xs text-muted-foreground"
                  >
                    <span class="font-mono w-24">{{ formatDate(rec.received_date) }}</span>
                    <span class="font-semibold text-foreground">{{ rec.quantity_received }} {{ detail.unit }}</span>
                    <span
                      :class="{
                        'text-green-600': rec.condition === 'good',
                        'text-red-500':   rec.condition === 'damaged',
                        'text-yellow-600': rec.condition === 'incomplete',
                      }"
                      class="capitalize"
                    >
                      {{ rec.condition }}
                    </span>
                    <span class="text-muted-foreground">by: {{ rec.receiver?.username ?? '—' }}</span>  
                    <span v-if="rec.remarks" class="italic">"{{ rec.remarks }}"</span>
                  </div>
                </div>
              </TableCell>
            </TableRow>

          </template>
        </TableBody>
      </Table>
    </div>
  </AppLayout>
</template>