<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, usePage } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
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

const { requestOrder } = usePage().props
const toast = useToast()

const password = ref('')
const showApproveModal = ref(false)
const showRejectModal = ref(false)

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

async function submitReject() {
  form.processing = true

  form.patch(route('request-to-order.reject', requestOrder.id), {
    onSuccess: (page) => {
      toast.success('Request rejected successfully')
      showRejectModal.value = false
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
</script>

<template>
  <Head :title="`Order ${requestOrder.order_no}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">

      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Request To Order Details</h1>
        
        <div class="space-x-2 flex items-center">
            <Button @click="goBack" variant="outline">Back</Button>
            <!-- Approve Button with Dialog -->
            <Dialog v-model:open="showApproveModal">
                <DialogTrigger as-child>
                    <Button
                    variant="default"
                    size="sm"
                    :disabled="requestOrder.status == 'approved' || form.processing"
                    >
                    Approve
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
                    variant="default"
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
      </div>

      <div class="space-y-2">
        <p><strong>Order No:</strong> {{ requestOrder.order_no }}</p>
        <p><strong>Order Date:</strong> {{ new Date(requestOrder.order_date).toLocaleDateString() }}</p>
        <p><strong>Notes:</strong> {{ requestOrder.notes }}</p>
        <p>
          <strong>Status:</strong>
          <Badge :variant="getStatusVariant(requestOrder.status)" class="capitalize">
            {{ requestOrder.status }}
          </Badge>
        </p>
      </div>

      <h2 class="text-xl font-semibold mt-6 mb-2">Order Details</h2>
      <Table>
        <TableCaption>Items in this order</TableCaption>
        <TableHeader>
            <TableRow>
            <TableHead>Item Description</TableHead>
            <TableHead>Quantity</TableHead>
            <TableHead>Unit</TableHead>
            <TableHead>Department</TableHead>
            <TableHead>Purpose</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="detail in requestOrder.details" :key="detail.id">
            <TableCell>{{ detail.item_description }}</TableCell>
            <TableCell>{{ detail.quantity }}</TableCell>
            <TableCell>{{ detail.unit }}</TableCell>
            <TableCell>{{ detail.request?.department?.department_name ?? 'N/A' }}</TableCell>
        <TableCell>{{ detail.request?.purpose ?? 'N/A' }}</TableCell>
            </TableRow>
        </TableBody>
        </Table>
    </div>
  </AppLayout>
</template>
