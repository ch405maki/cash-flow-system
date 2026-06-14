<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, onMounted, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Printer, History, BadgeCheck, Rocket, Send, ArrowLeft, CheckCircle } from 'lucide-vue-next';
import { formatDate, formatDateTime } from '@/lib/utils'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    } from '@/components/ui/dialog'
import PageHeader from '@/components/PageHeader.vue';
import { Skeleton } from '@/components/ui/skeleton'
import { requestToOrderService } from '@/services/requestToOrderService';
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
import StatusBadge from '@/components/StatusBadge.vue';
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  orderId: number
}>()

const toast = useToast()

const loading = ref(true)
const requestOrder = ref<any>(null)
const authUser = ref<any>(null)
const password = ref('')
const showApproveModal = ref(false)
const showForEODModal = ref(false)
const processing = ref(false)
const printableComponent = ref<InstanceType<typeof PrintableSection> | null>(null)

onMounted(async () => {
  try {
    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
    authUser.value = response.data.authUser
  } catch (error) {
    console.error('Failed to load order:', error)
  } finally {
    loading.value = false
  }
})

async function submitApproval() {
  if (!password.value) return
  processing.value = true

  try {
    await requestToOrderService.approve(props.orderId, password.value)
    toast.success('Request approved successfully')
    showApproveModal.value = false
    password.value = ''

    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
  } catch (error: any) {
    if (error.response?.data?.errors?.password) {
      toast.error(error.response.data.errors.password)
    } else {
      toast.error('Something went wrong.')
    }
  } finally {
    processing.value = false
  }
}

async function submitForEOD() {
  if (!password.value) return
  processing.value = true

  try {
    await requestToOrderService.forEod(props.orderId, password.value)
    toast.success('Request sent for EOD approval successfully')
    showForEODModal.value = false
    password.value = ''

    const response = await requestToOrderService.showData(props.orderId)
    requestOrder.value = response.data.requestOrder
  } catch (error: any) {
    if (error.response?.data?.errors?.password) {
      toast.error(error.response.data.errors.password)
    } else {
      toast.error('Something went wrong.')
    }
  } finally {
    processing.value = false
  }
}

function printArea() {
  if (printableComponent.value) {
    printableComponent.value.printArea()
  }
}

function goBack() {
  window.history.back()
}

const breadcrumbs = computed(() => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: `Order ${requestOrder.value?.order_no || ''}`, href: '#' },
])
</script>

