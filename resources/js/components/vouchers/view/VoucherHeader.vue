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
            <h2 class="text-2xl font-bold">Voucher # {{ voucher.voucher_no }}</h2>
        </div>
        <div class="flex gap-2">
            <Button variant="default" @click="emit('print')" class="flex items-center gap-2">
                <Printer class="h-4 w-4" />
                Print
            </Button>
            <template v-if="authUser.role == 'executive_director' && authUser.access_id == '1' && voucher.status !== 'rejected'">
                <EodVerificationDialog :voucher-id="voucher.id" action="approve">
                    <template #trigger>
                        <Button variant="default" class="flex items-center gap-2"
                            :class="voucher.status === 'forCheck' ? 'bg-gray-400 text-white cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-400'"
                            :disabled="voucher.status === 'forCheck'">
                            <Check class="h-4 w-4" />
                            <span v-if="voucher.status == 'forCheck'">For Check Releasing </span>
                            <span v-else>Approve </span>
                        </Button>
                    </template>
                </EodVerificationDialog>

                <EodVerificationDialog :voucher-id="voucher.id" action="reject">
                    <template #trigger>
                        <Button variant="default" class="flex items-center gap-2"
                            :class="voucher.status === 'forCheck' ? 'bg-gray-400 text-white cursor-not-allowed' : 'bg-red-500 text-white hover:bg-red-400'"
                            :disabled="voucher.status === 'forCheck'">
                            Reject
                        </Button>
                    </template>
                </EodVerificationDialog>
            </template>

            <template v-if="authUser.role == 'accounting' && authUser.access_id == '3' && voucher.status !== 'forCheck'">
                <DirectorVerificationDialog :voucher-id="voucher.id" action="forEod">
                    <template #trigger>
                        <Button variant="default" class="flex items-center gap-2"
                            :class="voucher.status == 'forEOD' ? 'bg-gray-400 text-white cursor-not-allowed' : 'bg-green-500 text-white hover:bg-green-400'"
                            :disabled="voucher.status == 'forEOD'">
                            <Check class="h-4 w-4" />
                            <span v-if="voucher.status == 'forEOD'"> Sent to EOD</span>
                            <span v-else>For EOD Approval</span>
                        </Button>
                    </template>
                </DirectorVerificationDialog>

                <DirectorVerificationDialog :voucher-id="voucher.id" action="reject">
                    <template #trigger>
                        <Button variant="default" class="flex items-center gap-2"
                            :class="voucher.status === 'forEOD' ? 'bg-gray-400 text-white cursor-not-allowed' : 'bg-red-500 text-white hover:bg-red-400'"
                            :disabled="voucher.status === 'forEOD'">
                            <X class="h-4 w-4" />
                            Reject
                        </Button>
                    </template>
                </DirectorVerificationDialog>
            </template>
        </div>
    </div>
</template>