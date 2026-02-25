<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button'
import { Info, SquarePen } from 'lucide-vue-next'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

interface Props {
  user: any
  thresholds: any
  pettyCashFund?: any
  fundStatus: any
  hasItem: boolean
  // New props for tabs
  activeTab: string
  uniqueStatuses: string[]
  statusCounts: Record<string, number>
  totalCount: number
}

const props = defineProps<Props>()
const emit = defineEmits(['create-petty-cash', 'update:activeTab'])

const goToCreate = () => {
  emit('create-petty-cash')
}

const handleTabChange = (value: string) => {
  emit('update:activeTab', value)
}

// Status color mapping for tabs
const getStatusTabColor = (status: string) => {
  const colorMap: Record<string, string> = {
    'draft': 'text-gray-600',
    'requested': 'text-blue-600',
    'submitted': 'text-purple-600',
    'approved': 'text-green-600',
    'returned': 'text-orange-600',
    'rejected': 'text-red-600',
    'liquidation': 'text-indigo-600',
    'approved liquidation': 'text-teal-600',
    'released': 'text-emerald-600',
    'completed': 'text-green-700',
    'cancelled': 'text-red-700'
  }
  return colorMap[status] || 'text-gray-600'
}

// Check if tabs should be shown
const showTabs = computed(() => {
  return props.hasItem && props.uniqueStatuses.length > 0
})
</script>

<template>
  <div class="space-y-4">
    <!-- Header Section -->
    <div class="flex justify-between items-center">
      <PageHeader 
        title="Petty Cash" 
        subtitle="List of created petty cash."
      />

      <div class="flex items-center gap-3">
        <Popover v-if="user.role != 'bursar'">
          <PopoverTrigger as-child>
            <Button title="Daily Threshold Status" variant="outline" size="icon" class="h-9 w-9 border-orange-200 bg-orange-50 hover:bg-orange-100">
              <Info class="h-4 w-4 text-orange-600" />
            </Button>
          </PopoverTrigger>
          <PopoverContent>
            <slot name="threshold-content" />
          </PopoverContent>
        </Popover>
        
        <slot name="fund-balance" />
        
        <Button v-if="hasItem && user.is_petty_cash == 1" @click="goToCreate">
          <SquarePen />New Petty Cash
        </Button>
      </div>
    </div>

    <!-- Tabs Section - Directly under header -->
    <div v-if="showTabs" class="border-b border-border">
      <Tabs :model-value="activeTab" @update:model-value="handleTabChange" class="w-full">
        <TabsList class="w-full justify-end h-auto p-0 bg-transparent border-0 gap-1">
          <!-- All tab -->
          <TabsTrigger 
            value="all" 
            class="data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none bg-transparent px-4 py-2 text-muted-foreground data-[state=active]:text-foreground"
          >
            All
            <Badge variant="secondary" class="ml-2">
              {{ totalCount }}
            </Badge>
          </TabsTrigger>

          <!-- Dynamic status tabs -->
          <TabsTrigger 
            v-for="status in uniqueStatuses" 
            :key="status"
            :value="status"
            class="data-[state=active]:border-b-2 data-[state=active]:border-primary rounded-none bg-transparent px-4 py-2 text-muted-foreground data-[state=active]:text-foreground capitalize"
            :class="getStatusTabColor(status)"
          >
            {{ status }}
            <Badge variant="secondary" class="ml-2">
              {{ statusCounts[status] || 0 }}
            </Badge>
          </TabsTrigger>
        </TabsList>
      </Tabs>
    </div>
  </div>
</template>