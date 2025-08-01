<script setup lang="ts">
import { ArrowLeft, SquarePen, Printer, Check, X, BadgeCheck } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import EodVerificationDialog from '@/components/vouchers/EodVerificationDialog.vue';
import DirectorVerificationDialog from '@/components/vouchers/DirectorVerificationDialog.vue';
import { router } from '@inertiajs/vue3';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import { ref } from 'vue'
import ReceiptUploadDialog from '@/components/vouchers/upload/ReceiptUploadDialog.vue';
import { Upload } from 'lucide-vue-next';
import AddCheckDialog from '@/components/vouchers/edit/AddCheckDialog.vue';
import { useToast } from 'vue-toastification'

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

</script>

<template>
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Voucher # {{ voucher.voucher_no }}</h2>
        </div>
        <div class="flex gap-2">
            <template v-if="authUser.role == 'accounting' && voucher.status !== 'draft' && voucher.status !== 'completed'">
                <ReceiptUploadDialog 
                :voucher-id="voucher.id"
                :current-issue-date="voucher.issue_date"
                :current-delivery-date="voucher.delivery_date"
                :current-remarks="voucher.remarks"
                :auth-user="authUser"
                @upload-success="handleUploadSuccess"
            />
            </template>

            <!-- Executive Director Actions -->
            <template v-if="authUser.role == 'executive_director' && authUser.access_id == '1' && voucher.status == 'forEOD'">
                <EodVerificationDialog :voucher-id="voucher.id" action="approve">
                    <template #trigger>
                        <Button 
                            :variant="voucher.status === 'forCheck' ? 'secondary' : 'default'"
                            :class="voucher.status === 'forCheck' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'forCheck'"
                        >
                            <Check class="h-4 w-4" />
                            <span>{{ voucher.status === 'forCheck' ? 'For Check Releasing' : 'Approve' }}</span>
                        </Button>
                    </template>
                </EodVerificationDialog>
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
                    v-if="authUser.role === 'accounting' && voucher.status == 'forEOD'"
                    variant="default"
                    @click.stop="goToEditVoucher(voucher.id, $event)" 
                    >
                    <SquarePen />
                    <span>Edit</span>
                </Button>
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'draft'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="forEod">
                    <template #trigger>
                        <Button 
                            :variant="voucher.status == 'forEOD' ? 'secondary' : 'default'"
                            :class="voucher.status == 'forEOD' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status == 'forEOD'"
                        >
                            <Check class="h-4 w-4" />
                            <span>{{ voucher.status == 'forEOD' ? 'Sent to EOD' : 'For EOD Approval' }}</span>
                        </Button>
                    </template>
                </DirectorVerificationDialog>
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status == 'unreleased'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="released">
                    <template #trigger>
                        <Button 
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
            <Button variant="outline" @click="emit('print')">
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