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

  isReleasing.value = true;
  
  try {
    const itemsToRelease = form.value.details
      .filter(detail => selectedItems.value.includes(detail.id))
      .map(item => ({
        request_detail_id: item.id,
        quantity: item.released_quantity
      }));

    console.log('Releasing items:', itemsToRelease);
    
    const response = await axios.post(`/api/requests/${props.request.id}/release`, {
      items: itemsToRelease
    });

    console.log('Release response:', response.data);
    
    toast({
      title: 'Success',
      description: 'Items released successfully',
    });

    // Reset selection after release
    selectedItems.value = [];
    
  } catch (error) {
    console.error('Release error:', error.response ? error.response.data : error.message);
    
    if (error.response?.data?.errors) {
      toast({
        title: 'Release Error',
        description: Object.entries(error.response.data.errors)
          .map(([field, messages]) => `${field}: ${messages.join(', ')}`)
          .join('\n'),
        variant: 'destructive',
      });
    } else {
      toast({
        title: 'Error',
        description: error.response?.data?.message || 'Failed to release items',
        variant: 'destructive',
      });
    }
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
    <div class="container mx-auto p-4 space-y-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Request Items</h1>
        <Link :href="route('request.index')">
          <Button variant="outline"> Back to Requests </Button>
        </Link>
      </div>

      <Card>
        <CardHeader>
          <CardTitle>Request Information (Read Only)</CardTitle>
        </CardHeader>
        <CardContent class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-1">
            <Label>Request Number</Label>
            <div class="p-2 border rounded-md bg-gray-50 dark:bg-gray-800">
              {{ request.request_no }}
            </div>
          </div>

          <div class="space-y-1">
            <Label>Request Date</Label>
            <div class="p-2 border rounded-md bg-gray-50 dark:bg-gray-800">
              {{ new Date(request.request_date).toLocaleDateString() }}
            </div>
          </div>

          <div class="space-y-1">
            <Label>Department</Label>
            <div class="p-2 border rounded-md bg-gray-50 dark:bg-gray-800">
              {{ request.department.department_name || 'N/A' }}
            </div>
          </div>

          <div class="space-y-1">
            <Label>Status</Label>
            <div class="p-2 border rounded-md bg-gray-50 dark:bg-gray-800">
              {{ request.status }}
            </div>
          </div>

          <div class="space-y-1 md:col-span-2">
            <Label>Purpose</Label>
            <div class="p-2 border rounded-md bg-gray-50 dark:bg-gray-800 min-h-20">
              {{ request.purpose }}
            </div>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Request Items (Editable)</CardTitle>
        </CardHeader>
        <CardContent>
          <form @submit.prevent="submit" class="space-y-6">
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <h3 class="font-medium">Items List</h3>
                <Button type="button" @click="addDetail" variant="outline">
                  Add Item
                </Button>
              </div>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[60px]">
                      <Checkbox 
                      id="select-all"
                      :checked="selectedItems.length === form.details.length"
                      @update:checked="toggleSelectAll"
                    />
                    </TableHead>
                    <TableHead class="w-[100px]">Release Qty</TableHead>
                    <TableHead class="w-[100px]">Quantity</TableHead>
                    <TableHead class="w-[100px]">Unit</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead class="w-[100px]">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="(detail, index) in form.details"
                    :key="index"
                  >
                    <TableCell>
                      <div class="flex items-center space-x-2">
                        <Checkbox 
                          :id="`select-${index}`"
                          :checked="selectedItems.includes(detail.id)"
                          @update:checked="(checked) => {
                            if (checked) {
                              selectedItems.push(detail.id);
                            } else {
                              selectedItems = selectedItems.filter(id => id !== detail.id);
                            }
                          }"
                        />
                        <Label :for="`select-${index}`" class="text-sm">Release</Label>
                      </div>
                    </TableCell>

                    <TableCell>
                      <Input
                        :id="`released-${index}`"
                        type="number"
                        v-model.number="detail.released_quantity"
                        :max="detail.quantity"
                        min="0"
                        :disabled="!selectedItems.includes(detail.id)"
                      />
                    </TableCell>

                    <TableCell>
                      <Input
                        :id="`quantity-${index}`"
                        type="number"
                        v-model.number="detail.quantity"
                        min="1"
                        required
                      />
                    </TableCell>

                    <TableCell>
                      <Input
                        :id="`unit-${index}`"
                        v-model="detail.unit"
                        placeholder="e.g. kg, pcs"
                        required
                      />
                    </TableCell>

                    <TableCell>
                      <Input
                        :id="`item_description-${index}`"
                        v-model="detail.item_description"
                        placeholder="Item description"
                        required
                      />
                    </TableCell>

                    <TableCell>
                      <Button
                        type="button"
                        @click="removeDetail(index)"
                        variant="destructive"
                        size="sm"
                        :disabled="form.details.length <= 1"
                      >
                        Remove
                      </Button>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
            <div class="flex justify-end gap-4">
              <Button
                type="button"
                variant="outline"
                as-child
              >
                <Link :href="route('request.index')">Cancel</Link>
              </Button>

              <Button 
                type="button" 
                @click="releaseItems"
                :disabled="selectedItems.length === 0 || isReleasing"
              >
                <span v-if="isReleasing">Releasing...</span>
                <span v-else>Release Selected Items</span>
              </Button>
              
              <Button type="submit" :disabled="processing">
                <span v-if="processing">Saving...</span>
                <span v-else>Save Items</span>
              </Button>
            </div>
          </form>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>