<script setup lang="ts">
import { formatDateTime } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { ReceiptText, BadgeCheck } from 'lucide-vue-next'

defineProps<{
  voucher?: {
    voucher_no?: string
    status?: string
    created_at?: string
    approvals?: Array<{
      id: number
      remarks?: string
      created_at?: string
      user?: { username?: string }
    }>
  } | null
}>()
</script>

<template>
  <div class="flex items-center space-x-2">
    <Popover>
      <PopoverTrigger>
        <Button size="sm" class="bg-green-600 hover:bg-green-700 capitalize">
          <ReceiptText /> Voucher
        </Button>
      </PopoverTrigger>
      <PopoverContent class="max-w-sm">
        <div class="grid gap-2">
          <div class="space-y-2">
            <h4 class="leading-none text-sm font-medium">Voucher Information</h4>
            <p class="text-xs text-muted-foreground">Details of the voucher linked to this purchase order.</p>
          </div>

          <div class="text-xs space-y-1">
            <div>
              <span class="font-medium">Voucher No: </span>
              <span class="text-green-700 underline">{{ voucher?.voucher_no || 'N/A' }}</span>
            </div>
            <div>
              <span class="font-medium">Status: </span>
              <span class="text-green-700 underline capitalize">{{ voucher?.status || 'N/A' }}</span>
            </div>
            <div>
              <span class="font-medium">Created At: </span>
              <span class="text-green-700 underline capitalize">{{ formatDateTime(voucher?.created_at) }}</span>
            </div>
          </div>

          <div v-if="voucher" class="text-xs">
            <h3 class="font-medium py-2">Voucher History</h3>
            <div class="max-h-48 overflow-y-auto pr-2">
              <div v-if="voucher?.approvals?.length">
                <div class="relative pl-6">
                  <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
                  <div
                    v-for="(approval, index) in voucher.approvals"
                    :key="approval.id"
                    class="relative mb-6 last:mb-0"
                  >
                    <div class="bg-green-500 border-2 border-green-500 absolute -left-3.5 top-0 h-4 w-4 rounded-full flex items-center justify-center z-10">
                      <BadgeCheck class="h-3 w-3 text-white" />
                    </div>
                    <div
                      v-if="index < voucher.approvals.length - 1"
                      class="absolute -left-[7px] top-4 h-[calc(100%+8px)] w-0.5 bg-green-500 z-0"
                    ></div>
                    <div class="pl-4">
                      <span class="capitalize">{{ approval.user?.username || 'Unknown' }}</span>
                      <p class="text-xs text-muted-foreground mt-1 italic">"{{ approval.remarks || 'No remarks' }}"</p>
                      <p class="text-xs text-right text-muted-foreground">- {{ formatDateTime(approval.created_at) }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </PopoverContent>
    </Popover>
  </div>
</template>