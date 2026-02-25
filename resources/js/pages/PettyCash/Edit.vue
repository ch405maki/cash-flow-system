<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashEditForm from '@/components/pettyCash/PettyCashEditForm.vue'
import PageHeader from '@/components/PageHeader.vue';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import { Button } from '@/components/ui/button'
import { History, CircleCheck } from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils' // Make sure to import this

const props = defineProps<{
  pettyCash: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
  { title: `Edit ${props.pettyCash.pcv_no}`, href: '#' }
]
</script>

<template>
  <Head :title="`Edit Petty Cash - ${props.pettyCash.pcv_no}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
      <!-- Header with title and timestamp button -->
      <div class="flex items-center justify-between">
        <PageHeader 
          title="Edit Petty Cash Voucher" 
          :subtitle="`Update details for ${props.pettyCash.pcv_no}`"
        />
        
        <!-- Timestamp/History Button -->
        <Sheet>
          <SheetTrigger as-child>
            <Button>
              <History class="h-4 w-4" /> Time Stamp
            </Button>
          </SheetTrigger>
          <SheetContent class="w-[400px] sm:w-[540px] overflow-y-auto">
            <SheetHeader>
              <SheetTitle>Approval History</SheetTitle>
              <SheetDescription>
                <div class="mt-4">
                  <h3 class="text-sm font-medium text-muted-foreground mb-2">
                    Petty Cash Voucher
                  </h3>
                  <div class="mb-4 p-3 bg-muted/30 rounded-lg">
                    <h4 class="font-medium">{{ props.pettyCash.pcv_no }}</h4>
                    <p class="text-sm text-muted-foreground">
                      Created: {{ formatDateTime(props.pettyCash.created_at) }}
                    </p>
                    <p class="text-sm text-muted-foreground">
                      Last Updated: {{ formatDateTime(props.pettyCash.updated_at) }}
                    </p>
                  </div>

                  <h3 class="text-sm font-medium text-muted-foreground mb-3">
                    Approval Timeline
                  </h3>

                  <div v-if="props.pettyCash.approvals?.length" class="relative pl-6">
                    <!-- Timeline line -->
                    <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>

                    <div
                      v-for="(approval, index) in props.pettyCash.approvals"
                      :key="approval.id"
                      class="relative mb-6 last:mb-0"
                    >
                      <!-- Circle icon -->
                      <div
                        class="absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10"
                        :class="{
                          'bg-green-500 border-2 border-green-500': approval.status === 'approved',
                          'bg-red-500 border-2 border-red-500': approval.status === 'rejected',
                          'bg-blue-500 border-2 border-blue-500': approval.status === 'pending' || !approval.status,
                          'bg-yellow-500 border-2 border-yellow-500': approval.status === 'returned'
                        }"
                      >
                        <CircleCheck class="h-5 w-5 text-white" />
                      </div>

                      <!-- Connector line -->
                      <div
                        v-if="index < props.pettyCash.approvals.length - 1"
                        class="absolute -left-6 top-8 h-full w-0.5 ml-4"
                        :class="{
                          'bg-green-500': approval.status === 'approved',
                          'bg-red-500': approval.status === 'rejected',
                          'bg-blue-500': approval.status === 'pending' || !approval.status,
                          'bg-yellow-500': approval.status === 'returned'
                        }"
                      ></div>

                      <!-- Content -->
                      <div class="pl-4">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-2">
                            <span class="font-medium capitalize">
                              {{ approval.user?.username || 'Unknown User' }}
                            </span>
                            <span 
                              class="text-xs px-2 py-0.5 rounded-full"
                              :class="{
                                'bg-green-100 text-green-700': approval.status === 'approved',
                                'bg-red-100 text-red-700': approval.status === 'rejected',
                                'bg-blue-100 text-blue-700': approval.status === 'pending' || !approval.status,
                                'bg-yellow-100 text-yellow-700': approval.status === 'returned'
                              }"
                            >
                              {{ approval.status || 'pending' }}
                            </span>
                          </div>
                          <span class="text-xs text-muted-foreground">
                            {{ formatDateTime(approval.created_at) }}
                          </span>
                        </div>

                        <div class="mt-1 flex items-start gap-2">
                          <p class="text-xs text-muted-foreground italic">
                            "{{ approval.remarks || 'No remarks provided' }}"
                          </p>
                        </div>

                        <!-- Show role if available -->
                        <p class="text-xs text-muted-foreground mt-1">
                          {{ approval.user?.role || 'User' }}
                        </p>
                      </div>
                    </div>
                  </div>

                  <!-- No approvals yet -->
                  <div v-else class="text-center py-8 text-muted-foreground">
                    <History class="h-12 w-12 mx-auto mb-3 opacity-50" />
                    <p>No approval history yet</p>
                    <p class="text-sm">This voucher is still pending review</p>
                  </div>
                </div>
              </SheetDescription>
            </SheetHeader>
          </SheetContent>
        </Sheet>
      </div>

      <!-- Edit Form -->
      <PettyCashEditForm :petty-cash="props.pettyCash" />
    </div>
  </AppLayout>
</template>