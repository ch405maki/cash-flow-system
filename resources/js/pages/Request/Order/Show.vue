<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Printer, ListChecks } from 'lucide-vue-next';
  import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    } from '@/components/ui/dialog'
import {
  Table,
  TableCaption,
  TableHeader,
  TableBody,
  TableRow,
  TableHead,
  TableCell,
} from '@/components/ui/table'
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ref } from 'vue'
import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useForm } from '@inertiajs/vue3'

function goBack() {
  window.history.back()
}

function getStatusVariant(status: string) {
  switch (status.toLowerCase()) {
    case 'pending':
      return 'secondary'
    case 'approved':
      return 'success'
    case 'rejected':
      return 'destructive'
    default:
      return 'default'
  }
}

const { requestOrder, authUser } = usePage().props
const toast = useToast()

const password = ref('')
const showApproveModal = ref(false)
const showRejectModal = ref(false)
const showRejectCustodianModal = ref(false)
const showForEODModal = ref(false)

const form = useForm({
  password: '',
})

async function submitApproval() {
  form.processing = true

  form.patch(route('request-to-order.approve', requestOrder.id), {
    onSuccess: (page) => {
      toast.success('Request approved successfully')
      showApproveModal.value = false
      form.reset('password')
    },
    onError: (errors) => {
      if (errors.password) {
        toast.error(errors.password)
      } else {
        toast.error('Something went wrong.')
      }
    },
    onFinish: () => {
      form.processing = false
    }
  })
}

async function submitForEOD() {
  form.processing = true

  form.patch(route('request-to-order.for-eod', requestOrder.id), {
    onSuccess: (page) => {
      toast.success('Request sent for EOD approval successfully')
      showForEODModal.value = false
      form.reset('password')
    },
    onError: (errors) => {
      if (errors.password) {
        toast.error(errors.password)
      } else {
        toast.error('Something went wrong.')
      }
    },
    onFinish: () => {
      form.processing = false
    }
  })
}

async function submitReject() {
  form.processing = true

  form.patch(route('request-to-order.reject', requestOrder.id), {
    onSuccess: (page) => {
      toast.success('Request rejected successfully')
      showRejectModal.value = false
      showRejectCustodianModal.value = false
      form.reset('password')
    },
    onError: (errors) => {
      if (errors.password) {
        toast.error(errors.password)
      } else {
        toast.error('Something went wrong.')
      }
    },
    onFinish: () => {
      form.processing = false
    }
  })
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
     { title: 'Request To Order', href: '/request-to-order' },
    { title: `Order ${requestOrder.order_no}`, href: `/request/order/${requestOrder.id}` }
]

function formatDate(dateStr: string): string {
  const date = new Date(dateStr)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: '2-digit'
  })
}

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
</script>

