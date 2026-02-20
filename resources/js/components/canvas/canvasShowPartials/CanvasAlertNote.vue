<script setup lang="ts">
import { ref } from 'vue';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert';
import { X } from 'lucide-vue-next';

defineProps<{
  note?: string
}>();

const showAlert = ref(true);
</script>

<template>
  <Alert 
    v-if="showAlert && note" 
    variant="warning" 
    class="relative pr-10"
  >
    <AlertCircle class="h-4 w-4" />
    <AlertTitle>Note!</AlertTitle>
    <AlertDescription>
      <h1 class="capitalize">{{ note }}</h1>
    </AlertDescription>
    <button
      class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
      @click="showAlert = false"
      aria-label="Dismiss"
    >
      <X class="h-4 w-4 text-yellow-700" />
    </button>
  </Alert>
</template>