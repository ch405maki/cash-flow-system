<script setup lang="ts">
import { ref } from 'vue'
import { Link, router, useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  Dialog, DialogContent, DialogDescription,
  DialogFooter, DialogHeader, DialogTitle, DialogTrigger,
} from '@/components/ui/dialog'
import { PackageCheck, Ticket, Check, Send, Printer, ArrowLeft } from 'lucide-vue-next'
import { useToast } from 'vue-toastification'
import POTimestampSheet from './Potimestampsheet.vue'
import POVoucherPopover from './Povoucherpopover.vue'

const toast = useToast()

const props = defineProps<{
  purchaseOrder: {
    id: number
    canvas_id: number
    po_no: string
    status: string
    voucher?: any
    approvals?: any[]
    created_at?: string
  }
  authUser: {
    id: number
    name: string
    role: string
    access?: number
  }
}>()

const emit = defineEmits<{
  print: []
}>()

const showApproveModal = ref(false)
const showForApproveModal = ref(false)

const form = useForm({
  status: '',
  password: '',
  remarks: '',
  canvas_id: props.purchaseOrder.canvas_id,
})

async function submitStatusUpdate(newStatus: string) {
  form.status = newStatus
  form.canvas_id = props.purchaseOrder.canvas_id

  form.patch(`/purchase-orders/${props.purchaseOrder.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Status updated successfully')
      showApproveModal.value = false
      showForApproveModal.value = false
      form.reset()
    },
    onError: (errors) => {
      toast.error(errors.password ?? 'Failed to update status')
    },
  })
}

function goToCreate(poId?: number) {
  const url = poId ? `/vouchers/create?po_id=${poId}` : '/vouchers/create'
  router.visit(url)
}
</script>

<template>
  <div class="space-x-2 flex space-x-2">
    <!-- Receiving Page -->
    <Button v-if="purchaseOrder.status === 'voucherCreated'" size="sm" @click="router.visit(`/purchase-orders/${purchaseOrder.id}/receiving`)">
      <PackageCheck />
      Receiving Page
    </Button>

    <!-- Create Voucher (accounting) -->
    <Button
      v-if="authUser.role === 'accounting'"
      variant="default"
      size="sm"
      @click.stop="goToCreate(purchaseOrder.id)"
    >
      <Ticket /> Create Voucher
    </Button>

    <!-- Approve (executive_director) -->
    <div v-if="authUser.role === 'executive_director'" class="space-x-2 flex">
      <Dialog v-model:open="showApproveModal">
        <DialogTrigger as-child>
          <Button
            variant="default"
            size="sm"
            :disabled="purchaseOrder.status !== 'forEOD' || form.processing"
          >
            <Check /> Approve
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Approve Purchase Order</DialogTitle>
            <DialogDescription>Please verify your identity and add remarks</DialogDescription>
          </DialogHeader>
          <div class="space-y-4">
            <div class="space-y-2">
              <Label for="approve-password">Password</Label>
              <Input id="approve-password" v-model="form.password" type="password" placeholder="Enter your password" class="w-full" />
            </div>
            <div class="space-y-2">
              <Label for="approve-remarks">Remarks (Optional)</Label>
              <Textarea id="approve-remarks" v-model="form.remarks" placeholder="Add any remarks" class="w-full" />
            </div>
          </div>
          <DialogFooter>
            <Button @click="submitStatusUpdate('approved')" :disabled="!form.password || form.processing">
              <span v-if="form.processing">Processing...</span>
              <span v-else>Confirm Approval</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <!-- Submit for EOD (purchasing, access 3) -->
    <div
      v-if="authUser.role === 'purchasing' && authUser.access === 3 && purchaseOrder.status === 'draft'"
      class="space-x-2 flex"
    >
      <Dialog v-model:open="showForApproveModal">
        <DialogTrigger as-child>
          <Button
            variant="default"
            size="sm"
            :disabled="['forEOD', 'approved'].includes(purchaseOrder.status) || form.processing"
          >
            <Send /> Submit for EOD
          </Button>
        </DialogTrigger>
        <DialogContent>
          <DialogHeader>
            <DialogTitle>Submit Purchase Order for Approval</DialogTitle>
            <DialogDescription>Please verify your identity and add remarks</DialogDescription>
          </DialogHeader>
          <div class="space-y-4">
            <div class="space-y-2">
              <Label for="eod-password">Password</Label>
              <Input id="eod-password" v-model="form.password" type="password" placeholder="Enter your password" class="w-full" />
            </div>
            <div class="space-y-2">
              <Label for="eod-remarks">Remarks (Optional)</Label>
              <Textarea id="eod-remarks" v-model="form.remarks" placeholder="Add any remarks" class="w-full" />
            </div>
          </div>
          <DialogFooter>
            <Button @click="submitStatusUpdate('forEOD')" :disabled="!form.password || form.processing">
              <span v-if="form.processing">Processing...</span>
              <span v-else>Confirm Approval</span>
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>

    <!-- Voucher Popover -->
    <POVoucherPopover
      v-if="purchaseOrder.status === 'voucherCreated'"
      :voucher="purchaseOrder.voucher"
    />

    <!-- Timestamp Sheet + Print + Back -->
    <div class="flex items-center space-x-2">
      <POTimestampSheet
        :po-no="purchaseOrder.po_no"
        :created-at="purchaseOrder.created_at"
        :approvals="purchaseOrder.approvals"
      />
      <Button size="sm" variant="outline" @click="$emit('print')">
        <Printer /> Print
      </Button>
      <Button variant="outline" size="sm" as-child>
        <Link href="/purchase-orders"><ArrowLeft /> Back</Link>
      </Button>
    </div>
  </div>
</template>