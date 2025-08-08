<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref } from 'vue'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Printer, ListChecks, Rocket, Send, ArrowLeft, CircleCheck } from 'lucide-vue-next';
import { formatDate, formatDateTime } from '@/lib/utils'
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
import PrintableSection from '@/components/printables/OrderPrint.vue'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
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
    default:
      return 'default'
  }
}

const { requestOrder, authUser } = usePage().props
const toast = useToast()

const password = ref('')
const showApproveModal = ref(false)
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

// Add a ref for the printable component
const printableComponent = ref<InstanceType<typeof PrintableSection> | null>(null)

// Update your printArea function
function printArea() {
  if (printableComponent.value) {
    printableComponent.value.printArea()
  }
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
     { title: 'Request To Order', href: '/request-to-order' },
    { title: `Order ${requestOrder.order_no}`, href: `/request/order/${requestOrder.id}` }
]
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
          </div>
          <!-- for property -->
          <div v-if="authUser.role === 'property_custodian' && authUser.access == 3 && requestOrder.status === 'pending'" class="space-x-2 flex items-center">
            <Dialog v-model:open="showForEODModal">
              <DialogTrigger as-child>
                  <Button
                  variant="default"
                  size="sm"
                  :disabled="requestOrder.status == 'forEOD' || form.processing"
                  >
                  <Send />For EOD Approval
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
          </div>
          <div v-if="authUser.role === 'purchasing'" >
            <Button 
              v-if="requestOrder.status === 'forPO'"
              size="sm"
              @click="$inertia.visit(route('request-to-order.release.create', requestOrder.id))"
            >
              <Rocket />  Release Items
            </Button>
          </div>
          <Button size="sm" variant="outline" @click="printArea"> <Printer />Print List</Button>
          <Button size="sm" @click="goBack" variant="outline"> <ArrowLeft />Back</Button>
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

        <div v-if="requestOrder.details.some(d => d.releases.length > 0)" class="mt-8">
          <h2 class="text-xl font-semibold mb-4">Release History</h2>
          <div class="border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead class="w-[120px]">Date</TableHead>
                  <TableHead>Item</TableHead>
                  <TableHead class="text-right">Quantity</TableHead>
                  <TableHead>Released By</TableHead>
                  <TableHead>Notes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <template v-for="detail in requestOrder.details" :key="`detail-${detail.id}`">
                  <TableRow 
                    v-for="release in detail.releases" 
                    :key="`release-${release.id}`"
                    class="hover:bg-muted/50"
                  >
                    <TableCell class="font-medium w-48">
                      {{ formatDateTime(release.created_at) }}
                    </TableCell>
                    <TableCell>
                      {{ detail.item_description }}
                    </TableCell>
                    <TableCell class="text-right">
                      {{ release.quantity_released }} {{ detail.unit }}
                    </TableCell>
                    <TableCell class="w-48">
                      {{ release.released_by ? `${release.released_by.first_name} ${release.released_by.last_name}` : 'N/A' }}
                    </TableCell>
                    <TableCell class="capitalize">
                      {{ release.notes || 'No notes' }}
                    </TableCell>
                  </TableRow>
                </template>
              </TableBody>
            </Table>
          </div>
        </div>

        <!-- Printed Item -->
          <PrintableSection ref="printableComponent" :request-order="requestOrder"/>
        <!-- End Print Item -->
      </div>
    </div>
  </AppLayout>
</template>
