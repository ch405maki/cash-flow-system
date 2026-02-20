<script setup lang="ts">
import { FileText } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
  Empty,
  EmptyContent,
  EmptyDescription,
  EmptyHeader,
  EmptyMedia,
  EmptyTitle,
} from '@/components/ui/empty'

interface Props {
  user: any
}

const emit = defineEmits(['create-petty-cash'])
</script>

<template>
  <Empty>
    <EmptyHeader>
      <EmptyMedia variant="icon">
        <FileText />
      </EmptyMedia>
      <EmptyTitle>No petty cash found</EmptyTitle>
      <EmptyDescription>
        List of your petty cash will appear here.
      </EmptyDescription>
    </EmptyHeader>
    <EmptyContent>
      <div class="flex gap-2">
        <Button
          @click="$emit('create-petty-cash')"
        >
          Create New Petty Cash
        </Button>
      </div>
    </EmptyContent>
  </Empty>
</template>