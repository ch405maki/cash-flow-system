<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashView from '@/components/pettyCash/audit/PettyCashView.vue'
import { Button } from '@/components/ui/button'
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogFooter,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogCancel,
  AlertDialogAction
} from '@/components/ui/alert-dialog'
import { Checkbox } from '@/components/ui/checkbox'
import { reactive, ref, computed } from 'vue'

const props = defineProps<{
    pettyCash: any,
    pettyCashFund: Number,
}>()

console.log(props.pettyCashFund)

const confirmChecked = ref(false)

const releasePettyCash = () => {
  const data = {
    reimbursement_total: reimbursementTotal.value,
    liquidation_total: liquidationTotal.value,
    grand_total: grandTotal.value,
  }

  router.put(
    route('bursar.petty-cash.release', props.pettyCash.id),
    data,
    {
      onSuccess: () => {
        confirmChecked.value = false
      }
    }
  )
}

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Petty Cash Voucher', href: '#' },
  { title: `Viewing ${props.pettyCash.pcv_no}`, href: '#' }
]

const reimbursementTotal = computed(() => {
  return props.pettyCash.items
    .filter((item: any) => item.type === 'Reimbursement')
    .reduce((sum: number, item: any) => sum + Number(item.amount || 0), 0)
})

const liquidationTotal = computed(() => {
  return props.pettyCash.items
    .filter((item: any) => item.type === 'Liquidation')
    .reduce((sum: number, item: any) => sum + Number(item.amount || 0), 0)
})

const grandTotal = computed(() => reimbursementTotal.value + liquidationTotal.value)
</script>

<template>
  <Head :title="`Edit Petty Cash - ${props.pettyCash.pcv_no}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Review Petty Cash Voucher</h1>
{{ props.pettyCashFund }}
        <!-- AlertDialog -->
        <AlertDialog v-if="props.pettyCash.status == 'approved'">
          <AlertDialogTrigger as-child>
            <Button>Release</Button>
          </AlertDialogTrigger>
          <AlertDialogContent>
            <AlertDialogHeader>
              <AlertDialogTitle>Release Petty Cash Voucher?</AlertDialogTitle>
              <AlertDialogDescription>
                This action will mark petty cash <b>{{ props.pettyCash.pcv_no }}</b> as <i>released</i>.  
                Please confirm before continuing.
              </AlertDialogDescription>
            </AlertDialogHeader>

            <!-- Checkbox confirm -->
            <div class="flex items-center space-x-2 my-4">
              <Checkbox v-model:checked="confirmChecked" id="confirm-release" />
              <label for="confirm-release" class="text-sm">I confirm release of this petty cash.</label>
            </div>

            <AlertDialogFooter>
              <AlertDialogCancel>Cancel</AlertDialogCancel>
              <AlertDialogAction
                :disabled="!confirmChecked"
                @click="releasePettyCash"
              >
                Confirm Release
              </AlertDialogAction>
            </AlertDialogFooter>
          </AlertDialogContent>
        </AlertDialog>
      </div>

      <div class="rounded-xl border p-4">
        <PettyCashView :petty-cash="props.pettyCash" />
      </div>
    </div>
  </AppLayout>
</template>
