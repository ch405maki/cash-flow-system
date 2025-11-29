<script setup lang="ts">
import { FileText } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'

interface Props {
  user: any
}

const emit = defineEmits(['create-petty-cash'])
</script>

<template>
  <div class="flex h-48 flex-col items-center justify-center rounded-xl border text-center">
    <FileText class="h-8 w-8 text-muted-foreground" />
    <p class="mt-2 text-sm text-muted-foreground">No petty cash found</p>
    <p class="text-xs text-muted-foreground mb-4">
      List of your petty cash will appear here
    </p>
    <Button v-if="user.is_petty_cash == 1" @click="$emit('create-petty-cash')">
      Create New Petty Cash
    </Button>
  </div>
</template>