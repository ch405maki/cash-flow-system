<script setup lang="ts">
import { ref } from 'vue'
import { useToast } from 'vue-toastification'
import { useForm, router } from '@inertiajs/vue3'
import PasswordDialog from './PasswordDialog.vue'
import { Button } from '@/components/ui/button'
import { Printer, FilePenLine, History } from 'lucide-vue-next'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'


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
}

const emit = defineEmits(['print-list'])

function printList() {
  emit('print-list')
}
</script>

<template>
  <div class="flex items-center gap-2">
    <!-- Executive Director -->
    <div v-if="user.role === 'executive_director'">
      <Button
        size="sm"
        :disabled="request.status === 'approved' || form.processing"
        @click="showApproveModal = true"
      >
        Approve
      </Button>
      <PasswordDialog
        v-model="showApproveModal"
        title="Password Verification"
        description="Please enter your password to approve this request"
        confirm-label="Confirm Approval"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('approved', password)"
      />
    </div>

    <!-- Property Custodian -->
    <div v-if="user.role === 'property_custodian'" class="flex gap-2">
      <Button
        variant="secondary"
        size="sm"
        @click="navigateToEdit"
        :disabled="request.status == 'rejected' || request.status == 'released'"
      >
        Partial Release
      </Button>

      <Button
        size="sm"
        :disabled="request.status === 'released' || form.processing"
        @click="showReleaseModal = true"
      >
        Release All
      </Button>
      <PasswordDialog
        v-model="showReleaseModal"
        title="Password Verification"
        description="Please enter your password to release this request"
        confirm-label="Confirm Release"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('released', password)"
      />

      <Button
        size="sm"
        :disabled="request.status === 'to_order' || request.status == 'released' || form.processing"
        @click="showForRequestModal = true"
      >
        For Request To Order
      </Button>
      <PasswordDialog
        v-model="showForRequestModal"
        title="Password Verification"
        description="Please enter your password to send this order"
        confirm-label="Confirm Order"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('to_order', password)"
      />
    </div>

    <!-- Department Head -->
    <div v-if="user.role === 'department_head'">
      <Button
        size="sm"
        :disabled="request.status === 'propertyCustodian' || request.status === 'rejected' || form.processing"
        @click="showOrderModal = true"
      >
        Approve
      </Button>
      <PasswordDialog
        v-model="showOrderModal"
        title="Password Verification"
        description="Please enter your password to send this order"
        confirm-label="Confirm Order"
        :loading="form.processing"
        @confirm="(password) => submitStatusUpdate('propertyCustodian', password)"
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
                {{ request.approvals }}
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
