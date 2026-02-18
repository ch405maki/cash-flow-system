<script setup lang="ts">
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter
} from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'

const props = defineProps<{
  open: boolean
  title: string
  description: string
  confirmLabel?: string
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'confirm'): void
}>()

const confirmChecked = defineModel<boolean>('checked', { default: false })

const handleConfirm = () => {
  if (!confirmChecked.value) return
  emit('confirm')
}

const handleOpenChange = (value: boolean) => {
  if (!value) {
    confirmChecked.value = false
  }
  emit('update:open', value)
}
</script>

<template>
  <Dialog :open="open" @update:open="handleOpenChange">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>

      <div class="flex items-center space-x-2 mt-4">
        <Checkbox :id="title" v-model:checked="confirmChecked" />
        <Label :for="title">I confirm that all entries are correct.</Label>
      </div>

      <DialogFooter class="mt-4 flex justify-end space-x-2">
        <Button variant="outline" @click="handleOpenChange(false)">
          Cancel
        </Button>
        <Button 
          class="disabled:opacity-50 disabled:cursor-not-allowed" 
          :disabled="!confirmChecked" 
          @click="handleConfirm"
        >
          {{ confirmLabel || 'Confirm' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>