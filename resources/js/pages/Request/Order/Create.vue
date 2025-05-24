<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

interface RequestDetail {
  id: number;
  request_id: number;
  department_id: number;
  quantity: number;
  released_quantity: number;
  unit: string;
  item_description: string;
}

interface FormData {
  notes: string;
  selectedItems: number[];
  quantities: Record<number, number>;
  request_ids: number[];
  department_ids: number[];
}

const props = defineProps<{
  requests: Array<{
    id: number;
    department_id: number;
    request_no: string;
    department: { id: number, name: string };
    details: RequestDetail[];
  }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Create Request To Order', href: '/request-to-order/create' }
];

// Flatten all request details with department_id
const allRequestDetails = computed(() => {
  return props.requests.flatMap(request => 
    request.details.map(detail => ({
      ...detail,
      request_no: request.request_no,
      department_name: request.department.name,
      department_id: request.department_id, // Ensure department_id is included
      available_quantity: detail.quantity - detail.released_quantity
    }))
  );
});

const form = useForm<FormData>({
  notes: '',
  selectedItems: [],
  quantities: {},
  request_ids: [],
  department_ids: []
});

// Initialize with all items selected by default
onMounted(() => {
  form.selectedItems = allRequestDetails.value.map(d => d.id);
  form.request_ids = [...new Set(allRequestDetails.value.map(d => d.request_id))];
  form.department_ids = [...new Set(allRequestDetails.value.map(d => d.department_id))];
  allRequestDetails.value.forEach(detail => {
    form.quantities[detail.id] = detail.available_quantity;
  });
});

const toggleSelectAll = (event: Event) => {
  const isChecked = (event.target as HTMLInputElement).checked;
  form.selectedItems = isChecked ? allRequestDetails.value.map(d => d.id) : [];
  form.request_ids = isChecked 
    ? [...new Set(allRequestDetails.value.map(d => d.request_id))] 
    : [];
  form.department_ids = isChecked
    ? [...new Set(allRequestDetails.value.map(d => d.department_id))]
    : [];
};

const submit = () => {
  if (form.selectedItems.length === 0) {
    alert('Please select at least one item');
    return;
  }

  // Prepare items with department_id
  const items = form.selectedItems.map(id => {
    const detail = allRequestDetails.value.find(d => d.id === id)!;
    return {
      request_id: detail.request_id,
      department_id: detail.department_id, // Include department_id
      detail_id: detail.id,
      quantity: form.quantities[id],
      unit: detail.unit,
      item_description: detail.item_description
    };
  });

  form.transform(() => ({
    notes: form.notes,
    items,
    request_ids: [...new Set(items.map(item => item.request_id))],
    department_ids: [...new Set(items.map(item => item.department_id))]
  })).post(route('request-to-orders.store'));
};
</script>

<template>
  <Head title="Create Request To Order" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <h1 class="text-2xl font-bold mb-4">Create Request to Order</h1>
      
      <div v-if="Object.keys(form.errors).length > 0" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p v-for="(error, field) in form.errors" :key="field" class="text-sm">
          {{ error }}
        </p>
      </div>
      
      <div v-if="allRequestDetails.length > 0">
        <div class="mb-4">
          <label for="order-notes" class="block font-medium">Notes</label>
          <textarea
            id="order-notes"
            v-model="form.notes"
            class="w-full border rounded p-2"
            rows="3"
          ></textarea>
        </div>
        
        <div class="mb-4 p-4 bg-blue-50 rounded">
          <h3 class="font-bold mb-2">Order Summary</h3>
          <p>Items Selected: {{ form.selectedItems.length }} of {{ allRequestDetails.length }}</p>
          <p>Requests Involved: {{ form.request_ids.length }}</p>
          <p>Departments Involved: {{ form.department_ids.length }}</p>
        </div>
        
        <div class="mb-2">
          <label class="flex items-center">
            <input 
              type="checkbox" 
              :checked="form.selectedItems.length === allRequestDetails.length"
              :indeterminate="form.selectedItems.length > 0 && form.selectedItems.length < allRequestDetails.length"
              @change="toggleSelectAll"
              class="mr-2"
            >
            Select All Items
          </label>
        </div>
        
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Select</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request #</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Department</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="detail in allRequestDetails" :key="detail.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <input
                    type="checkbox"
                    v-model="form.selectedItems"
                    :value="detail.id"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ detail.request_no }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ detail.department_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ detail.item_description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ detail.unit }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ detail.quantity }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div class="mt-6">
          <Button
            @click="submit"
            :disabled="form.processing || form.selectedItems.length === 0"
            class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500"
          >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Create Order</span>
          </Button>
        </div>
      </div>
      
      <div v-else class="bg-yellow-100 p-4 rounded">
        No available items to order.
      </div>
    </div>
  </AppLayout>
</template>