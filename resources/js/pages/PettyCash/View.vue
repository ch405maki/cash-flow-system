<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { type BreadcrumbItem } from '@/types'
import PettyCashView from '@/components/pettyCash/audit/PettyCashView.vue'
import PettyCashPrint from '@/components/pettyCash/printables/PettyCash.vue'
import { Button } from '@/components/ui/button'
import { History, CheckCircle, } from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils'
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
import { useToast } from "vue-toastification";
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'
import { usePage } from '@inertiajs/vue3';

const toast = useToast();

const user = usePage().props.auth.user;
const props = defineProps<{
    pettyCash: any,
    pettyCashFund: Number,
}>()

const confirmChecked = ref(false)
const releaseRemarks = ref('')


const releasePettyCash = () => {
  const data = {
    reimbursement_total: reimbursementTotal.value,
    liquidation_total: liquidationTotal.value,
    grand_total: grandTotal.value,
    remarks: releaseRemarks.value,
  };

  router.put(
    route("bursar.petty-cash.release", props.pettyCash.id),
    data,
    {
      onSuccess: () => {
        confirmChecked.value = false;
        toast.success("Petty Cash released successfully");
      },
      onError: () => {
        toast.error("Failed to release Petty Cash");
      },
    }
  );
};

const releaseCashAdvance = () => {
  const data = {
    reimbursement_total: reimbursementTotal.value,
    liquidation_total: liquidationTotal.value,
    grand_total: cashAdvanceTotal.value,
    remarks: releaseRemarks.value,
  };

  router.put(
    route("bursar.cashAdvance.release", props.pettyCash.id),
    data,
    {
      onSuccess: () => {
        confirmChecked.value = false;
        toast.success("Cash Advance released successfully");
      },
      onError: () => {
        toast.error("Failed to release Cash Advance");
      },
    }
  );
};


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

const cashAdvanceTotal = computed(() => {
  return props.pettyCash.items
    .filter((item: any) => item.type === 'Cash Advance')
    .reduce((sum: number, item: any) => sum + Number(item.amount || 0), 0)
})

const liquidationTotal = computed(() => {
  return props.pettyCash.items
    .filter((item: any) => item.type === 'Liquidation')
    .reduce((sum: number, item: any) => sum + Number(item.amount || 0), 0)
})

const grandTotal = computed(() => reimbursementTotal.value + liquidationTotal.value)

// Print action
const printReport = () => {
  const printContents = document.getElementById('print-section')?.innerHTML;
  const originalContents = document.body.innerHTML;

  if (printContents) {
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  } else {
    console.error('Print section not found');
  }
}

console.log(props.pettyCash)
</script>

<template>
  <Head :title="`Edit Petty Cash - ${props.pettyCash.pcv_no}`" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Review Petty Cash Vouchers</h1>

        <div class="flex items-center space-x-2">
          <Button v-if="props.pettyCash.status == 'for liquidation'"  @click="router.get(route('petty-cash.edit', props.pettyCash.id))">
            Liquidate
          </Button>
          <!-- Approval History Button -->
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

        <div v-if="user.role == 'accounting'">
          <!-- Release button -->
          <AlertDialog v-if="props.pettyCash.status == 'requested'">
            <AlertDialogTrigger as-child>
              <Button>Tag Release</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Release Cash Advance?</AlertDialogTitle>
                <AlertDialogDescription>
                  This action will mark the request
                  <b>{{ props.pettyCash.pcv_no }}</b> as <i>to liquidate</i>.
                </AlertDialogDescription>
              </AlertDialogHeader>
              <div class="my-2">
                <label for="release-remarks" class="block text-sm font-medium mb-1">
                  Remarks (optional)
                </label>
                <textarea
                  id="release-remarks"
                  v-model="releaseRemarks"
                  rows="2"
                  class="w-full border rounded-md p-2 text-sm"
                  placeholder="Add any remarks for this release..."
                ></textarea>
              </div>

              <div class="flex items-center space-x-2 my-2">
                <Checkbox v-model:checked="confirmChecked" id="confirm-release" />
                <label for="confirm-release" class="text-sm">
                  I confirm release of this request.
                </label>
              </div>

              <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction
                  :disabled="!confirmChecked"
                  @click="releaseCashAdvance"
                >
                  Confirm Release
                </AlertDialogAction>
              </AlertDialogFooter>
            </AlertDialogContent>
          </AlertDialog>
        </div>

        <div v-if="user.role == 'bursar'">
          <!-- Release button -->
          <AlertDialog v-if="props.pettyCash.status == 'audited'">
            <AlertDialogTrigger as-child>
              <Button>Release</Button>
            </AlertDialogTrigger>
            <AlertDialogContent>
              <AlertDialogHeader>
                <AlertDialogTitle>Release Petty Cash Voucher?</AlertDialogTitle>
                <AlertDialogDescription>
                  This action will mark petty cash
                  <b>{{ props.pettyCash.pcv_no }}</b> as <i>released</i>.
                </AlertDialogDescription>
              </AlertDialogHeader>

              <div class="flex items-center space-x-2 my-4">
                <Checkbox v-model:checked="confirmChecked" id="confirm-release" />
                <label for="confirm-release" class="text-sm">
                  I confirm release of this petty cash.
                </label>
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
        <Button
          @click="printReport"
        >
          Print
        </Button>
        </div>
      </div>

      <div class="rounded-xl border p-4">
        <PettyCashView :petty-cash="props.pettyCash" />
        <!-- class="hidden print:block" -->
        <div id="print-section" class="hidden print:block">
          <PettyCashPrint :petty-cash="props.pettyCash" />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

