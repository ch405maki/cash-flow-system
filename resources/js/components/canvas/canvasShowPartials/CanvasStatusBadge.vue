<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Pencil, ChevronRight, UserRoundCheck, CheckCircle, XCircle } from 'lucide-vue-next';

const props = defineProps<{
  status: string
}>();

const statusIcons = {
  draft: Pencil,
  submitted: ChevronRight,
  pending_approval: UserRoundCheck,
  approved: CheckCircle,
  rejected: XCircle,
  forEOD: CheckCircle,
};

const statusVariants = {
  draft: 'bg-gray-100 text-gray-800',
  submitted: 'bg-blue-100 text-blue-800',
  pending_approval: 'bg-purple-100 text-purple-800',
  approved: 'bg-green-100 text-green-800',
  rejected: 'bg-red-100 text-red-800',
  forEOD: 'bg-green-100 text-green-800',
};
</script>

<template>
  <Badge :class="statusVariants[status]" class="mt-1">
    <component 
      :is="statusIcons[status]" 
      class="h-3 w-3 mr-1" 
    />
    <span class="capitalize">{{ status.replace('_', ' ') }}</span>
  </Badge>
</template>