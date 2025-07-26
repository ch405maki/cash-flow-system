<script setup lang="ts">
import { ChevronDown, ChevronRight } from 'lucide-vue-next'
import { ref } from 'vue'

interface Props {
  data: unknown
  depth?: number
  keyName?: string
}

const props = withDefaults(defineProps<Props>(), {
  depth: 0,
  keyName: undefined
})

const isObject = (value: unknown): value is Record<string, unknown> => {
  return typeof value === 'object' && value !== null && !Array.isArray(value)
}

const isArray = (value: unknown): value is unknown[] => {
  return Array.isArray(value)
}

const isPrimitive = (value: unknown): boolean => {
  return !isObject(value) && !isArray(value)
}

const expanded = ref(props.depth < 2) // Auto-expand first few levels
</script>

<template>
  <div class="font-mono text-sm">
    <div v-if="keyName" class="flex items-start">
      <button 
        v-if="!isPrimitive(data)"
        @click="expanded = !expanded"
        class="mr-1 mt-1 text-muted-foreground hover:text-foreground"
      >
        <component :is="expanded ? ChevronDown : ChevronRight" class="h-3 w-3" />
      </button>
      <span class="font-medium text-primary">{{ keyName }}:</span>
    </div>

    <div v-if="isPrimitive(data)" class="ml-4">
      <span v-if="typeof data === 'string'" class="text-green-600">"{{ data }}"</span>
      <span v-else class="text-blue-600">{{ String(data) }}</span>
    </div>

    <div v-else-if="isArray(data)">
      <div v-if="!expanded" @click="expanded = true" class="ml-4 cursor-pointer text-muted-foreground">
        [{{ data.length }} items]
      </div>
      <div v-else class="ml-4 border-l pl-4">
        <div v-for="(item, index) in data" :key="index" class="my-1">
          <JsonViewer :data="item" :depth="depth + 1" :key-name="`[${index}]`" />
        </div>
      </div>
    </div>

    <div v-else-if="isObject(data)">
      <div v-if="!expanded" @click="expanded = true" class="ml-4 cursor-pointer text-muted-foreground">
        { ... }
      </div>
      <div v-else class="ml-4 border-l pl-4">
        <div v-for="(value, key) in data" :key="key" class="my-1">
          <JsonViewer :data="value" :depth="depth + 1" :key-name="key" />
        </div>
      </div>
    </div>
  </div>
</template>