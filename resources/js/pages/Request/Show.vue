<script setup lang="ts">
    import AppLayout from '@/layouts/AppLayout.vue'
    import { Head } from '@inertiajs/vue3'
    import { type BreadcrumbItem } from '@/types'
    import PrintableSection from '@/components/printables/RequestPrint.vue'
    import { Printer, ListChecks } from 'lucide-vue-next';
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
    user: {
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
    const showApproveModal = ref(false);
    const showReleaseModal = ref(false);
    const showOrderModal = ref(false);
    const showForRequestModal = ref(false);

    const password = ref('');

    const form = useForm({
        status: '',
        password: ''
    });

    function navigateToEdit() {
        router.get(`/requests/${props.request.id}/release`);
    }

    // Status update function
    async function submitStatusUpdate(newStatus: string, userPassword: string) {
        form.status = newStatus;
        form.password = userPassword;
        
        form.patch(`/requests/${props.request.id}/status`, {
            preserveScroll: true,
            onSuccess: () => {
                toast.success('Status updated successfully');
                showApproveModal.value = false;
                showReleaseModal.value = false;
                showForRequestModal.value = false;
                showOrderModal.value = false;
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

    // Add a ref for the printable component
    const printableComponent = ref<InstanceType<typeof PrintableSection> | null>(null)

    // Update your printArea function
    function printArea() {
        if (printableComponent.value) {
            printableComponent.value.printArea()
        }
    }
</script>

    <template>
    <Head title="Request Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-4">
        <div class="flex items-center  space-x-2 justify-between">
            <h1 class="text-lg font-semibold">Request Details</h1>
            <div class="ml-auto space-x-2 flex items-center">
                <!-- Executive access -->
                <div v-if="user.role === 'executive_director'" class="flex items-center gap-2">
                    <Dialog v-model:open="showApproveModal">
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
                            <Label for="approve-password">Password</Label>
                            <Input 
                            id="approve-password" 
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
                        :disabled="request.status === 'rejected' || request.status === 'approved' || form.processing"
                    >
                        Reject
                    </Button>
                </div>
                <!-- Property access -->
                <div v-if="user.role === 'property_custodian'" class="flex items-center gap-2">
                    <Button 
                        variant="secondary" 
                        size="sm"
                        @click="navigateToEdit"
                        :disabled="request.status == 'rejected' || request.status == 'released'"
                        >
                        Partial Release
                    </Button>
                    
                    <!-- Release All Dialog -->
                    <Dialog v-model:open="showReleaseModal">
                        <DialogTrigger as-child>
                        <Button 
                            variant="default" 
                            size="sm" 
                            :disabled="request.status === 'released' || form.processing"
                        >
                            Release All
                        </Button>
                        </DialogTrigger>
                        <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Password Verification</DialogTitle>
                            <DialogDescription>
                            Please enter your password to release this request
                            </DialogDescription>
                        </DialogHeader>
                        <div class="space-y-2">
                            <Label for="release-password">Password</Label>
                            <Input 
                            id="release-password" 
                            v-model="password" 
                            type="password" 
                            placeholder="Enter your password"
                            class="w-full"
                            />
                        </div>
                        <DialogFooter>
                            <Button 
                            @click="submitStatusUpdate('released', password)"
                            :disabled="!password || form.processing"
                            >
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Confirm Release</span>
                            </Button>
                        </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <!-- Request To Order Dialog -->
                    <Dialog v-model:open="showForRequestModal">
                        <DialogTrigger as-child>
                        <Button 
                            variant="default" 
                            size="sm" 
                            :disabled="request.status === 'to_order' || form.processing || request.status == 'released'"
                        >
                            For Request To Order
                        </Button>
                        </DialogTrigger>
                        <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Password Verification</DialogTitle>
                            <DialogDescription>
                            Please enter your password to send this order
                            </DialogDescription>
                        </DialogHeader>
                        <div class="space-y-2">
                            <Label for="order-password">Password</Label>
                            <Input 
                            id="order-password" 
                            v-model="password" 
                            type="password" 
                            placeholder="Enter your password"
                            class="w-full"
                            />
                        </div>
                        <DialogFooter>
                            <Button 
                            @click="submitStatusUpdate('to_order', password)"
                            :disabled="!password || form.processing"
                            >
                            <span v-if="form.processing">Processing...</span>
                            <span v-else>Confirm Order</span>
                            </Button>
                        </DialogFooter>
                        </DialogContent>
                    </Dialog>
                </div>
                <!-- Request To Property Dialog -->
                <div v-if="user.role === 'department_head'" class="flex items-center gap-2">
                    <Dialog v-model:open="showOrderModal">
                        <DialogTrigger as-child>
                        <Button 
                            variant="default" 
                            size="sm" 
                            :disabled="request.status === 'propertyCustodian' || request.status === 'rejected' || form.processing"
                        >
                            Approve
                        </Button>
                        </DialogTrigger>
                        <DialogContent>
                        <DialogHeader>
                            <DialogTitle>Password Verification</DialogTitle>
                            <DialogDescription>
                            Please enter your password to send this order
                            </DialogDescription>
                        </DialogHeader>
                        <div class="space-y-2">
                            <Label for="order-password">Password</Label>
                            <Input 
                            id="order-password" 
                            v-model="password" 
                            type="password" 
                            placeholder="Enter your password"
                            class="w-full"
                            />
                        </div>
                        <DialogFooter>
                            <Button 
                                @click="submitStatusUpdate('propertyCustodian', password)"
                                :disabled="!password || form.processing"
                                >
                                <span v-if="form.processing">Processing...</span>
                                <span v-else>Confirm Order</span>
                                </Button>
                        </DialogFooter>
                        </DialogContent>
                    </Dialog>
                    <Button 
                            variant="outline" 
                            size="sm" 
                            @click="submitStatusUpdate('rejected', '')"
                            :disabled="request.status === 'propertyCustodian' || request.status === 'propertyCustodian' || form.processing"
                        >
                            Reject
                    </Button>
                </div>
                <Button size="sm" @click="printArea"> <Printer />Print List</Button>
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
                    <TableCell class="border p-2 capitalize">
                    <span :class="{
                            'text-green-600': request.status === 'approved',
                            'text-green-700': request.status === 'released',
                            'text-red-600': request.status === 'rejected',
                            'text-yellow-800': request.status === 'pending',
                            'text-yellow-600': request.status === 'to_property'
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
            <h2 class="text-lg font-semibold my-4">Items</h2>
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
                    <TableRow 
                    v-for="(detail, index) in request.details" 
                    :key="detail.id"
                    >
                    <TableCell class="border p-2">{{ index + 1 }}</TableCell>
                    <TableCell class="border p-2">
                        <span v-if="detail.quantity == 0" class="text-green-600">
                        (Released: {{ detail.released_quantity }})
                        </span>
                        <span v-else>
                        {{ detail.quantity }}
                        </span>
                    </TableCell>
                    <TableCell class="border p-2">{{ detail.unit }}</TableCell>
                    <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                    </TableRow>
                </TableBody>
            </Table>
            </div>
        </div>
        </div>
        <!-- Printed Item -->
          <PrintableSection ref="printableComponent" :request="request" :user="user" />
        <!-- End Print Item -->
    </AppLayout>
    </template>