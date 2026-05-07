<script setup lang="ts">
import { formatDateTime } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Sheet, SheetContent, SheetDescription,
  SheetHeader, SheetTitle, SheetTrigger,
} from '@/components/ui/sheet'
import { History, CheckCircle } from 'lucide-vue-next'

defineProps<{
  poNo: string
  createdAt?: string
  approvals?: Array<{
    id: number
    approved?: boolean
    remarks?: string
    created_at?: string
    user?: { username?: string }
  }>
}>()
</script>

<template>
  <Sheet>
    <SheetTrigger>
      <Button variant="outline" size="sm"><History /> Time Stamp</Button>
    </SheetTrigger>
    <SheetContent>
      <SheetHeader>
        <SheetTitle>Time Stamp</SheetTitle>
        <SheetDescription>
          <h3 class="text-sm font-medium text-muted-foreground">Request History</h3>
          <div class="mb-3">
            <h4 class="text-muted-foreground">{{ poNo }}</h4>
            <p>Created At: {{ formatDateTime(createdAt) }}</p>
          </div>

          <div v-if="approvals?.length">
            <div class="relative pl-6">
              <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
              <div
                v-for="(approval, index) in approvals"
                :key="approval.id"
                class="relative mb-6 last:mb-0"
              >
                <div class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10">
                  <CheckCircle class="h-5 w-5 text-white" />
                </div>
                <div
                  v-if="index < approvals.length - 1"
                  class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
                ></div>
                <div class="pl-4">
                  <div class="flex items-center justify-between">
                    <span class="capitalize">{{ approval.user?.username || 'Unknown' }}</span>
                    <span class="text-xs text-muted-foreground">{{ formatDateTime(approval.created_at) }}</span>
                  </div>
                  <div class="mt-1">
                    <p class="text-xs text-muted-foreground">"{{ approval.remarks || 'No remarks' }}..."</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </SheetDescription>
      </SheetHeader>
    </SheetContent>
  </Sheet>
</template>