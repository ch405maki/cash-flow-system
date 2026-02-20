<script setup lang="ts">
import { ref } from 'vue';
import { Badge } from '@/components/ui/badge';
import { CheckCircle, ChevronDown } from 'lucide-vue-next';
import { formatDateTime } from '@/lib/utils';

const props = defineProps<{
  approvals: any[]
}>();

const showAllApprovals = ref(false);
</script>

<template>
  <div v-if="approvals?.length">
    <h3 class="text-sm font-medium text-muted-foreground mb-3">History</h3>
    <div class="relative pl-6">
      <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>

      <!-- Show only the first item unless expanded -->
      <div
        v-for="(approval, index) in showAllApprovals 
          ? approvals 
          : approvals.slice(0, 1)"
        :key="approval.id"
        class="relative mb-6 last:mb-0"
      >
        <div
          class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10"
        >
          <CheckCircle class="h-5 w-5 text-white" />
        </div>

        <div
          v-if="index < (showAllApprovals ? approvals.length - 1 : 0)"
          class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
        ></div>

        <div class="pl-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="capitalize">{{ approval.user.username }}</span>
            </div>
            <span class="text-xs text-muted-foreground">
              {{ formatDateTime(approval.created_at) }}
            </span>
          </div>

          <div class="mt-1 flex items-start gap-2">
            <p class="text-sm">
              "{{ approval.comments || 'No comments' }}"
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Toggle Button -->
    <div v-if="approvals.length > 1" class="flex justify-center mt-2">
      <button
        class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition"
        @click="showAllApprovals = !showAllApprovals"
      >
        <span>
          {{ showAllApprovals ? "Show less" : `+${approvals.length - 1} more` }}
        </span>
        <ChevronDown
          class="h-4 w-4 ml-1 transition-transform"
          :class="{ 'rotate-180': showAllApprovals }"
        />
      </button>
    </div>
  </div>
</template>