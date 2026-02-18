<script setup lang="ts">
import { ref, watch } from 'vue'
import { Input } from '@/components/ui/input'
import { formatDate } from '@/lib/utils'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  value: any
  itemId?: number
  field: string
  type?: 'text' | 'date' | 'number'
  pettyCashId?: number
}>()

const emit = defineEmits<{
  (e: 'update:value', value: any): void
  (e: 'saved'): void
}>()

const toast = useToast()
const isEditing = ref(false)
const editValue = ref(props.value)
const isSaving = ref(false)

watch(() => props.value, (newVal) => {
  editValue.value = newVal
})

const startEditing = () => {
  isEditing.value = true
  editValue.value = props.value
}

const saveEdit = async () => {
  if (editValue.value === props.value) {
    isEditing.value = false
    return
  }

  isSaving.value = true
  
  try {
    // Emit the change to parent first
    emit('update:value', editValue.value)
    emit('saved')
    isEditing.value = false
  } catch (error) {
    console.error('Failed to save:', error)
    toast.error('Failed to save changes')
    // Revert on error
    editValue.value = props.value
  } finally {
    isSaving.value = false
  }
}

const cancelEdit = () => {
  editValue.value = props.value
  isEditing.value = false
}

const getDisplayValue = () => {
  if (props.type === 'date') {
    return formatDate(props.value)
  }
  if (props.type === 'number') {
    return Number(props.value).toLocaleString()
  }
  return props.value
}
</script>

<template>
  <TableCell 
    @dblclick="startEditing" 
    class="cursor-pointer relative"
    :class="{ 'bg-muted/50': isEditing }"
  >
    <template v-if="isEditing">
      <div class="flex items-center gap-2">
        <Input
          v-model="editValue"
          :type="type || 'text'"
          :min="type === 'number' ? 0 : undefined"
          @blur="saveEdit"
          @keyup.enter="saveEdit"
          @keyup.escape="cancelEdit"
          class="w-full"
          :class="{ 'text-right': type === 'number' }"
          :disabled="isSaving"
          autofocus
        />
        <span v-if="isSaving" class="text-xs text-muted-foreground animate-pulse">
          Saving...
        </span>
      </div>
    </template>
    <template v-else>
      <span :class="{ 'font-medium': type === 'number' }">
        {{ getDisplayValue() }}
      </span>
    </template>
  </TableCell>
</template>