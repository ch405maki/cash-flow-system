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
import axios from 'axios'

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

// Modal states
const showPasswordModal = ref(false);
const showConfirmDialog = ref(false);
const password = ref('');

// Tagging state
const taggingValues = ref<Record<number, string>>(
  props.request.details.reduce((acc, detail) => {
    acc[detail.id] = detail.tagging || '';
    return acc;
  }, {} as Record<number, string>)
);

const form = useForm({
  status: '',
  password: ''
});

// Function to update local tagging state
function updateLocalTagging(detailId: number, value: string) {
  taggingValues.value[detailId] = value;
}

// Function to submit all tagging changes
async function submitAllTagging() {
  try {
    showConfirmDialog.value = false;
    
    const response = await axios.patch(`/api/requests/${props.request.id}/tagging`, {
      tagging: taggingValues.value
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });
    
    toast.success('Tagging updated successfully');
    router.reload({ only: ['request'] });
  } catch (error) {
    if (axios.isAxiosError(error)) {
      if (error.response) {
        toast.error(error.response.data.message || 'Failed to update tagging');
        console.error('Error details:', error.response.data);
      } else {
        toast.error('Network error occurred');
      }
    } else {
      toast.error('An unexpected error occurred');
    }
  }
}

</script>

<template>
  <Head title="Request Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div class="flex items-center justify-between">
        <h1 class="text-xl font-bold">Request Details</h1>
        <div class="flex gap-2">
          <Dialog v-model:open="showConfirmDialog">
            <DialogTrigger as-child>
              <Button 
                variant="default"
                :disabled="Object.values(taggingValues).every(val => !val)"
              >
                Save Tagging
              </Button>
            </DialogTrigger>
            <DialogContent>
              <DialogHeader>
                <DialogTitle>Confirm Tagging Update</DialogTitle>
                <DialogDescription>
                  Are you sure you want to update the tagging for all items?
                </DialogDescription>
              </DialogHeader>
              <DialogFooter>
                <Button variant="outline" @click="showConfirmDialog = false">Cancel</Button>
                <Button variant="default" @click="submitAllTagging">Confirm</Button>
              </DialogFooter>
            </DialogContent>
          </Dialog>
        </div>
      </div>

      <!-- Request Details -->
      <div>
        <Table>
          <TableBody>
            <TableRow>
              <TableCell class="border p-2 w-10">Request No:</TableCell>
              <TableCell class="border p-2">{{ request.request_no }}</TableCell>
              <TableCell class="border p-2 w-32">Status: </TableCell>
              <TableCell class="border p-2 capitalize">
                <span :class="{
                  'text-green-600': request.status === 'approved',
                  'text-green-700': request.status === 'released',
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
                <TableHead class="border p-2">Tagging</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="(detail, index) in request.details" :key="detail.id">
                <TableCell class="border p-2">{{ index + 1 }}</TableCell>
                <TableCell class="border p-2">{{ detail.quantity }}</TableCell>
                <TableCell class="border p-2">{{ detail.unit }}</TableCell>
                <TableCell class="border p-2">{{ detail.item_description }}</TableCell>
                <TableCell class="border p-2">
                  <select
                    :value="taggingValues[detail.id]"
                    @change="updateLocalTagging(detail.id, $event.target.value)"
                    class="border rounded p-1 w-full"
                  >
                    <option value="">Select Tagging</option>
                    <option value="no_canvas">No Canvas</option>
                    <option value="with_canvas">With Canvas</option>
                  </select>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>
    </div>
  </AppLayout>
</template>