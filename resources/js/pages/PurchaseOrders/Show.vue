<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog'
import { computed } from 'vue'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { useToast } from 'vue-toastification'
import { router } from '@inertiajs/vue3'
import { useForm } from '@inertiajs/vue3'

const toast = useToast()

const props = defineProps({
  purchaseOrder: {
    type: Object,
    required: true,
  },
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Purchase Orders', href: '/purchase-orders' },
  { title: `${props.purchaseOrder.po_no}`, href: '' },
];

function formatDate(dateStr: string): string {
  const date = new Date(dateStr);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  });
}

function formatCurrency(amount: number): string {
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP'
  }).format(amount);
}

// Modal state
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showReleaseModal = ref(false)
const showOrderModal = ref(false)

const form = useForm({
  status: '',
  password: '',
  remarks: '',
})

// Status update function
async function submitStatusUpdate(newStatus: string) {
  form.status = newStatus
  
  form.patch(`/purchase-orders/${props.purchaseOrder.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Status updated successfully')
      showApproveModal.value = false
      showRejectModal.value = false
      showReleaseModal.value = false
      showOrderModal.value = false
      form.reset()
    },
    onError: (errors) => {
      if (errors.password) {
        toast.error(errors.password)
      } else {
        toast.error('Failed to update status')
      }
    }
  })
}
</script>

<template>
  <Head title="Purchase Order Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Purchase Order: {{ purchaseOrder.po_no }}</h1>
        <div class="space-x-2">
          <!-- Approve Dialog -->
          <Dialog v-model:open="showApproveModal">
            <DialogTrigger as-child>
              <Button 
                variant="default" 
                size="sm" 
                :disabled="purchaseOrder.status === 'approved' || form.processing"
              >
                Approve
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Approve Purchase Order</DialogTitle>
                <DialogDescription>
                  Please verify your identity and add remarks
                </DialogDescription>
              </DialogHeader>
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label for="approve-password">Password</Label>
                  <Input 
                    id="approve-password" 
                    v-model="form.password" 
                    type="password" 
                    placeholder="Enter your password"
                    class="w-full"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="approve-remarks">Remarks (Optional)</Label>
                  <Textarea
                    id="approve-remarks"
                    v-model="form.remarks"
                    placeholder="Add any remarks about this approval"
                    class="w-full"
                  />
                </div>
              </div>
              <DialogFooter>
                <Button 
                  @click="submitStatusUpdate('approved')"
                  :disabled="!form.password || form.processing"
                >
                  <span v-if="form.processing">Processing...</span>
                  <span v-else>Confirm Approval</span>
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <!-- Reject Dialog -->
          <Dialog v-model:open="showRejectModal">
            <DialogTrigger as-child>
              <Button 
                variant="outline" 
                size="sm" 
                :disabled="purchaseOrder.status === 'rejected' || form.processing"
              >
                Reject
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Reject Purchase Order</DialogTitle>
                <DialogDescription>
                  Please verify your identity and provide a reason
                </DialogDescription>
              </DialogHeader>
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label for="reject-password">Password</Label>
                  <Input 
                    id="reject-password" 
                    v-model="form.password" 
                    type="password" 
                    placeholder="Enter your password"
                    class="w-full"
                  />
                </div>
                <div class="space-y-2">
                  <Label for="reject-remarks">Reason for Rejection (Required)</Label>
                  <Textarea
                    id="reject-remarks"
                    v-model="form.remarks"
                    placeholder="Explain why this PO is being rejected"
                    class="w-full"
                    required
                  />
                </div>
              </div>
              <DialogFooter>
                <Button 
                  @click="submitStatusUpdate('rejected')"
                  :disabled="!form.password || !form.remarks || form.processing"
                  variant="destructive"
                >
                  <span v-if="form.processing">Processing...</span>
                  <span v-else>Confirm Rejection</span>
                </Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>

          <Button variant="outline" as-child>
            <Link href="/purchase-orders">Back to List</Link>
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
        <table class="w-full text-sm border border-border rounded-md">
          <tbody>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r w-48">COMPANY NAME:</td>
              <td class="p-2 uppercase border-r w-xl">{{ purchaseOrder.payee }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r w-40">P.O. NUMBER:</td>
              <td class="p-2 w-40">{{ purchaseOrder.po_no }}</td>
            </tr>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r">CHECK PAYABLE TO:</td>
              <td class="p-2 uppercase border-r">{{ purchaseOrder.check_payable_to }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r">P.O. DATE:</td>
              <td class="p-2">{{ formatDate(purchaseOrder.date) }}</td>
            </tr>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r">DEPARTMENT:</td>
              <td class="p-2 uppercase border-r">{{ purchaseOrder.department.department_name }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r">AMOUNT:</td>
              <td class="p-2">{{ formatCurrency(purchaseOrder.amount) }}</td>
            </tr>
          </tbody>
        </table>
        <table class="w-full text-sm border border-border rounded-md">
          <tbody>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r w-48">ACCOUNT CHARGE:</td>
              <td class="p-2 uppercase border-r">{{ purchaseOrder.account.account_title }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r">REMARKS:</td>
              <td class="p-2 uppercase">{{ purchaseOrder.remarks || 'No remarks' }}</td>
            </tr>
            <tr>
              <td class="p-2 font-medium text-muted-foreground border-r">PURPOSE:</td>
              <td class="p-2 uppercase" colspan="3">{{ purchaseOrder.purpose || 'No remarks' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Items Table -->
      <Table class="w-full text-sm border border-border rounded-md">
        <TableHeader>
          <TableRow>
              <TableHead>Quantity</TableHead>
              <TableHead>Unit/S</TableHead>
              <TableHead>Description</TableHead>
            <TableHead class="text-right">Unit Price</TableHead>
            <TableHead class="text-right">Amount</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow v-for="item in purchaseOrder.details" :key="item.id">
              <TableCell>{{ item.quantity }}</TableCell>
              <TableCell> {{ item.unit }}</TableCell>
              <TableCell>{{ item.item_description }}</TableCell>
            <TableCell class="text-right">{{ formatCurrency(item.unit_price) }}</TableCell>
            <TableCell class="text-right font-medium">{{ formatCurrency(item.amount) }}</TableCell>
          </TableRow>
          <TableRow>
            <TableCell colspan="4" class="text-right font-medium">Total</TableCell>
            <TableCell class="text-right font-bold">
              {{ formatCurrency(purchaseOrder.amount) }}
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>
  </AppLayout>
</template>