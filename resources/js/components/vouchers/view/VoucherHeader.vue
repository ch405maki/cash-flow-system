<script setup lang="ts">
import { ArrowLeft, SquarePen, Printer, Check, X, BadgeCheck, History, Clock, CheckCircle, } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import AuditVerificationDialog from '@/components/vouchers/AuditVerificationDialog.vue';
import DirectorVerificationDialog from '@/components/vouchers/DirectorVerificationDialog.vue';
import { router } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ref } from 'vue'
import ReceiptUploadDialog from '@/components/vouchers/upload/ReceiptUploadDialog.vue';
import { Upload } from 'lucide-vue-next';
import AddCheckDialog from '@/components/vouchers/edit/AddCheckDialog.vue';
import { useToast } from 'vue-toastification'
import { formatDateTime } from '@/lib/utils'
import PageHeader from '@/components/PageHeader.vue';
import {
  Sheet,
  SheetContent,
  SheetDescription,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
} from '@/components/ui/sheet'

defineProps({
    voucher: {
        type: Object,
        required: true
    },
    authUser: {
        type: Object,
        required: true
    }
});

const toast = useToast();
const emit = defineEmits(['print', 'check-updated']);

function goToEditVoucher(voucherId, e) {
  e.preventDefault()
  router.get(route("vouchers.edit", voucherId))
}
</script>

<template>
    <div class="flex justify-between items-center">
        <PageHeader 
            :title="`Voucher # ${ voucher.voucher_no }`" 
            subtitle="Review and approve voucher"
        />
        <div class="flex space-x-2">
            <template v-if="authUser.role == 'accounting' && voucher.status !== 'draft' && voucher.status !== 'completed' && voucher.status !== 'forAudit'">
                <ReceiptUploadDialog 
                :voucher-id="voucher.id"
                :current-issue-date="voucher.issue_date"
                :current-delivery-date="voucher.delivery_date"
                :current-remarks="voucher.remarks"
                :auth-user="authUser"
                @upload-success="handleUploadSuccess"
            />
            </template>

            <!-- Auditing Actions -->
            <template v-if="authUser.role == 'audit' && authUser.access_id == '2' && voucher.status == 'forAudit'">
                <AuditVerificationDialog :voucher-id="voucher.id" action="approve">
                    <template #trigger>
                        <Button 
                            size="sm"
                            :variant="voucher.status === 'forCheck' ? 'secondary' : 'default'"
                            :class="voucher.status === 'forCheck' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'forCheck'"
                        >
                            <Check class="h-4 w-4" />
                            <span>{{ voucher.status === 'forCheck' ? 'For Check Releasing' : 'Approve' }}</span>
                        </Button>
                    </template>
                </AuditVerificationDialog>
            </template>

            <!-- Accounting Actions -->
            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'forCheck'">
                <AddCheckDialog
                    :voucher-id="voucher.id"
                    :current-check-no="voucher.check_no"
                    :current-check-date="voucher.check_date"
                    :auth-user="authUser"
                    @saved="$emit('check-updated', $event)"
                    />
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'draft'">
                <Button
                    size="sm"
                    v-if="authUser.role === 'accounting' && voucher.status == 'draft'"
                    variant="default"
                    @click.stop="goToEditVoucher(voucher.id, $event)" 
                    >
                    <SquarePen />
                    <span>Edit</span>
                </Button>
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'draft'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="forAudit">
                    <template #trigger>
                        <Button 
                            size="sm"
                            :variant="voucher.status == 'forAudit' ? 'secondary' : 'default'"
                            :class="voucher.status == 'forAudit' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status == 'forAudit'"
                        >
                            <Check class="h-4 w-4" />
                            <span>{{ voucher.status == 'forAudit' ? 'Sent to EOD' : 'For Audit Review' }}</span>
                        </Button>
                    </template>
                </DirectorVerificationDialog>
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'unreleased'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="released">
                    <template #trigger>
                        <Button 
                            size="sm"
                            variant="default"
                            :class="voucher.status === 'released' ? 'opacity-50 cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'released'"
                        >
                            <BadgeCheck class="h-4 w-4" />
                            Tag as released
                        </Button>
                    </template>
                </DirectorVerificationDialog>
            </template>
            
            <Sheet>
                <SheetTrigger><Button variant="outline" size="sm"><History />Time Stamp</Button></SheetTrigger>
                <SheetContent>
                <SheetHeader>
                    <SheetTitle>Time Stamp</SheetTitle>
                    <SheetDescription>
                    <!-- Approval History -->
                    <h3 class="text-sm font-medium text-muted-foreground">Voucher History</h3>
                    <div class="mb-3">
                        <h4 class="text-muted-foreground">{{ voucher.voucher_no }}</h4>
                        <p>Created At: {{ formatDateTime(voucher.created_at) }}</p>
                    </div>
                    <div v-if="voucher.approvals?.length">
                    <div class="relative pl-6">
                        <div class="absolute left-0 top-0 h-full w-0.5 bg-gray-200 ml-4"></div>
                        <div
                        v-for="(approval, index) in voucher.approvals"
                        :key="approval.id"
                        class="relative mb-6 last:mb-0"
                        >
                        <div
                            class="bg-green-500 border-2 border-green-500 absolute -left-6 top-0 h-8 w-8 rounded-full flex items-center justify-center z-10"
                        >
                            <component
                            :is="approval.approved ? CheckCircle : CheckCircle"
                            class="h-5 w-5 text-white"
                            />
                        </div>

                        <div
                            v-if="index < voucher.approvals.length - 1"
                            class="absolute -left-6 top-8 h-full w-0.5 ml-4 bg-green-500 z-0"
                        ></div>

                        <div class="pl-4">
                            <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="capitalize">{{ approval.user?.username || 'Unknown' }}</span>
                            </div>
                            <span class="text-xs text-muted-foreground">
                                {{ formatDateTime(approval.created_at) }}
                            </span>
                            </div>

                            <div class="mt-1 flex items-start gap-2">
                            <p class="text-sm text-xs text-muted-foreground">
                                "{{ approval.remarks || 'No remarks' }}."
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

            <Button size="sm" variant="outline" @click="emit('print')">
                <Printer class="h-4 w-4" />
                Print
            </Button>
        </div>
    </div>
    
    <!-- Alert Remarks -->
    <Alert 
        v-if="showAlert && voucher.remarks" 
        variant="success" 
        class="relative pr-10"
    >
        <BellRing class="h-4 w-4" />
        <AlertTitle>Remarks</AlertTitle>
        <AlertDescription>
            {{ voucher.remarks }}
        </AlertDescription>
        <!-- Dismiss Button -->
        <button
            class="absolute right-2 top-2 text-sm text-muted-foreground hover:text-foreground"
            @click="showAlert = false"
            aria-label="Dismiss"
        >
            <X class="h-4 w-4 text-purple-700" />
        </button>
    </Alert>
</template>