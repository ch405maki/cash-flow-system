<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button'
import { Info, SquarePen, Filter } from 'lucide-vue-next'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'
import {
  Select,
  SelectContent,
  SelectGroup,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Badge } from '@/components/ui/badge'
import { computed } from 'vue'

interface Props {
  user: any
  thresholds: any
  pettyCashFund?: any
  fundStatus: any
  hasItem: boolean
  activeFilter: string
  uniqueStatuses: string[]
  statusCounts: Record<string, number>
  totalCount: number
}

const props = defineProps<Props>()
const emit = defineEmits(['create-petty-cash', 'update:activeFilter'])

const goToCreate = () => {
  emit('create-petty-cash')
}

const handleFilterChange = (value: string) => {
  emit('update:activeFilter', value)
}

// Get display label for selected filter
const getFilterDisplay = computed(() => {
  if (!props.activeFilter || props.activeFilter === 'all') {
    return `All (${props.totalCount || 0})`
  }
  const count = props.statusCounts?.[props.activeFilter] || 0
  return `${props.activeFilter} (${count})`
})

// Check if filter should be shown
const showFilter = computed(() => {
  return props.hasItem && props.uniqueStatuses?.length > 0
})
</script>

<template>
  <div class="space-y-4">
    <!-- Header Section - Now with filter inline -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <PageHeader 
        title="Petty Cash" 
        subtitle="List of created petty cash."
      />

      <div class="flex items-center gap-3 w-full sm:w-auto">
        <!-- Filter Select - Moved here to be inline with buttons -->
        <div v-if="showFilter" class="w-full">
          <Select :model-value="activeFilter || 'all'" @update:model-value="handleFilterChange">
            <SelectTrigger class="w-full h-9 px-4">
              <div class="flex items-center gap-2">
                <Filter class="h-4 w-4 text-muted-foreground shrink-0" />
                <SelectValue>
                  {{ getFilterDisplay }}
                </SelectValue>
              </div>
            </SelectTrigger>
            <SelectContent>
              <SelectGroup>
                <!-- All option -->
                <SelectItem value="all" class="cursor-pointer">
                  <div class="flex items-center justify-between w-full gap-4">
                    <span>All</span>
                    <Badge variant="secondary" class="ml-2">
                      {{ totalCount || 0 }}
                    </Badge>
                  </div>
                </SelectItem>

                <!-- Dynamic status options -->
                <SelectItem 
                  v-for="status in uniqueStatuses" 
                  :key="status"
                  :value="status"
                  class="cursor-pointer capitalize"
                >
                  <div class="flex items-center justify-between w-full gap-4">
                    <span>{{ status }}</span>
                    <Badge variant="secondary" class="ml-2">
                      {{ statusCounts?.[status] || 0 }}
                    </Badge>
                  </div>
                </SelectItem>
              </SelectGroup>
            </SelectContent>
          </Select>
        </div>

        <Popover v-if="user.role != 'bursar'">
          <PopoverTrigger as-child>
            <Button title="Daily Threshold Status" variant="outline" size="icon" class="h-9 w-9 border-orange-200 bg-orange-50 hover:bg-orange-100 shrink-0">
              <Info class="h-4 w-4 text-orange-600" />
            </Button>
          </PopoverTrigger>
          <PopoverContent>
            <slot name="threshold-content" />
          </PopoverContent>
        </Popover>
        
        <slot name="fund-balance" />
        
        <Button v-if="hasItem && user.is_petty_cash == 1" @click="goToCreate" class="shrink-0">
          <SquarePen class="h-4 w-4 mr-2" />New Petty Cash
        </Button>
      </div>
    </div>
  </div>
</template>