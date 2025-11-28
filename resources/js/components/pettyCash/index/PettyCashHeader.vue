<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button'
import { Info, SquarePen  } from 'lucide-vue-next'
import {
  Popover,
  PopoverContent,
  PopoverTrigger,
} from '@/components/ui/popover'

interface Props {
  user: any
  thresholds: any
  pettyCashFund?: any
  fundStatus: any
  hasItem: boolean
}

const props = defineProps<Props>()
const emit = defineEmits(['create-petty-cash'])

const goToCreate = () => {
  emit('create-petty-cash')
}
</script>

<template>
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
</template>