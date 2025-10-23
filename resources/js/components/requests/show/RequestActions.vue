<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import { useForm, router } from '@inertiajs/vue3'
import PasswordDialog from './PasswordDialog.vue'
import { Button } from '@/components/ui/button'
import { Printer, FilePenLine, BadgeCheck, History, Rocket, Clock, CheckCircle, XCircle } from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import {
  Stepper,
  StepperDescription,
  StepperIndicator,
  StepperItem,
  StepperSeparator,
  StepperTitle,
  StepperTrigger,
} from '@/components/ui/stepper'
import { Check, Circle, Dot } from "lucide-vue-next"

const props = defineProps<{
  request: any
  user: any
  printableRef?: any
}>()

const toast = useToast()

// Modal states
const showApproveModal = ref(false)
const showReleaseModal = ref(false)
const showOrderModal = ref(false)
const showForRequestModal = ref(false)
const showRejectModal = ref(false) // Add reject modal

const form = useForm({
  status: '',
  password: ''
})

// Navigate functions
function navigateToEdit() {
  router.get(`/requests/${props.request.id}/release`)
}

function goToEditRequest(requestId: number) {
  router.get(`/requests/${requestId}/edit`)
}

// Status update function
function submitStatusUpdate(newStatus: string, password: string) {
  form.status = newStatus
  form.password = password

  form.patch(`/requests/${props.request.id}/status`, {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Status updated successfully')
      closeAllDialogs()
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

function closeAllDialogs() {
  showApproveModal.value = false
  showReleaseModal.value = false
  showForRequestModal.value = false
  showOrderModal.value = false
  showRejectModal.value = false // Close reject modal too
}

const emit = defineEmits(['print-list'])

function printList() {
  emit('print-list')
}
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Executive Director -->
    <div v-if="user.role === 'executive_director'" class="flex gap-2">
      <Button
        size="sm"
        :disabled="request.status === 'approved' || form.processing"
        @click="showApproveModal = true"
      >
        <BadgeCheck />Approve
      </Button>
      <PasswordDialog
        v-model="showApproveModal"
        title="Password Verification"
        description="Please enter your password to approve this request"
        confirm-label="Confirm Approval"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('approved', password)"
      />
      
      <!-- Reject Button for Executive Director -->
      <Button
        variant="destructive"
        size="sm"
        :disabled="request.status === 'rejected' || form.processing"
        @click="showRejectModal = true"
      >
        <XCircle />Reject
      </Button>
      <PasswordDialog
        v-model="showRejectModal"
        title="Password Verification"
        description="Please enter your password to reject this request"
        confirm-label="Confirm Rejection"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('rejected', password)"
      />
    </div>

    <!-- Property Custodian -->
    <div v-if="user.role === 'property_custodian'" class="flex gap-2">
      <Button
        variant="outline"
        size="sm"
        @click="navigateToEdit"
        :disabled="request.status == 'rejected' || request.status == 'released'"
      >
        <Rocket /> Release Item
      </Button>

      <Button
        size="sm"
        :disabled="request.status === 'to_order' || request.status == 'released' || form.processing"
        @click="showForRequestModal = true"
      >
        <FilePenLine />For Request To Purchase
      </Button>
      <PasswordDialog
        v-model="showForRequestModal"
        title="Password Verification"
        description="Please enter your password to send this order"
        confirm-label="Confirm Approval"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('to_order', password)"
      />
    </div>

    <!-- Department Head -->
    <div v-if="user.role === 'department_head'" class="flex gap-2">
      <Button
        size="sm"
        v-if="request.status === 'pending'"
        @click="showOrderModal = true"
      >
        <BadgeCheck />Approve
      </Button>
      <PasswordDialog
        v-model="showOrderModal"
        title="Password Verification"
        description="Please enter your password to send this order"
        confirm-label="Confirm Approval"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('propertyCustodian', password)"
      />
      
      <!-- Reject Button for Department Head -->
      <Button
        variant="destructive"
        size="sm"
        v-if="request.status === 'pending'"
        @click="showRejectModal = true"
      >
        <XCircle />Reject
      </Button>
      <PasswordDialog
        v-model="showRejectModal"
        title="Password Verification"
        description="Please enter your password to reject this request"
        confirm-label="Confirm Rejection"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('rejected', password)"
      />
    </div>

    <!-- Staff or Department Head - Edit -->
    <Button
      size="sm"
      variant="outline"
      v-if="(user.role === 'staff' || user.role === 'department_head') && request.status === 'pending'"
      @click.stop="goToEditRequest(request.id)"
    >
      <FilePenLine class="h-4" /> Edit
    </Button>

    <Sheet>
        <SheetTrigger><Button variant="outline" size="sm"><History />Time Stamp</Button></SheetTrigger>
        <SheetContent>
        <SheetHeader>
            <SheetTitle>Time Stamp</SheetTitle>
            <SheetDescription>
            <!-- Approval History -->
            <h3 class="text-sm font-medium text-muted-foreground">Request History</h3>
            <div class="mb-3">
                <h4 class="text-muted-foreground">{{ request.request_no }}</h4>
                <p>Created At: {{ formatDateTime(request.created_at) }}</p>
            </div>
            <div v-if="request.approvals?.length">
            <div class="relative pl-6">
                <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
                <div
                v-for="(approval, index) in request.approvals"
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
                    v-if="index < request.approvals.length - 1"
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

    <!-- Print List -->
    <Button size="sm" @click="printList">
      <Printer class="h-4" /> Print List
    </Button>
  </div>
</template>