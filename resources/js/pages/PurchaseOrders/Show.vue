<script setup lang="ts">
import { ref } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { computed } from 'vue';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
  TableCaption,
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
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { useToast } from 'vue-toastification'
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { useForm } from '@inertiajs/vue3'
import { BellRing, X, ReceiptText,  Send , AlertCircle,Ticket ,Printer   } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

const toast = useToast()

const props = defineProps<{
  purchaseOrder: {
    id: number;
    po_no: string;
    date: string;
    payee: string;
    status: string;
    amount: number;
    department: {
      department_name: string;
    };
    canvas: {
      id: number;
      remarks: string;
    };
    // Add other fields as needed
  };
  authUser: {
    id: number;
    name: string;
    email: string;
    // Add other fields as needed
  };
  signatories: {
    full_name: string;
    position: string;
  };
}>();


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
const showForApproveModal = ref(false)
const showRejectModal = ref(false)
const showReleaseModal = ref(false)
const showOrderModal = ref(false)
const showAlert = ref(true)

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
      showForApproveModal.value = false
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

const executiveDirector = computed(() => 
  props.signatories.find(s => s.position === 'Executive Director')
);

const printArea = () =>{
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

function goToCreate(poId?: number) {
  const url = poId ? `/vouchers/create?po_id=${poId}` : '/vouchers/create'
  router.visit(url)
}

function viewVoucher(poId: number) {
  router.visit(`/vouchers/by-po/${poId}`);
}
</script>

<template>
  <Head title="Purchase Order Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold">Purchase Order: {{ purchaseOrder.po_no }}</h1>
        <div class="space-x-2 flex space-x-2">
          <Button 
              v-if="authUser.role === 'accounting'"
              variant="outline" 
              size="sm"
              @click.stop="goToCreate(purchaseOrder.id)"
            >
              <Ticket />Create Voucher
          </Button>
          <!-- Approve Dialog -->
          <div v-if="authUser.role === 'executive_director'" class="space-x-2 flex space-x-2">
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
          </div>

          <div v-if="authUser.role === 'purchasing' && authUser.access === 3 && purchaseOrder.status === 'draft'" class="space-x-2 flex space-x-2">
            <Dialog v-model:open="showForApproveModal">
            <DialogTrigger as-child>
              <Button 
                variant="default" 
                size="sm" 
                :disabled="purchaseOrder.status === 'forEOD' || purchaseOrder.status === 'approved' || form.processing"
              >
                <Send /> Submit for EOD
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Submit Purchase Order for Approval</DialogTitle>
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
                  @click="submitStatusUpdate('forEOD')"
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
            <DialogTrigger>
              <Button 
                variant="outline"
                size="sm" 
                :disabled="purchaseOrder.status === 'forEOD' || purchaseOrder.status === 'approved' || form.processing"
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
          </div>

          <div class="flex items-center space-x-2">
            <Button size="sm" @click="printArea"><Printer />Print</Button>
            <Button variant="outline" size="sm"  as-child>
              <Link href="/purchase-orders">Back to List</Link>
            </Button>
          </div>
          <div class="flex items-center space-x-2" v-if="purchaseOrder.status === 'voucherCreated'">
            <Button size="sm" class="bg-green-600 hover:bg-green-700" @click="viewVoucher(purchaseOrder.id)">
              <ReceiptText />View Voucher
            </Button>
          </div>
        </div>
      </div>
        <Alert 
            v-if="showAlert && purchaseOrder.tagging === 'with_canvas'" 
            variant="warning" 
            class="relative pr-10"
          >
            <AlertCircle class="h-4 w-4" />
            <AlertTitle>Canvas Information</AlertTitle>
            <AlertDescription>
              <template v-if="purchaseOrder.canvas">
                <h1 class="capitalize">Filename: {{ purchaseOrder.canvas.original_filename }}</h1>
                <h1 class="capitalize">Remarks: {{ purchaseOrder.canvas.remarks }}</h1>
              </template>
              <template v-else>
                (Canvas details not available)
              </template>
            </AlertDescription>
            <!-- Dismiss Button -->
            <button
              class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
              @click="showAlert = false"
              aria-label="Dismiss"
            >
              <X class="h-4 w-4 text-yellow-700" />
            </button>
        </Alert>

      <!-- Allert Remarks -->
      <Alert 
        v-if="showAlert && purchaseOrder.remarks" 
        variant="success" 
        class="relative pr-10"
      >
        <BellRing class="h-4 w-4" />
        <AlertTitle>Remarks</AlertTitle>
        <AlertDescription>
          {{ purchaseOrder.remarks }}
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

      <div id="print-section">
      <div class="hidden print:block">
        <FormHeader text="Purchase Order" :bordered="false"  />
      </div>
      <div class="grid grid-cols-1 md:grid-cols-1">
        <table class="w-full text-sm border border-border rounded-md mb-2">
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
          </tbody>
        </table>
        <p class="text-xs italic hidden print:block">*Please deliver the following items immediately subject to the agreed terms and condition.</p>
      </div>

      <!-- Items Table -->
      <Table class="w-full text-sm border border-border rounded-md mt-2">
        <TableCaption>      
          <h3 class="flex items-center w-full mt-2">
            <span class="flex-grow border-t border-dashed border-gray-300"></span>
            <span class="mx-3 text-xs font-medium">Nothing Follows</span>
            <span class="flex-grow border-t border-dashed border-gray-300"></span>
          </h3>
        </TableCaption>
        <TableBody>
          <TableRow>
            <TableCell>Quantity</TableCell>
            <TableCell>Unit/S</TableCell>
            <TableCell>Description</TableCell>
            <TableCell class="text-right">Unit Price</TableCell>
            <TableCell class="text-right font-medium">Amount</TableCell>
          </TableRow>
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

      <div class="hidden print:block" >
        <div class="flex justify-between mt-12 items-center">
            <div class="text-left w-1/2">
              <div class="flex  items-center text-sm  space-x-[55px]">
                <p>For: {{ purchaseOrder.purpose || 'No purpose' }}</p>
              </div>
            </div>
        </div>

        <div class="flex justify-between mt-12 items-center">
            <div class="text-left w-1/2">
              <div class="text-sm">
                <p>PLEASE NOTE: All orders for equipment and supplies are made only on this form and signed by Executive Director.</p>
                <p>Orders made on other forms and Signed by other person's shall not be honored.</p>
              </div>
            </div>
            <div class="text-right w-1/2">
                <div class="inline-block text-sm border-black uppercase font-semibold">Arellano Law Foundation</div>
            </div>
        </div>

        <div class="flex justify-between mt-12 items-center">
            <div class="text-left w-1/2">
              <div class="flex  items-center text-sm uppercase space-x-[55px]">
                <h1>DEPARTMENT:</h1>
                <h1>{{ purchaseOrder.department.department_name }}</h1>
              </div>
              <div class="flex  items-center text-sm uppercase space-x-[10px]">
                <h1>ACCOUNT CHARGES:</h1>
                <h1>{{ purchaseOrder.account.account_title }}</h1>
              </div>
            </div>

          <div class="text-right w-1/2">
            <p class="text-xs mb-10 mr-[60px]">Approved By</p>
            
            <div v-if="executiveDirector" class="relative inline-block text-sm uppercase">
              <img
                v-if="purchaseOrder.status === 'approved'"
                src="/images/signatures/oed_signature.png"
                alt="Signature"
                class="w-[100px] absolute -top-6 left-1/2 -translate-x-1/2 pointer-events-none"
              />
              <div class="border-b border-black px-2">
                  {{ executiveDirector.full_name }}
                </div>
                <p class="text-xs mt-1 mr-[30px]">{{ executiveDirector.position }}</p>
              </div>
              
              <div v-else class="text-xs text-gray-500">
                No Executive Director assigned.
              </div>
            </div>
          </div>
          <p class="italic text-zinc-400 text-sm">{{ authUser.name }}</p>
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
    padding: 0.50rem !important;
  }
</style>