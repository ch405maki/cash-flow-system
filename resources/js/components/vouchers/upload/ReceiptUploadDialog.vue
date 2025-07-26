<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref } from 'vue'
import { Loader2, Upload } from 'lucide-vue-next'

const props = defineProps({
  voucherId: {
    type: Number,
    required: true
  },
  currentIssueDate: {
    type: String,
    default: null
  },
  currentDeliveryDate: {
    type: String,
    default: null
  },
  currentRemarks: {
    type: String,
    default: null
  }
})

const emit = defineEmits(['upload-success'])

const toast = useToast()
const isOpen = ref(false)
const isLoading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)

const form = ref({
  issue_date: props.currentIssueDate,
  delivery_date: props.currentDeliveryDate,
  remarks: props.currentRemarks,
  receipt: null
})

const handleFileChange = (e: Event) => {
  const input = e.target as HTMLInputElement
  if (input.files && input.files[0]) {
    form.value.receipt = input.files[0]
  }
}

const resetForm = () => {
  form.value = {
    issue_date: props.currentIssueDate,
    delivery_date: props.currentDeliveryDate,
    remarks: props.currentRemarks,
    receipt: null
  }
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const uploadReceipt = async () => {
  if (!form.value.receipt) {
    toast.error('Please select a receipt file')
    return
  }

  isLoading.value = true

  try {
    const formData = new FormData()
    formData.append('receipt', form.value.receipt)
    formData.append('issue_date', form.value.issue_date || '')
    formData.append('delivery_date', form.value.delivery_date || '')
    formData.append('remarks', form.value.remarks || '')

    const response = await axios.post(`/api/vouchers/${props.voucherId}/receipt`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    toast.success(response.data.message)
    emit('upload-success', response.data.data)
    isOpen.value = false
    resetForm()
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.values(error.response.data.errors).forEach((msg) => {
        toast.error(msg[0])
      })
    } else {
      toast.error(error.response?.data?.message || 'Failed to upload receipt')
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button variant="outline" class="gap-2">
        <Upload class="h-4 w-4" />
        Upload Receipt
      </Button>
    </DialogTrigger>
    <DialogContent @pointer-down-outside="resetForm">
      <DialogHeader>
        <DialogTitle>Upload Receipt & Details</DialogTitle>
      </DialogHeader>
      
      <div class="grid gap-4 py-4">
        <div class="grid gap-2">
          <Label for="receipt">Receipt</Label>
          <Input 
            id="receipt" 
            ref="fileInput"
            type="file" 
            accept="image/*,.pdf"
            @change="handleFileChange"
          />
          <p class="text-sm text-muted-foreground">Supports JPG, PNG, PDF (Max 2MB)</p>
        </div>

        <div class="grid grid-cols-2 gap-4">
          <div class="grid gap-2">
            <Label for="issue_date">Issue Date</Label>
            <Input 
              id="issue_date" 
              type="date" 
              v-model="form.issue_date"
            />
          </div>
          <div class="grid gap-2">
            <Label for="delivery_date">Delivery Date</Label>
            <Input 
              id="delivery_date" 
              type="date" 
              v-model="form.delivery_date"
            />
          </div>
        </div>

        <div class="grid gap-2">
          <Label for="remarks">Remarks</Label>
          <Textarea
            id="remarks"
            v-model="form.remarks"
            placeholder="Enter remarks here"
            class="min-h-[100px]"
          />
        </div>
      </div>

      <div class="flex justify-end gap-2">
        <Button variant="outline" @click="() => { isOpen = false; resetForm(); }" :disabled="isLoading">
          Cancel
        </Button>
        <Button @click="uploadReceipt" :disabled="isLoading">
          <Loader2 v-if="isLoading" class="h-4 w-4 mr-2 animate-spin" />
          <Upload />
          <span>{{ isLoading ? 'Uploading...' : 'Upload' }}</span>
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>