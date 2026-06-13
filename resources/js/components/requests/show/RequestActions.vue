<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetDescription, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { formatDateTime } from '@/lib/utils';
import { requestService } from '@/services/requestService';
import { router } from '@inertiajs/vue3';
import { BadgeCheck, CircleCheck, FilePenLine, History, Printer, Rocket, ShoppingCart, XCircle } from 'lucide-vue-next';
import { ref } from 'vue';
import { useToast } from 'vue-toastification';
import PasswordDialog from './PasswordDialog.vue';

const props = defineProps<{
    request: any;
    user: any;
    printableRef?: any;
}>();

const toast = useToast();
const processing = ref(false);

// Modal states
const showApproveModal = ref(false);
const showReleaseModal = ref(false);
const showOrderModal = ref(false);
const showForRequestModal = ref(false);
const showRejectModal = ref(false);

// Navigate functions
function navigateToEdit() {
    router.get(`/requests/${props.request.id}/release`);
}

function goToEditRequest(requestId: number) {
    router.get(`/requests/${requestId}/edit`);
}

// Status update function
async function submitStatusUpdate(newStatus: string, password: string) {
    processing.value = true;
    try {
        await requestService.updateStatus(props.request.id, {
            status: newStatus,
            password: password,
        });
        toast.success('Status updated successfully');
        closeAllDialogs();
        router.reload();
    } catch (error: any) {
        if (error.response?.data?.errors?.password) {
            toast.error(error.response.data.errors.password[0]);
        } else {
            toast.error('Failed to update status');
        }
    } finally {
        processing.value = false;
    }
}

function closeAllDialogs() {
    showApproveModal.value = false;
    showReleaseModal.value = false;
    showForRequestModal.value = false;
    showOrderModal.value = false;
    showRejectModal.value = false;
}

const emit = defineEmits(['print-list', 'print-released-items', 'reorder']);
function printList() {
    emit('print-list');
}

function printReleasedItems() {
    emit('print-released-items');
}

const handleReorder = () => {
    emit('reorder', props.request);
};
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Executive Director -->
        <div v-if="user.role === 'executive_director'" class="flex gap-2">
            <Button size="sm" :disabled="request.status === 'approved' || processing" @click="showApproveModal = true">
                <BadgeCheck />Approve
            </Button>
            <PasswordDialog
                v-model="showApproveModal"
                title="Password Verification"
                description="Please enter your password to approve this request"
                confirm-label="Confirm Approval"
                :loading="processing"
                @confirm="(password) => submitStatusUpdate('approved', password)"
            />

            <!-- Reject Button for Executive Director -->
            <Button variant="destructive" size="sm" :disabled="request.status === 'rejected' || processing" @click="showRejectModal = true">
                <XCircle />Reject
            </Button>
            <PasswordDialog
                v-model="showRejectModal"
                title="Password Verification"
                description="Please enter your password to reject this request"
                confirm-label="Confirm Rejection"
                :loading="processing"
                @confirm="(password) => submitStatusUpdate('rejected', password)"
            />
        </div>

        <!-- Property Custodian -->
        <div v-if="user.role === 'property_custodian'" class="flex gap-2">
            <Button variant="outline" size="sm" @click="navigateToEdit" :disabled="request.status == 'rejected' || request.status == 'released'">
                <Rocket /> Release Item
            </Button>

            <Button
                size="sm"
                :disabled="request.status === 'to_order' || request.status == 'released' || processing"
                @click="showForRequestModal = true"
            >
                <FilePenLine />For Request To Purchase
            </Button>
            <PasswordDialog
                v-model="showForRequestModal"
                title="Password Verification"
                description="Please enter your password to send this order"
                confirm-label="Confirm Approval"
                :loading="processing"
                @confirm="(password) => submitStatusUpdate('to_order', password)"
            />
        </div>

        <!-- Department Head -->
        <div v-if="(user.access ?? user.access_id) == 3" class="flex gap-2">
            <Button size="sm" v-if="request.status === 'pending'" @click="showOrderModal = true"> <BadgeCheck />Approve </Button>
            <PasswordDialog
                v-model="showOrderModal"
                title="Password Verification"
                description="Please enter your password to send this order"
                confirm-label="Confirm Approval"
                :loading="processing"
                @confirm="(password) => submitStatusUpdate('propertyCustodian', password)"
            />

            <!-- Reject Button for Department Head -->
            <Button variant="destructive" size="sm" v-if="request.status === 'pending'" @click="showRejectModal = true"> <XCircle />Deny </Button>
            <PasswordDialog
                v-model="showRejectModal"
                title="Password Verification"
                description="Please enter your password to reject this request"
                confirm-label="Confirm Rejection"
                :loading="processing"
                @confirm="(password) => submitStatusUpdate('rejected', password)"
            />
        </div>

        <!-- Staff or Department Head - Edit -->
        <div v-if="user.role === 'staff' || user.role === 'department_head'">
            <Button size="sm" variant="outline" v-if="request.status === 'pending'" @click.stop="goToEditRequest(request.id)">
                <FilePenLine class="h-4" /> Edit
            </Button>
            <Button size="sm" variant="outline" v-if="request.status === 'released'" @click.stop="handleReorder">
                <ShoppingCart class="h-4 w-4" /> Re Order
            </Button>
        </div>

        <Sheet>
            <SheetTrigger
                ><Button variant="outline" size="sm"><History />Time Stamp</Button></SheetTrigger
            >
            <SheetContent>
                <SheetHeader>
                    <SheetTitle>Time Stamp</SheetTitle>
                    <SheetDescription>
                        <!-- Approval History -->
                        <h3 class="text-sm font-medium text-muted-foreground">Request History</h3>
                        <div class="mb-3">
                            <h4 class="text-muted-foreground">{{ request.request_no }}</h4>
                            <p>Created At: {{ formatDateTime(request.created_at) }}</p>
                        </div>
                        <div v-if="request.approvals?.length">
                            <div v-for="(approval, index) in request.approvals" :key="approval.id" class="flex gap-4">
                                <!-- Left: icon + connector line -->
                                <div class="flex w-5 flex-shrink-0 flex-col items-center">
                                    <div class="z-10 flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-full bg-green-500">
                                        <CircleCheck class="h-4 w-4 text-white" />
                                    </div>

                                    <div v-if="index < request.approvals.length - 1" class="w-0.5 flex-1 bg-green-500"></div>
                                </div>

                                <!-- Right: content -->
                                <div :class="['flex-1 pt-0.5', index < request.approvals.length - 1 ? 'pb-6' : 'pb-0']">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-sm font-medium capitalize">
                                            {{ approval.user?.username || 'Unknown' }}
                                        </span>
                                        <span class="whitespace-nowrap text-xs text-muted-foreground">
                                            {{ formatDateTime(approval.created_at) }}
                                        </span>
                                    </div>
                                    <p class="mt-1 text-xs text-muted-foreground">"{{ approval.remarks || 'No remarks' }}."</p>
                                </div>
                            </div>
                        </div>
                    </SheetDescription>
                </SheetHeader>
            </SheetContent>
        </Sheet>

        <DropdownMenu>
            <DropdownMenuTrigger as-child>
                <Button size="sm">
                    <Printer class="h-4 w-4" />
                    Print Options
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" class="w-48">
                <DropdownMenuItem @click="printList" class="cursor-pointer"> Print List </DropdownMenuItem>
                <DropdownMenuItem @click="printReleasedItems" class="cursor-pointer" :disabled="!(request.releases && request.releases.length > 0)">
                    Print Released Items
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
