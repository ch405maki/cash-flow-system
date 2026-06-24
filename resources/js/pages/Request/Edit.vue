<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { requestService } from '@/services/requestService';
import { ref, onMounted } from 'vue';
import { Trash2, Edit } from 'lucide-vue-next';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import StatusBadge from '@/components/StatusBadge.vue';
import { useToast } from "vue-toastification";
import { Skeleton } from '@/components/ui/skeleton';

const toast = useToast();

const props = defineProps({
  requestId: {
    type: Number,
    required: true,
  },
});

const request = ref<any>(null)
const departments = ref<any[]>([])
const loading = ref(true)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: '', href: '' },
]

const selectedItems = ref<number[]>([]);
const isReleasing = ref(false);
const isEditingPurpose = ref(false);

const form = ref({
  purpose: '',
  details: [] as any[],
});
const processing = ref(false);

onMounted(async () => {
  try {
    const response = await requestService.editData(props.requestId)
    request.value = response.data.request
    departments.value = response.data.departments
    breadcrumbs[2].title = request.value.request_no
    form.value.purpose = request.value.purpose || ''
    form.value.details = request.value.details.map(detail => ({
      id: detail.id,
      item_id: detail.item_id || null,
      quantity: detail.quantity,
      released_quantity: detail.released_quantity || 0,
      unit: detail.unit,
      item_description: detail.item_description
    }))
  } catch (error) {
    console.error('Failed to load request for edit:', error)
  } finally {
    loading.value = false
  }
})

const submit = async () => {
  processing.value = true;
  
  try {
    console.log('Submitting:', JSON.stringify(form.value.details, null, 2));
    
    const response = await requestService.updateItems(request.value.id, form.value.details.map(item => ({
      item_id: item.item_id || null,
      quantity: Number(item.quantity),
      unit: item.unit,
      item_description: item.item_description
    })));

    toast.success(response.message || 'Request items updated successfully');

  } catch (error) {
    if (error.response?.data?.errors) {
      toast({
        title: 'Validation Error',
        description: Object.entries(error.response.data.errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n'),
        variant: 'destructive',
      });
    } else {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to update request items',
        variant: 'destructive',
      });
    }
  } finally {
    processing.value = false;
  }
};

const updatePurpose = async () => {
  try {
    await requestService.updatePurpose(request.value.id, form.value.purpose);

    toast.success('Purpose updated successfully');
    isEditingPurpose.value = false;
    
    request.value.purpose = form.value.purpose;

  } catch (error) {
    console.error('Error updating purpose:', error);
    
    if (error.response?.data?.errors) {
      toast({
        title: 'Validation Error',
        description: Object.entries(error.response.data.errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n'),
        variant: 'destructive',
      });
    } else {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to update purpose',
        variant: 'destructive',
      });
    }
  }
};

const cancelEditPurpose = () => {
  form.value.purpose = request.value.purpose || '';
  isEditingPurpose.value = false;
};

const addDetail = () => {
  form.value.details.push({
    item_id: null,
    quantity: 1,
    unit: 'pcs',
    item_description: '',
  });
};

const removeDetail = (index: number) => {
  form.value.details.splice(index, 1);
};

const releaseItems = async () => {
  if (selectedItems.value.length === 0) {
    toast({
      title: 'No items selected',
      description: 'Please select at least one item to release',
      variant: 'destructive'
    });
    return;
  }

  const invalidItems = form.value.details
    .filter(detail => selectedItems.value.includes(detail.id))
    .filter(item => {
      const available = item.quantity - (item.released_quantity || 0);
      if (isNaN(available) || available < 0) {
        console.error('Invalid quantity calculation', {
          quantity: item.quantity,
          released: item.released_quantity,
          available
        });
      }
      return item.released_quantity <= 0 || item.released_quantity > available;
    });

  if (invalidItems.length > 0) {
    const itemNames = invalidItems.map(i => i.item_description).join(', ');
    toast({
      title: 'Invalid quantities',
      description: `Release quantities must be greater than 0 and not exceed available quantity for: ${itemNames}`,
      variant: 'destructive'
    });
    return;
  }

  isReleasing.value = true;
  
  try {
    const itemsToRelease = form.value.details
      .filter(detail => selectedItems.value.includes(detail.id))
      .map(item => ({
        request_detail_id: item.id,
        quantity: Number(item.released_quantity)
      }));

    const response = await requestService.release(request.value.id, {
      items: itemsToRelease,
      notes: 'Items released from request'
    });

    form.value.details = form.value.details.map(detail => {
      const updated = response.data.request.details.find(d => d.id === detail.id);
      return updated ? {
        ...detail,
        released_quantity: Number(updated.released_quantity)
      } : detail;
    });

    toast({
      title: 'Success',
      description: 'Items released successfully',
    });

    selectedItems.value = [];
    
  } catch (error) {
    console.error('Release error:', error);
    let errorMessage = 'Failed to release items';
    
    if (error.response?.data?.errors?.quantity) {
      errorMessage = error.response.data.errors.quantity.join(', ');
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    }

    toast({
      title: 'Error',
      description: errorMessage,
      variant: 'destructive',
    });
  } finally {
    isReleasing.value = false;
  }
};

