<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from '@/components/ui/toast';
import axios from 'axios';
import { ref } from 'vue';
import { Trash2 } from 'lucide-vue-next';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
  departments: {
    type: Array,
    required: true,
  },
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
]

// Add selected items state
const selectedItems = ref<number[]>([]);
const isReleasing = ref(false);

// Modify form to include released quantities
const form = ref({
  details: props.request.details.map(detail => ({
    id: detail.id,
    quantity: detail.quantity,
    released_quantity: detail.released_quantity || 0,
    unit: detail.unit,
    item_description: detail.item_description
  }))
});
const processing = ref(false);

const submit = async () => {
  processing.value = true;
  
  try {
    console.log('Submitting:', JSON.stringify(form.value.details, null, 2));
    
    const response = await axios.put(`/api/requests/${props.request.id}/items`, {
      details: form.value.details.map(item => ({
        quantity: Number(item.quantity),
        unit: item.unit,
        item_description: item.item_description
      }))
    }, {
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    });

    console.log('Response:', response.data);
    
    toast({
      title: 'Success',
      description: 'Request items updated successfully',
    });

  } catch (error) {
    console.error('Error:', error.response ? error.response.data : error.message);
    
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

const addDetail = () => {
  form.value.details.push({
    quantity: 1,
    unit: 'pcs',
    item_description: '',
  });
};

const removeDetail = (index: number) => {
  form.value.details.splice(index, 1);
};

// Add release method
const releaseItems = async () => {
  if (selectedItems.value.length === 0) {
    toast({
      title: 'No items selected',
      description: 'Please select at least one item to release',
      variant: 'destructive'
    });
    return;
  }

  // Validate quantities before submitting
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
        quantity: Number(item.released_quantity) // Ensure number type
      }));

    const response = await axios.post(`/api/requests/${props.request.id}/release`, {
      items: itemsToRelease,
      notes: 'Items released from request'
    });

    // Update local state
    form.value.details = form.value.details.map(detail => {
      const updated = response.data.data.request.details.find(d => d.id === detail.id);
      return updated ? {
        ...detail,
        released_quantity: Number(updated.released_quantity) // Ensure number type
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
    <div class="p-4 space-y-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Request Information</h1>
        <Link :href="route('request.index')">
          <Button variant="outline"> Back to Requests </Button>
        </Link>
      </div>
      <div>
        <Table>
        <TableBody>
            <TableRow>
                <TableCell class="border p-2  w-10">Request No:</TableCell>
                <TableCell class="border p-2">{{ request.request_no }}</TableCell>
                <TableCell class="border p-2  w-32">Status: </TableCell>
                <TableCell class="border p-2 capitalize">
                <span 
                class="inline-block rounded-full px-2 py-0.5 text-xs font-semibold capitalize"
                :class="{
                        'bg-indigo-100 text-indigo-800': request.status === 'partially_released',
                        'bg-orange-100 text-orange-800 ': request.status === 'request to order',
                        'bg-green-100 text-green-700': request.status === 'released',
                        'bg-yellow-100 text-yellow-800': request.status === 'pending',
                        'bg-green-100 text-green-800': request.status === 'approved',
                        'bg-red-100 text-red-800': request.status === 'rejected',
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
      <!-- start table -->
      <div class="pt-4 pb-6">
        <h1 class="text-xl font-bold">Partially Release Requested Items (Editable)</h1>
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-sm font-medium">Items List</h3>
        </div>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="border overflow-hidden">
            <Table>
              <TableHeader class="bg-gray-100 dark:bg-gray-800">
                <TableRow>
                  <TableHead class="w-[40px] border-r text-xs text-center">
                    Release
                  </TableHead>
                  <TableHead class="w-[100px] border-r text-xs">Release Qty</TableHead>
                  <TableHead class="w-[100px] border-r text-xs">Quantity</TableHead>
                  <TableHead class="w-[100px] border-r text-xs">Unit</TableHead>
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
                  <TableCell class="border-r">
                    <div class="flex justify-center items-center">
                      <Checkbox 
                        :id="'select-' + index"
                        :checked="selectedItems.includes(detail.id)"
                        @update:checked="(checked) => {
                          if (checked) {
                            selectedItems.push(detail.id);
                          } else {
                            selectedItems = selectedItems.filter(id => id !== detail.id);
                          }
                        }"
                        class="h-4 mr-4 w-4 border-zinc-600"
                      />
                    </div>
                  </TableCell>

                  <TableCell class="border-r p-2">
                    <Input
                      :id="'released-' + index"
                      type="number"
                      v-model.number="detail.released_quantity"
                      :max="detail.quantity - detail.released_quantity"
                      min="0"
                      :disabled="!selectedItems.includes(detail.id)"
                      class="border border-gray-300 rounded text-xs h-8 w-full"
                    />
                  </TableCell>

                  <TableCell class="border-r p-2">
                    <Input
                      :id="`quantity-${index}`"
                      type="number"
                      v-model.number="detail.quantity"
                      min="1"
                      required
                      class="border border-gray-300 rounded text-xs h-8 w-full"
                      readonly
                    />
                  </TableCell>

                  <TableCell class="border-r p-2">
                    <Input
                      :id="`unit-${index}`"
                      v-model="detail.unit"
                      placeholder="e.g. kg, pcs"
                      required
                      class="border border-gray-300 rounded text-xs h-8 w-full"
                      readonly
                    />
                  </TableCell>

                  <TableCell class="border-r p-2">
                    <Input
                      :id="`item_description-${index}`"
                      v-model="detail.item_description"
                      placeholder="Item description"
                      required
                      class="border border-gray-300 rounded text-xs h-8 w-full"
                      readonly
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
          
          <div class="flex justify-between items-center">
            <div class="flex items-center  gap-2">
              <Checkbox 
                id="select-all"
                :checked="selectedItems.length === form.details.length"
                @update:checked="toggleSelectAll"
                class="h-4 w-4"
              />
              <Label>Select All</Label>
            </div>
            <div class="flex gap-2">
              <Button
                type="button"
                variant="outline"
                size="sm"
                as-child
              >
                <Link :href="route('request.index')">Cancel</Link>
              </Button>

              <Button 
                type="button" 
                @click="releaseItems"
                size="sm"
                :disabled="selectedItems.length === 0 || isReleasing"
              >
                <span v-if="isReleasing" class="text-xs">Releasing...</span>
                <span v-else class="text-xs">Release Selected Items</span>
              </Button>
            </div>
          </div>
        </form>
      </div>
      <!-- end table -->
    </div>
  </AppLayout>
</template>