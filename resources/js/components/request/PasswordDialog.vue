<script setup lang="ts">
import { ref } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  modelValue: boolean
  title: string
  description: string
  confirmLabel?: string
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: boolean): void
  (e: 'confirm', password: string): void
}>()

const password = ref('')

function handleConfirm() {
  emit('confirm', password.value)
  password.value = ''
}
</script>

<template>
  <Dialog :open="modelValue" @update:open="$emit('update:modelValue', $event)">
    <DialogContent>
      <DialogHeader>
        <DialogTitle>{{ title }}</DialogTitle>
        <DialogDescription>{{ description }}</DialogDescription>
      </DialogHeader>
      <div class="space-y-2">
        <Label>Password</Label>
        <Input v-model="password" type="password" placeholder="Enter your password" />
      </div>
      <DialogFooter>
        <Button :disabled="!password || loading" @click="handleConfirm">
          <span v-if="loading">Processing...</span>
          <span v-else>{{ confirmLabel || 'Confirm' }}</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