const toggleSelectAll = (checked: boolean) => {
  selectedItems.value = checked ? form.value.details.map(d => d.id) : [];
};
</script>

<template>
  <Head title="Edit Request Items" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-4 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-32 w-full" />
      <Skeleton class="h-64 w-full" />
    </div>

    <template v-else-if="request">
      <div class="p-4 space-y-4">
        <div class="flex justify-between items-center">
          <h1 class="text-xl font-bold">Request Information</h1>
          <Link :href="route('request.index')">
            <Button variant="outline"> Back to Requests </Button>
          </Link>
        </div>
        <div>
          <Table>
          <TableBody>
              <TableRow>
                  <TableCell class="border-r p-2 w-10">Request No:</TableCell>
                  <TableCell class="border-r p-2">{{ request.request_no }}</TableCell>
                  <TableCell class="border-r p-2 w-32">Status: </TableCell>
                  <TableCell class="p-2 capitalize">
                  <StatusBadge :status="request.status" show-icon size="md" />
                  </TableCell>
              </TableRow>
              <TableRow>
                  <TableCell class="border-r p-2">Department:</TableCell>
                  <TableCell class="border-r p-2">{{ request.department.department_name || 'N/A' }}</TableCell>
                  <TableCell class="border-r p-2">Requested By:</TableCell>
                  <TableCell class="p-2">{{ request.user.first_name }} {{ request.user.last_name }}</TableCell>
              </TableRow>
              <TableRow>
                <TableCell class="border-r p-2">Purpose:</TableCell>

                <TableCell colspan="3" class="p-2">
                  <div class="relative">

                    <div v-if="isEditingPurpose" class="relative">
                      <Textarea
                        v-model="form.purpose"
                        placeholder="Enter purpose..."
                        class="min-h-[120px] pr-20"
                      />

                      <div class="absolute bottom-2 right-2 flex gap-2">
                        <Button
                          @click="updatePurpose"
                          size="sm"
                          class="h-7 px-3"
                        >
                          Save
                        </Button>

                        <Button
                          @click="cancelEditPurpose"
                          size="sm"
                          variant="secondary"
                          class="h-7 px-3"
                        >
                          Cancel
                        </Button>
                      </div>
                    </div>

                    <div
                      v-else
                      class="flex items-center justify-between w-full"
                    >
                      <span class="text-gray-900">
                        {{ request.purpose || 'N/A' }}
                      </span>

                      <Button
                        @click="isEditingPurpose = true"
                        size="icon"
                        variant="ghost"
                        class="ml-4"
                      >
                        <Edit class="h-4 w-4" />
                      </Button>
                    </div>

                  </div>
                </TableCell>
              </TableRow>

          </TableBody>
          </Table>
        </div>
        <div class="pt-4 pb-6">
          <h1 class="text-xl font-bold">Partially Release Requested Items (Editable)</h1>
          <div class="flex justify-between items-center mb-2">
            <h3 class="text-sm font-medium">Items List</h3>
            <Button type="button" @click="addDetail" variant="outline" size="sm">
              Add Item
            </Button>
          </div>
          <form @submit.prevent="submit" class="space-y-4">
            <div class="overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[100px] border-r text-xs">Quantity</TableHead>
                    <TableHead class="w-[150px] border-r text-xs">Unit</TableHead>
                    <TableHead class="border-r text-xs">Description</TableHead>
                    <TableHead class="w-[40px] text-xs text-right">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="(detail, index) in form.details"
                    :key="index"
                    class="border-b"
                  >

                    <TableCell class="border-r p-2">
                      <Input
                        :id="`quantity-${index}`"
                        type="number"
                        v-model.number="detail.quantity"
                        min="1"
                        required
                        class="border border-gray-300 rounded text-xs h-8 w-full"
                      />
                    </TableCell>

                    <TableCell class="border-r p-2">
                      <Input
                        :id="`unit-${index}`"
                        v-model="detail.unit"
                        placeholder="e.g. kg, pcs"
                        required
                        class="border border-gray-300 rounded text-xs h-8 w-full"
                      />
                    </TableCell>

                    <TableCell class="border-r p-2">
                      <Input
                        :id="`item_description-${index}`"
                        v-model="detail.item_description"
                        placeholder="Item description"
                        required
                        class="border border-gray-300 rounded text-xs h-8 w-full"
                      />
                    </TableCell>

                    <TableCell class="p-2 flex justify-end items-center mr-[7px]">
                      <Button
                        type="button"
                        @click="removeDetail(index)"
                        variant="destructive"
                        size="sm"
                        class="text-xs h-8 px-3"
                        :disabled="form.details.length <= 1"
                      >
                        <Trash2 />
                      </Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            
            <div class="flex justify-end items-center">
              
              <div class="flex gap-2">
                <Button
                  type="button"
                  variant="outline"
                  size="sm"
                  as-child
                >
                  <Link :href="route('request.index')">Cancel</Link>
                </Button>

                <Button type="submit" size="sm" :disabled="processing">
                  <span v-if="processing" class="text-xs">Saving...</span>
                  <span v-else class="text-xs">Save Items</span>
                </Button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </template>
  </AppLayout>
</template>
