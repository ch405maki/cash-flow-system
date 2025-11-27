<script setup lang="ts">
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { Rocket } from 'lucide-vue-next'

defineProps<{
  pettyCash: any
}>()
</script>

<template>
  <Alert>
    <Rocket class="h-4 w-4" />
    <AlertTitle>Remarks!</AlertTitle>
    <AlertDescription>{{ pettyCash.remarks || 'No remarks yet.' }}</AlertDescription>
  </Alert>
</template>