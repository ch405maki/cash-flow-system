<script setup lang="ts">
import { reactive, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { useToast } from 'vue-toastification'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { Checkbox } from '@/components/ui/checkbox'
import { Undo2 } from 'lucide-vue-next'
import {
  Dialog,
  DialogTrigger,
  DialogContent,
  DialogHeader,
  DialogFooter,
  DialogTitle,
  DialogDescription
} from '@/components/ui/dialog'

const toast = useToast()

const props = defineProps<{
  pettyCashId: string | number
  userRole: string
  hasLiquidation: boolean
}>()

const emit = defineEmits(['approval-submitted'])

// Dialog states
const normalApprovalDialogOpen = ref(false)
const returnApprovalDialogOpen = ref(false)
const liquidationDialogOpen = ref(false)

const confirmApproval = ref(false)
const confirmLiquidation = ref(false)

const approval = reactive({
  remarks: ''
})

const handleApproval = async (type: 'normal' | 'liquidation' | 'return') => {
  try {
    if (type === 'normal') {
      await submitApproval()
      confirmApproval.value = false
      normalApprovalDialogOpen.value = false
    } else if (type === 'liquidation') {
      await submitApprovalLiquidate()
      confirmLiquidation.value = false
      liquidationDialogOpen.value = false
    } else {
      await submitReturn()
      confirmApproval.value = false
      returnApprovalDialogOpen.value = false
    }
    emit('approval-submitted')
  } catch (error) {
    toast.error('Approval failed')
  }
}

const submitApproval = async () => {
  await router.post(`/petty-cash/${props.pettyCashId}/approve`, {  
    remarks: approval.remarks
  })
  approval.remarks = ''
}

const submitReturn = async () => {
  await router.post(`/petty-cash/${props.pettyCashId}/return`, {  
    remarks: approval.remarks
  })
  toast.success('Return submitted!')
  approval.remarks = ''
}

const submitApprovalLiquidate = async () => {
  await router.post(`/petty-cash/${props.pettyCashId}/approveLiquidate`, {  
    remarks: approval.remarks
  })
  toast.success('Liquidation approval submitted!')
  approval.remarks = ''
}

// Clear remarks when dialog closes
const handleDialogClose = (dialogType: 'normal' | 'liquidation' | 'return') => {
  approval.remarks = ''
  if (dialogType === 'normal') {
    confirmApproval.value = false
  } else if (dialogType === 'liquidation') {
    confirmLiquidation.value = false
  } else {
    confirmApproval.value = false
  }
}
</script>

<template>
  <div class="mt-6 border rounded-lg p-4" v-if="userRole === 'audit'">
    <div class="flex justify-end space-x-2">
      <!-- Normal Approval -->
      <Dialog v-if="!hasLiquidation" v-model:open="normalApprovalDialogOpen">
        <DialogTrigger as-child>
          <Button>
            Confirm Approval
          </Button>
        </DialogTrigger>

        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Approval</DialogTitle>
            <DialogDescription>
              Please confirm that you've reviewed this petty cash request before approving.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4 mt-4">
            <div>
              <Label for="approvalRemarksDialog">Remarks (Optional)</Label>
              <Textarea
                id="approvalRemarksDialog"
                v-model="approval.remarks"
                rows="3"
                class="block w-full border rounded-md p-2 mt-1"
                placeholder="Add optional remarks for this approval..."
              ></Textarea>
            </div>

            <div class="flex items-center space-x-2">
              <Checkbox id="confirmApproval" v-model:checked="confirmApproval" />
              <Label for="confirmApproval">I confirm the accuracy of this request.</Label>
            </div>
          </div>

          <DialogFooter class="mt-4">
            <Button variant="outline" @click="handleDialogClose('normal'); normalApprovalDialogOpen = false">Cancel</Button>
            <Button :disabled="!confirmApproval" @click="handleApproval('normal')">
              Approve
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Return for Review -->
      <Dialog v-model:open="returnApprovalDialogOpen">
        <DialogTrigger as-child>
          <Button variant="secondary">
            <Undo2 />Return For Review
          </Button>
        </DialogTrigger>

        <DialogContent>
          <DialogHeader>
            <DialogTitle>Return Voucher for Review</DialogTitle>
            <DialogDescription>
              Are you sure you want to return this petty cash voucher for further review?  
              This action will notify the respective department to revise the details.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4 mt-4">
            <div>
              <Label for="returnRemarksDialog">Remarks</Label>
              <Textarea
                id="returnRemarksDialog"
                v-model="approval.remarks"
                rows="3"
                class="block w-full border rounded-md p-2 mt-1"
                placeholder="Please provide remarks explaining why this voucher needs revision..."
                required
              ></Textarea>
            </div>

            <div class="flex items-center space-x-2">
              <Checkbox id="confirmReturn" v-model:checked="confirmApproval" />
              <Label for="confirmReturn">
                I confirm I've reviewed this voucher and it needs revision.
              </Label>
            </div>
          </div>

          <DialogFooter class="mt-4">
            <Button variant="outline" @click="handleDialogClose('return'); returnApprovalDialogOpen = false">Cancel</Button>
            <Button :disabled="!confirmApproval || !approval.remarks" @click="handleApproval('return')">
              Confirm Return
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>

      <!-- Liquidation Approval -->
      <Dialog v-if="hasLiquidation" v-model:open="liquidationDialogOpen">
        <DialogTrigger as-child>
          <Button>
            Confirm Liquidation Approval
          </Button>
        </DialogTrigger>

        <DialogContent>
          <DialogHeader>
            <DialogTitle>Confirm Liquidation Approval</DialogTitle>
            <DialogDescription>
              This petty cash request contains liquidation items. Please confirm before final approval.
            </DialogDescription>
          </DialogHeader>

          <div class="space-y-4 mt-4">
            <div>
              <Label for="liquidationRemarksDialog">Remarks (Optional)</Label>
              <Textarea
                id="liquidationRemarksDialog"
                v-model="approval.remarks"
                rows="3"
                class="block w-full border rounded-md p-2 mt-1"
                placeholder="Add optional remarks for this liquidation approval..."
              ></Textarea>
            </div>

            <div class="flex items-center space-x-2">
              <Checkbox id="confirmLiquidation" v-model:checked="confirmLiquidation" />
              <Label for="confirmLiquidation">I confirm liquidation details are correct.</Label>
            </div>
          </div>

          <DialogFooter class="mt-4">
            <Button variant="outline" @click="handleDialogClose('liquidation'); liquidationDialogOpen = false">Cancel</Button>
            <Button :disabled="!confirmLiquidation" @click="handleApproval('liquidation')">
              Approve
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>