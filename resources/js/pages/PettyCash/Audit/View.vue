<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashView from '@/components/pettyCash/audit/PettyCashView.vue'
import { History, CheckCircle, } from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils'
import { Button } from '@/components/ui/button'
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'

const props = defineProps<{
  pettyCash: any
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
  { title: `Viewing ${props.pettyCash.pcv_no}`, href: '#' }
]
</script>

<template>
  <Head :title="`Edit Petty Cash - ${props.pettyCash.pcv_no}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between">
        <h1 class="text-xl font-bold">Review Petty Cash Voucher</h1>
        <Sheet>
          <SheetTrigger as-child>
            <Button variant="outline">
              <History class="mr-2 h-4 w-4" /> Time Stamp
            </Button>
          </SheetTrigger>
          <SheetContent class="w-[400px] sm:w-[540px]">
            <SheetHeader>
              <SheetTitle>Approval History</SheetTitle>
              <SheetDescription>
                <h3 class="text-sm font-medium text-muted-foreground mb-2">
                  Petty Cash Voucher
                </h3>
                <div class="mb-3">
                  <h4 class="text-muted-foreground">{{ props.pettyCash.pcv_no }}</h4>
                  <p>Created At: {{ formatDateTime(props.pettyCash.created_at) }}</p>
                </div>

                <div v-if="props.pettyCash.approvals?.length">
                  <div class="relative pl-6">
                    <!-- Timeline line -->
                    <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>

                    <div
                      v-for="(approval, index) in props.pettyCash.approvals"
                      :key="approval.id"
                      class="relative mb-6 last:mb-0"
                    >
                      <!-- Circle icon -->
                      <div
                        class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10"
                      >
                        <CheckCircle class="h-5 w-5 text-white" />
                      </div>

                      <!-- Connector line -->
                      <div
                        v-if="index < props.pettyCash.approvals.length - 1"
                        class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
                      ></div>

                      <!-- Content -->
                      <div class="pl-4">
                        <div class="flex items-center justify-between">
                          <div class="flex items-center gap-2">
                            <span class="capitalize">
                              {{ approval.user?.username || 'Unknown' }}
                            </span>
                            <span class="text-xs px-2 py-0.5 rounded bg-gray-100">
                              {{ approval.status }}
                            </span>
                          </div>
                          <span class="text-xs text-muted-foreground">
                            {{ formatDateTime(approval.created_at) }}
                          </span>
                        </div>

                        <div class="mt-1 flex items-start gap-2">
                          <p class="text-xs text-muted-foreground">
                            "{{ approval.remarks || 'No remarks' }}"
                          </p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </SheetDescription>
            </SheetHeader>
          </SheetContent>
        </Sheet>
      </div>
      <div class="rounded-xl border p-4">
        <PettyCashView :petty-cash="props.pettyCash" />
      </div>
    </div>
  </AppLayout>
</template>