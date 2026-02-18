<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import {
  RadioGroup,
  RadioGroupItem
} from '@/components/ui/radio-group'
import { Send, Loader2 } from 'lucide-vue-next'
import { usePettyCash } from '@/composables/usePettyCash'

const props = defineProps<{
  existingItems: any[]
  status: string
  isCashAdvanceUser: boolean
  pettyCashId: number
}>()

const emit = defineEmits<{
  (e: 'item-added', item: any): void
}>()

const toast = useToast()
const { addItem: saveItem } = usePettyCash() // Rename the imported function
const fileKey = ref(0)
const isSubmitting = ref(false)

const newItem = reactive({
  type: '',
  particulars: '',
  date: '',
  liquidation_for_date: '',
  amount: 0,
  receipt: null as File | null
})

const handleAddItem = async () => { // Rename the function
  // Validation
  if (!newItem.type || !newItem.particulars || !newItem.date || !newItem.amount) {
    toast.warning('Please select type and fill all fields before adding.')
    return
  }

  if (newItem.type === 'Liquidation' && !newItem.receipt) {
    toast.error('Receipt attachment is required for Liquidation items.')
    return
  }

  if (newItem.type === 'Liquidation') {
    if (!newItem.liquidation_for_date) {
      toast.warning('Please select a Liquidation Dated value.')
      return
    }

    const normalizeToManilaDate = (dateStr: string | null | undefined) => {
      if (!dateStr) return null
      try {
        const d = new Date(dateStr)
        return d.toLocaleDateString('en-CA', { timeZone: 'Asia/Manila' })
      } catch (e) {
        return null
      }
    }

    const incomingNorm = normalizeToManilaDate(newItem.liquidation_for_date)
    const hasMatchingCashAdvance = props.existingItems.some((item) => {
      if (item.type !== 'Cash Advance') return false
      const itemDateNorm = normalizeToManilaDate(item.date)
      return itemDateNorm === incomingNorm
    })

    if (!hasMatchingCashAdvance) {
      toast.error(`No Cash Advance found for ${newItem.liquidation_for_date}. You cannot add a Liquidation for this date.`)
      return
    }
  }

  isSubmitting.value = true
  
  try {
    // Save to server first
    const itemData = { ...newItem }
    const savedItem = await saveItem(props.pettyCashId, itemData) // Use renamed import
    
    // Emit the saved item with ID
    emit('item-added', savedItem)
    
    // Reset form
    newItem.type = ''
    newItem.particulars = ''
    newItem.date = ''
    newItem.liquidation_for_date = ''
    newItem.amount = 0
    newItem.receipt = null
    fileKey.value++
    
    toast.success('Item added successfully!')
  } catch (error) {
    console.error('Failed to add item:', error)
    toast.error('Failed to add item. Please try again.')
  } finally {
    isSubmitting.value = false
  }
}
</script>

<template>
  <div class="space-y-3 border rounded-xl p-4">
    <h3 class="text-lg font-semibold">Add New Item</h3>

    <div>
      <Label>Type</Label>
      <RadioGroup v-model="newItem.type">
        <div v-if="status != 'for liquidation'" class="flex mt-2 items-center space-x-4">
          <div class="flex items-center space-x-2">
            <RadioGroupItem id="reimbursement" value="Reimbursement" />
            <Label for="reimbursement">Reimbursement</Label>
          </div>
          <div class="flex items-center space-x-4" v-if="isCashAdvanceUser">
            <div class="flex items-center space-x-2">
              <RadioGroupItem id="cash-advance" value="Cash Advance" />
              <Label for="cash-advance">Cash Advance</Label>
            </div>
          </div>
        </div>
        <div v-else class="flex mt-2 items-center space-x-4">
          <div class="flex items-center space-x-4" v-if="isCashAdvanceUser">
            <div class="flex items-center space-x-2">
              <RadioGroupItem id="liquidation" value="Liquidation" />
              <Label for="liquidation">Liquidation</Label>
            </div>
          </div>
        </div>
      </RadioGroup>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="col-span-4">
        <Label>Particulars</Label>
        <Textarea v-model="newItem.particulars" />
      </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div>
        <Label>Date</Label>
        <Input v-model="newItem.date" type="date" />
      </div>
      <div v-if="newItem.type === 'Liquidation'">
        <Label class="text-rose-600">Liquidation Dated</Label>
        <Input v-model="newItem.liquidation_for_date" type="date" />
      </div>
      <div>
        <Label>Amount</Label>
        <Input v-model.number="newItem.amount" type="number" min="0" />
      </div>
      <div>
        <Label>Receipt</Label>
        <input
          :key="fileKey"
          type="file"
          class="block w-full text-sm border rounded-lg p-2"
          @change="e => newItem.receipt = (e.target as HTMLInputElement).files?.[0] || null"
        />
      </div>
    </div>
    
    <div class="flex justify-end">
      <Button 
        variant="secondary" 
        @click="handleAddItem"
        :disabled="isSubmitting"
      >
        <Send v-if="!isSubmitting" class="mr-2 h-4 w-4" />
        <Loader2 v-else class="mr-2 h-4 w-4 animate-spin" />
        {{ isSubmitting ? 'Saving...' : 'Accept' }}
      </Button>
    </div>
  </div>
</template>