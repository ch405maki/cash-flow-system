<script setup lang="ts">
import { ref } from 'vue'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Button } from '@/components/ui/button'

const props = defineProps<{
  open: boolean
  title: string
  description: string
  confirmLabel?: string
  loading?: boolean
}>()

const emit = defineEmits<{
  (e: 'update:open', value: boolean): void
  (e: 'confirm', password: string): void
}>()

const password = ref('')

function handleConfirm() {
  const pwd = password.value
  password.value = ''
  emit('update:open', false)
  emit('confirm', pwd)
}
</script>

<template>
  <Dialog :open="open" @update:open="emit('update:open', $event)">
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