<template>
  <Head :title="`Order ${requestOrder.order_no}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">

      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Request To Order Details</h1>
        <div class="space-x-2 flex items-center">
          <!-- Approve Button with Dialog -->
          <div v-if="authUser.role == 'executive_director'" class="space-x-2 flex items-center">
            <Dialog v-model:open="showApproveModal">
              <DialogTrigger as-child>
                  <Button
                  variant="default"
                  size="sm"
                  :disabled="requestOrder.status == 'forPO' || form.processing"
                  >
                  Approve For Purchasing
                  </Button>
              </DialogTrigger>
              <DialogContent>
                  <DialogHeader>
                  <DialogTitle>Password Verification</DialogTitle>
                  <DialogDescription>Enter your password to approve this request</DialogDescription>
                  </DialogHeader>
                  <div class="space-y-2">
                  <Label for="approve-password">Password</Label>
                  <Input
                      id="approve-password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter password"
                      class="w-full"
                  />
                  </div>
                  <DialogFooter>
                  <Button
                      @click="submitApproval"
                      :disabled="!form.password || form.processing"
                  >
                      <span v-if="form.processing">Processing...</span>
                      <span v-else>Confirm Approval</span>
                  </Button>
                  </DialogFooter>
              </DialogContent>
          </Dialog>
          <Dialog v-model:open="showRejectModal">
              <DialogTrigger as-child>
                  <Button
                  variant="destructive"
                  size="sm"
                  :disabled="requestOrder.status == 'rejected' || form.processing"
                  >
                  Reject
                  </Button>
              </DialogTrigger>
              <DialogContent>
                  <DialogHeader>
                  <DialogTitle>Password Verification</DialogTitle>
                  <DialogDescription>Enter your password to approve this request</DialogDescription>
                  </DialogHeader>
                  <div class="space-y-2">
                  <Label for="approve-password">Password</Label>
                  <Input
                      id="approve-password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter password"
                      class="w-full"
                  />
                  </div>
                  <DialogFooter>
                  <Button
                      @click="submitReject"
                      :disabled="!form.password || form.processing"
                  >
                      <span v-if="form.processing">Processing...</span>
                      <span v-else>Confirm Reject Request</span>
                  </Button>
                  </DialogFooter>
              </DialogContent>
          </Dialog>
          </div>
          <!-- for property -->
          <div v-if="authUser.role === 'property_custodian' && authUser.access == 3" class="space-x-2 flex items-center">
            <Dialog v-model:open="showForEODModal">
              <DialogTrigger as-child>
                  <Button
                  variant="default"
                  size="sm"
                  :disabled="requestOrder.status == 'forEOD' || form.processing"
                  >
                  For EOD Approval
                  </Button>
              </DialogTrigger>
              <DialogContent>
                  <DialogHeader>
                  <DialogTitle>Password Verification</DialogTitle>
                  <DialogDescription>Enter your password to approve this request</DialogDescription>
                  </DialogHeader>
                  <div class="space-y-2">
                  <Label for="approve-password">Password</Label>
                  <Input
                      id="approve-password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter password"
                      class="w-full"
                  />
                  </div>
                  <DialogFooter>
                  <Button
                      @click="submitForEOD"
                      :disabled="!form.password || form.processing"
                  >
                      <span v-if="form.processing">Processing...</span>
                      <span v-else>Confirm</span>
                  </Button>
                  </DialogFooter>
              </DialogContent>
          </Dialog>
          <!-- reject -->
           <Dialog v-model:open="showRejectCustodianModal">
              <DialogTrigger as-child>
                  <Button
                  variant="destructive"
                  size="sm"
                  :disabled="requestOrder.status == 'forEOD' || form.processing"
                  >
                  Reject
                  </Button>
              </DialogTrigger>
              <DialogContent>
                  <DialogHeader>
                  <DialogTitle>Password Verification</DialogTitle>
                  <DialogDescription>Enter your password to approve this request</DialogDescription>
                  </DialogHeader>
                  <div class="space-y-2">
                  <Label for="approve-password">Password</Label>
                  <Input
                      id="approve-password"
                      v-model="form.password"
                      type="password"
                      placeholder="Enter password"
                      class="w-full"
                  />
                  </div>
                  <DialogFooter>
                  <Button
                      @click="submitReject"
                      :disabled="!form.password || form.processing"
                  >
                      <span v-if="form.processing">Processing...</span>
                      <span v-else>Confirm Reject Request</span>
                  </Button>
                  </DialogFooter>
              </DialogContent>
          </Dialog>
          </div>
          <Button v-if="authUser.role === 'purchasing'" size="sm" @click="printArea"> <ListChecks />Mark As Done</Button>
          <Button size="sm" @click="printArea"> <Printer />Print List</Button>
          <Button size="sm" @click="goBack" variant="outline">Back</Button>
        </div>
      </div>

      <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
        <table class="w-full text-sm border border-border rounded-md">
          <tbody>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">Order No:</td>
              <td class="p-2 uppercase border-r w-xl">{{ requestOrder.order_no }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">Order Date:</td>
              <td class="p-2 w-40">
                {{ formatDate(requestOrder.order_date) }}
              </td>
            </tr>
            <tr class="border-b">
              <td class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">Notes:</td>
              <td class="p-2 uppercase border-r w-xl">{{ requestOrder.notes }}</td>
              <td class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">Status:</td>
              <td class="p-2 w-40">
                <Badge :variant="getStatusVariant(requestOrder.status)" class="capitalize">
                  {{ requestOrder.status }}
                </Badge>
              </td>
            </tr>
          </tbody>
        </table>

        <div>
          <div class="hidden print:block">
              <FormHeader text="Requisition Form" :bordered="false" />
          </div>
          <h2 class="text-xl font-semibold my-4">Order Details</h2>
          <div>
          <Table>
            <TableCaption>      
              <h3 class="flex items-center w-full mt-2">
                <span class="flex-grow border-t border-dashed border-zinc-300"></span>
                <span class="mx-3 text-xs font-medium">Items to order</span>
                <span class="flex-grow border-t border-dashed border-zinc-300"></span>
              </h3>
            </TableCaption>
              <TableHeader class="bg-muted">
              <TableRow>
                  <TableHead class="border p-2 w-10">#</TableHead>
                  <TableHead class="border p-2">Item Description</TableHead>
                  <TableHead class="border p-2">Quantity</TableHead>
                  <TableHead class="border p-2">Unit</TableHead>
              </TableRow>
              </TableHeader>
              <TableBody>
              <TableRow v-for="(detail, index) in requestOrder.details" :key="detail.id">
                  <TableCell class="border p-2">{{ index + 1 }}</TableCell>
                  <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                  <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                  <TableCell class="border p-2">{{ detail.unit }}</TableCell> 
              </TableRow>
              </TableBody>
          </Table>
          </div>
        </div>

        <!-- Printed Item -->
        <div id="print-section">
          <!-- hidden print:block  -->
            <div class="hidden print:block">
              <header :class="`flex items-center mb-2 mr-[50px] max-w-full justify-center mx-auto`">
                <figure class="shrink-0 mr-4">
                  <img 
                    src="/images/logo/logo.png" 
                    class="w-10 h-auto" 
                    alt="ALF Logo"
                    width="96"
                    height="96"
                  >
                </figure>

                <div class="text-center">
                  <h1 class="font-bold text-sm uppercase tracking-widest">
                    Arellano Law Foundation
                  </h1>
                  <address class="text-xs tracking-wide">
                    <p>Taft Ave, Cor. Menlo St. Pasay City</p>
                  </address>
                </div>
              </header>
              <div class="space-y-0">
                <!-- Main Table -->
                <div class="grid grid-cols-[50px_60px_minmax(200px,1fr)_80px_80px] text-xs border-x border-t border-zinc-500 w-full">
                  <!-- Header Row -->
                  <div class="col-span-3 p-0.5 border-b border-r border-zinc-500 text-xs text-center font-bold uppercase tracking-widest bg-white h-6 leading-tight">
                    Requisition Form
                  </div>
                  <div class="col-span-2 p-0.5 border-b border-zinc-500 text-xs font-semibold bg-white h-6 leading-tight">
                    Date: {{ formatDate(requestOrder.order_date) }}
                  </div>

                  <!-- Column Headers -->
                  <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Qty</div>
                  <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Unit</div>
                  <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Articles/Description</div>
                  <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Unit Price</div>
                  <div class="col-span-1 p-0.5 border-b border-zinc-500 text-center bg-zinc-100 font-medium h-6 leading-tight uppercase">Amount</div>

                  <!-- Item Rows -->
                  <template v-for="(detail, index) in requestOrder.details">
                    <div v-if="index < 10" :key="detail.id" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center hover:bg-zinc-50 h-5 leading-tight">
                      {{ detail.quantity }}
                    </div>
                    <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center hover:bg-zinc-50 h-5 leading-tight">
                      {{ detail.unit }}
                    </div>
                    <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 truncate px-1 hover:bg-zinc-50 h-5 leading-tight capitalize">
                      {{ detail.item_description }}
                    </div>
                    <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-right pr-1 hover:bg-zinc-50 h-5 leading-tight">
                      -
                    </div>
                    <div v-if="index < 10" class="col-span-1 p-0.5 border-b border-zinc-500 text-right pr-1 hover:bg-zinc-50 h-5 leading-tight">
                      -
                    </div>
                  </template>

                  <!-- Empty Rows -->
                  <template v-for="index in Math.max(0, 10 - requestOrder.details.length)">
                    <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center h-5 leading-tight">-</div>
                    <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-center h-5 leading-tight">-</div>
                    <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 px-1 h-5 leading-tight">-</div>
                    <div class="col-span-1 p-0.5 border-b border-r border-zinc-500 text-right pr-1 h-5 leading-tight">-</div>
                    <div class="col-span-1 p-0.5 border-b border-zinc-500 text-right pr-1 h-5 leading-tight">-</div>
                  </template>
                </div>

                <!-- Purpose/Received Section -->
                <div class="grid grid-cols-5 text-xs border-x border-b border-zinc-500">
                  <div class="col-span-3 p-2 border-r border-zinc-500 align-top">
                    <div class="font-semibold">Purpose:</div>
                    <div class="text-sm">{{ requestOrder.notes }}</div>
                  </div>
                  <div class="col-span-2 p-2 h-20 align-top">
                    <div class="font-semibold text-xs">Items Received by:</div>
                    <div class="flex-1 flex flex-col justify-end">
                      <div class="text-center mt-8 border-t border-zinc-500 mx-8">
                        <div class="text-xs text-zinc-600 mt-1">Signature over printed name</div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Signatures Section -->
                <div class="grid grid-cols-6 text-xs border-x border-b border-zinc-500">
                  <div class="col-span-3 p-2 border-r border-zinc-500 align-top">
                    <div class="font-semibold text-xs">Requested By:</div>
                    <div class="flex-1 flex flex-col justify-end">
                      <div class="text-center mt-8 border-t border-zinc-500 mx-8">
                        <div class="text-xs text-zinc-600 mt-1">Department Head</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-span-3 p-2 h-22 align-top">
                    <div class="font-semibold text-xs">Approved:</div>
                    <div class="flex-1 flex flex-col justify-end">
                      <div class="text-center mt-8 border-t border-zinc-500 mx-8">
                        <div class="text-xs text-zinc-600 mt-1 uppercase">Atty. Gabriel P. Dela PeÑa<br>
                          Executive Director
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
        </div>
        <!-- End Print Item -->
      </div>
    </div>
  </AppLayout>
</template>
