<script setup lang="ts">
interface Props {
  title: string;
  subtitle?: string;
  icon?: any;
}

defineProps<Props>();
</script>

<template>
  <div>
    <div class="flex items-center gap-2" v-if="icon">
      <component :is="icon" class="h-5 w-5 text-blue-600" />
      <h1 class="text-xl font-bold">{{ title }}</h1>
    </div>
    <h1 v-else class="text-xl font-bold">{{ title }}</h1>
    <p v-if="subtitle" class="text-sm text-muted-foreground mt-1">{{ subtitle }}</p>
  </div>
</template>