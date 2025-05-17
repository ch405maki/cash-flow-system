    <script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue'
    import { Head } from '@inertiajs/vue3'
    import { type BreadcrumbItem } from '@/types'
    import {
    Table,
    TableHeader,
    TableRow,
    TableHead,
    TableBody,
    TableCell,
    } from '@/components/ui/table'
    import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    } from '@/components/ui/dialog'
    import { Button } from '@/components/ui/button'
    import { Input } from '@/components/ui/input'
    import { Label } from '@/components/ui/label'
    import { useToast } from 'vue-toastification'
    import { router } from '@inertiajs/vue3';
    import { useForm } from '@inertiajs/vue3';
    import { ref } from 'vue'

    const toast = useToast()
    const props = defineProps({
    request: {
        type: Object,
        required: true,
    },
    })

    const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Request', href: '/request' },
    { title: `${props.request.request_no}`, href: '' },
    ]

    // Modal state
    const showPasswordModal = ref(false);
    const password = ref('');

    const form = useForm({
        status: '',
        password: ''
    });

    function navigateToEdit() {
        router.get(`/requests/${props.request.id}/edit`);
    }

    // Status update function
    async function submitStatusUpdate(newStatus: string, userPassword: string) {
        form.status = newStatus;
        form.password = userPassword;
        
        form.patch(`/requests/${props.request.id}/status`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Status updated successfully');
                showPasswordModal.value = false; // Close dialog on success
                password.value = ''; // Clear password field
            },
            onError: (errors) => {
                if (errors.password) {
                    toast.error(errors.password);
                } else {
                    toast.error('Failed to update status');
                }
            }
        });
    }
    </script>

    <template>
    <Head title="Request Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-bold">Request Details</h1>
            <div class="ml-auto space-x-2">
            <Dialog v-model:open="showPasswordModal">
                <DialogTrigger as-child>
                <Button 
                    variant="default" 
                    size="sm" 
                    :disabled="request.status === 'approved' || form.processing"
                >
                    Approve
                </Button>
                </DialogTrigger>
                <DialogContent>
                <DialogHeader>
                    <DialogTitle>Password Verification</DialogTitle>
                    <DialogDescription>
                    Please enter your password to approve this request
                    </DialogDescription>
                </DialogHeader>
                <div class="space-y-2">
                    <Label for="password">Password</Label>
                    <Input 
                    id="password" 
                    v-model="password" 
                    type="password" 
                    placeholder="Enter your password"
                    class="w-full"
                    />
                </div>
                <DialogFooter>
                    <Button 
                    @click="submitStatusUpdate('approved', password)"
                    :disabled="!password || form.processing"
                    >
                    <span v-if="form.processing">Processing...</span>
                    <span v-else>Confirm Approval</span>
                    </Button>
                </DialogFooter>
                </DialogContent>
            </Dialog>

            <Button 
                variant="outline" 
                size="sm" 
                @click="submitStatusUpdate('rejected', '')"
                :disabled="request.status === 'rejected' || form.processing"
            >
                Reject
            </Button>
            <Button 
                variant="secondary" 
                size="sm"
                @click="navigateToEdit"
                :disabled="request.status == 'rejected'"
                >
                Edit
            </Button>
            <Button 
                variant="secondary" 
                size="sm" 
            >
                Request To Order
            </Button>
            </div>
        </div>

        <!-- Request Details -->
        <div>
            <Table>
            <TableBody>
                <TableRow>
                    <TableCell class="border p-2  w-10">Request No:</TableCell>
                    <TableCell class="border p-2">{{ request.request_no }}</TableCell>
                    <TableCell class="border p-2  w-32">Status: </TableCell>
                    <TableCell class="border p-2">
                    <span :class="{
                            'text-green-600': request.status === 'approved',
                            'text-red-600': request.status === 'rejected',
                            'text-yellow-600': request.status === 'pending'
                        }">
                        {{ request.status }}
                    </span>
                    </TableCell>
                </TableRow>
                <TableRow>
                    <TableCell class="border p-2">Department:</TableCell>
                    <TableCell class="border p-2">{{ request.department.department_name || 'N/A' }}</TableCell>
                    <TableCell class="border p-2">Requested By:</TableCell>
                    <TableCell class="border p-2">{{ request.user.first_name }} {{ request.user.last_name }}</TableCell>
                </TableRow>
                <TableRow>
                    <TableCell class="border p-2">Purpose:</TableCell>
                    <TableCell colspan="3" class="border p-2">{{ request.purpose || 'N/A'}}</TableCell>
                </TableRow>
            </TableBody>
            </Table>
        </div>

        <div>
            <h2 class="text-xl font-semibold my-4">Items</h2>
            <div>
            <Table>
                <TableHeader class="bg-muted">
                <TableRow>
                    <TableHead class="border p-2 w-10">#</TableHead>
                    <TableHead class="border p-2">Quantity</TableHead>
                    <TableHead class="border p-2">Unit</TableHead>
                    <TableHead class="border p-2">Item Description</TableHead>
                </TableRow>
                </TableHeader>
                <TableBody>
                <TableRow v-for="(detail, index) in request.details" :key="detail.id">
                    <TableCell class="border p-2">{{ index + 1 }}</TableCell>
                    <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                    <TableCell class="border p-2">{{ detail.unit }}</TableCell>
                    <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                </TableRow>
                </TableBody>
            </Table>
            </div>
        </div>
        </div>
    </AppLayout>
    </template>