<template>
  <Head :title="`Order ${requestOrder?.order_no || '...'}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-4 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-32 w-full" />
      <Skeleton class="h-64 w-full" />
    </div>

    <template v-else-if="requestOrder">
      <div class="p-4 space-y-4">
        <div class="flex justify-between items-center">
          <PageHeader 
            title="Request To Order Details" 
            subtitle="View order information, status, and item specifications"
          />
          <div class="space-x-2 flex items-center">
            <div v-if="authUser?.role == 'executive_director'" class="space-x-2 flex items-center">
              <Dialog v-model:open="showApproveModal">
                <DialogTrigger as-child>
                    <Button
                    variant="default"
                    size="sm"
                    :disabled="requestOrder.status == 'forPO' || processing"
                    >
                    <BadgeCheck />Approve For Purchasing
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
                        v-model="password"
                        type="password"
                        placeholder="Enter password"
                        class="w-full"
                    />
                    </div>
                    <DialogFooter>
                    <Button
                        @click="submitApproval"
                        :disabled="!password || processing"
                    >
                        <span v-if="processing">Processing...</span>
                        <span v-else>Confirm Approval</span>
                    </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            </div>
            <div v-if="authUser?.role === 'property_custodian' && authUser?.access == 3 && requestOrder.status === 'pending'" class="space-x-2 flex items-center">
              <Dialog v-model:open="showForEODModal">
                <DialogTrigger as-child>
                    <Button
                    variant="default"
                    size="sm"
                    :disabled="requestOrder.status == 'forEOD' || processing"
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
                        v-model="password"
                        type="password"
                        placeholder="Enter password"
                        class="w-full"
                    />
                    </div>
                    <DialogFooter>
                    <Button
                        @click="submitForEOD"
                        :disabled="!password || processing"
                    >
                        <span v-if="processing">Processing...</span>
                        <span v-else>Confirm</span>
                    </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
            </div>
            <div v-if="authUser?.role === 'property_custodian'">
              <Button 
                v-if="requestOrder.status === 'forPO'"
                size="sm"
                @click="router.visit(`/request-to-order/${requestOrder.id}/release`)"
              >
                <Rocket />Receive Items
              </Button>
            </div>

            <Sheet>
              <SheetTrigger>
                <Button variant="outline" size="sm"><History />Time Stamp</Button></SheetTrigger>
              <SheetContent>
              <SheetHeader>
                  <SheetTitle>Time Stamp</SheetTitle>
                  <SheetDescription>
                  <h3 class="text-sm font-medium text-muted-foreground">Request History</h3>
                  <div class="mb-3">
                      <h4 class="text-muted-foreground">{{ requestOrder.order_no }}</h4>
                      <p>Created At: {{ formatDateTime(requestOrder.created_at) }}</p>
                  </div>
                  <div v-if="requestOrder.approvals?.length">
                  <div class="relative pl-6">
                      <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
                      <div
                      v-for="(approval, index) in requestOrder.approvals"
                      :key="approval.id"
                      class="relative mb-6 last:mb-0"
                      >
                      <div
                          class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10"
                      >
                          <component
                          :is="approval.approved ? CheckCircle : CheckCircle"
                          class="h-5 w-5 text-white"
                          />
                      </div>

                      <div
                          v-if="index < requestOrder.approvals.length - 1"
                          class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
                      ></div>

                      <div class="pl-4">
                          <div class="flex items-center justify-between">
                          <div class="flex items-center gap-2">
                              <span class="capitalize">{{ approval.user?.username || 'Unknown' }}</span>
                          </div>
                          <span class="text-xs text-muted-foreground">
                              {{ formatDateTime(approval.created_at) }}
                          </span>
                          </div>

                          <div class="mt-1 flex items-start gap-2">
                          <p class="text-sm text-xs text-muted-foreground">
                              "{{ approval.remarks || 'No remarks' }}."
                          </p>
                          </div>
                      </div>
                      </div>
                  </div>
                  </div>
                  </SheetDescription>
              </SheetHeader>
              </SheetContent>
          </Sheet>
            <Button size="sm" variant="default" @click="printArea"> <Printer />Print List</Button>
            <Button size="sm" @click="goBack" variant="outline"> <ArrowLeft />Back</Button>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-1">
          <Table>
            <TableBody>
              <TableRow>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">
                  Order No:
                </TableCell>
                <TableCell class="p-2 uppercase border-r w-xl">
                  {{ requestOrder.order_no }}
                </TableCell>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">
                  Order Date:
                </TableCell>
                <TableCell class="p-2 w-40">
                  {{ formatDate(requestOrder.order_date) }}
                </TableCell>
              </TableRow>
              <TableRow>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-48 uppercase">
                  Notes:
                </TableCell>
                <TableCell class="p-2 uppercase border-r w-xl">
                  {{ requestOrder.notes }}
                </TableCell>
                <TableCell class="p-2 font-medium text-muted-foreground border-r w-40 uppercase">
                  Status:
                </TableCell>
                <TableCell class="p-2 w-40">
                  <StatusBadge 
                      :status="requestOrder.status"
                      show-icon
                      size="md"
                    />
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <PageHeader title="Order Items Details" />
          <Table>
            <TableCaption>      
                Items to purchase
            </TableCaption>
              <TableHeader class="bg-muted">
              <TableRow>
                  <TableHead class="border-r p-2 w-10">#</TableHead>
                  <TableHead class="border-r p-2">Item Description</TableHead>
                  <TableHead class="border-r p-2">Quantity</TableHead>
                  <TableHead class="border-r">Unit</TableHead>
              </TableRow>
              </TableHeader>
              <TableBody>
              <TableRow v-for="(detail, index) in requestOrder.details" :key="detail.id">
                  <TableCell class="border-b p-2">{{ index + 1 }}</TableCell>
                  <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                  <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                  <TableCell class="border p-2">{{ detail.unit }}</TableCell>
              </TableRow>
              </TableBody>
          </Table>

          <div v-if="requestOrder.details.some(d => d.releases?.length > 0)" class="mt-8">
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

          <PrintableSection ref="printableComponent" :request-order="requestOrder"/>
        </div>
      </div>
    </template>
  </AppLayout>
</template>
