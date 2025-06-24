<script setup lang="ts">
import { ArrowLeft, Printer, Check, X } from 'lucide-vue-next';
import { Button } from '@/components/ui/button'
import EodVerificationDialog from '@/components/vouchers/EodVerificationDialog.vue';
import DirectorVerificationDialog from '@/components/vouchers/DirectorVerificationDialog.vue';

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

const emit = defineEmits(['print']);
</script>

<template>
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold tracking-tight">Voucher # {{ voucher.voucher_no }}</h2>
        </div>
        <div class="flex gap-2">
            <Button variant="outline" @click="emit('print')">
                <Printer class="h-4 w-4 mr-2" />
                Print
            </Button>

            <!-- Executive Director Actions -->
            <template v-if="authUser.role == 'executive_director' && authUser.access_id == '1' && voucher.status !== 'rejected'">
                <EodVerificationDialog :voucher-id="voucher.id" action="approve">
                    <template #trigger>
                        <Button 
                            :variant="voucher.status === 'forCheck' ? 'secondary' : 'default'"
                            :class="voucher.status === 'forCheck' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'forCheck'"
                        >
                            <Check class="h-4 w-4 mr-2" />
                            <span>{{ voucher.status === 'forCheck' ? 'For Check Releasing' : 'Approve' }}</span>
                        </Button>
                    </template>
                </EodVerificationDialog>

                <EodVerificationDialog :voucher-id="voucher.id" action="reject">
                    <template #trigger>
                        <Button 
                            variant="destructive"
                            :class="voucher.status === 'forCheck' ? 'opacity-50 cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'forCheck'"
                        >
                            <X class="h-4 w-4 mr-2" />
                            Reject
                        </Button>
                    </template>
                </EodVerificationDialog>
            </template>

            <!-- Accounting Actions -->
            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status !== 'forCheck'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="forEod">
                    <template #trigger>
                        <Button 
                            :variant="voucher.status == 'forEOD' ? 'secondary' : 'default'"
                            :class="voucher.status == 'forEOD' ? 'cursor-not-allowed' : ''"
                            :disabled="voucher.status == 'forEOD'"
                        >
                            <Check class="h-4 w-4 mr-2" />
                            <span>{{ voucher.status == 'forEOD' ? 'Sent to EOD' : 'For EOD Approval' }}</span>
                        </Button>
                    </template>
                </DirectorVerificationDialog>

                <DirectorVerificationDialog :voucher-id="voucher.id" action="reject">
                    <template #trigger>
                        <Button 
                            variant="destructive"
                            :class="voucher.status === 'forEOD' ? 'opacity-50 cursor-not-allowed' : ''"
                            :disabled="voucher.status === 'forEOD'"
                        >
                            <X class="h-4 w-4 mr-2" />
                            Reject
                        </Button>
                    </template>
                </DirectorVerificationDialog>
            </template>
        </div>
    </div>
</template>