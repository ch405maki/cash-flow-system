<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

interface RequestItem {
  id: number;
  request_no: string;
  request_date: string;
  purpose: string;
  department: {
    id: number;
    name: string;
  };
  details: RequestDetail[];
}

interface RequestDetail {
  id: number;
  quantity: number;
  released_quantity: number;
  unit: string;
  item_description: string;
}

interface FormData {
  request_id: number | null;
  notes: string;
  quantities: Record<number, number>;
  suppliers: Record<number, string>;
  unitPrices: Record<number, number>;
}

const props = defineProps<{
  requests: RequestItem[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Create Request To Order', href: '/request-to-order/create' }
];

const selectedRequestId = ref<number | null>(null);
const selectedRequest = computed(() => {
  return props.requests.find(r => r.id === selectedRequestId.value) || null;
});

const form = useForm<FormData>({
  request_id: null,
  notes: '',
  quantities: {},
  suppliers: {},
  unitPrices: {},
});

// Calculate available quantity for each item
const availableQuantities = computed(() => {
  const quantities: Record<number, number> = {};
  if (selectedRequest.value) {
    selectedRequest.value.details.forEach(detail => {
      quantities[detail.id] = detail.quantity - detail.released_quantity;
    });
  }
  return quantities;
});

// Calculate total estimated cost
const totalEstimatedCost = computed(() => {
  if (!selectedRequest.value) return 0;
  
  return selectedRequest.value.details.reduce((total, detail) => {
    const quantity = form.quantities[detail.id] || 0;
    const price = form.unitPrices[detail.id] || 0;
    return total + (quantity * price);
  }, 0);
});

const loadRequestDetails = () => {
  if (!selectedRequestId.value) return;
  
  form.request_id = selectedRequestId.value;
  form.quantities = {};
  form.suppliers = {};
  form.unitPrices = {};
  
  // Initialize all items with available quantities
  if (selectedRequest.value) {
    selectedRequest.value.details.forEach(detail => {
      form.quantities[detail.id] = availableQuantities.value[detail.id];
      form.suppliers[detail.id] = '';
      form.unitPrices[detail.id] = 0;
    });
  }
};

const submit = () => {
  if (!selectedRequest.value) {
    alert('Please select a request');
    return;
  }

  // Validate quantities
  for (const detail of selectedRequest.value.details) {
    if (form.quantities[detail.id] <= 0 || form.quantities[detail.id] > availableQuantities.value[detail.id]) {
      alert(`Invalid quantity for item ${detail.item_description}`);
      return;
    }
  }

  // Prepare the items data (all details will be included)
  const items = selectedRequest.value.details.map(detail => ({
    id: detail.id,
    quantity: form.quantities[detail.id],
    supplier: form.suppliers[detail.id] || '',
    unit_price: form.unitPrices[detail.id] || 0,
  }));

  form.transform(() => ({
    ...form.data(),
    items,
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
      
      <div v-if="props.requests.length > 0">
        <div class="mb-4">
          <label for="request-select" class="block font-medium">Select Request</label>
          <select
            id="request-select"
            v-model="selectedRequestId"
            @change="loadRequestDetails"
            class="w-full border rounded p-2"
          >
            <option value="">Select a Request</option>
            <option v-for="request in props.requests" :key="request.id" :value="request.id">
              {{ request.request_no }} - {{ request.department.name }} ({{ request.request_date }})
            </option>
          </select>
        </div>
        
        <div v-if="selectedRequest">
          <div class="mb-4 p-4 bg-gray-50 rounded">
            <h2 class="font-bold mb-2">Request Information</h2>
            <p><strong>Request No:</strong> {{ selectedRequest.request_no }}</p>
            <p><strong>Date:</strong> {{ selectedRequest.request_date }}</p>
            <p><strong>Department:</strong> {{ selectedRequest.department.name }}</p>
            <p><strong>Purpose:</strong> {{ selectedRequest.purpose }}</p>
          </div>
          
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
            <p>Total Items: {{ selectedRequest.details.length }}</p>
            <p>Total Estimated Cost: ${{ totalEstimatedCost.toFixed(2) }}</p>
          </div>
          
          <h2 class="font-bold mb-2">Order Items</h2>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item Description</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Available Qty</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order Qty</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supplier</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="detail in selectedRequest.details" :key="detail.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ detail.item_description }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ availableQuantities[detail.id] }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ detail.unit }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="number"
                      v-model="form.quantities[detail.id]"
                      :max="availableQuantities[detail.id]"
                      :min="0"
                      class="w-20 border rounded p-1"
                      :class="{'border-red-500': form.quantities[detail.id] > availableQuantities[detail.id]}"
                    >
                    <span
                      v-if="form.quantities[detail.id] > availableQuantities[detail.id]"
                      class="text-xs text-red-500 block"
                    >
                      Max: {{ availableQuantities[detail.id] }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="text"
                      v-model="form.suppliers[detail.id]"
                      class="w-full border rounded p-1"
                    >
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <input
                      type="number"
                      step="0.01"
                      v-model="form.unitPrices[detail.id]"
                      class="w-24 border rounded p-1"
                    >
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    ${{ ((form.quantities[detail.id] || 0) * (form.unitPrices[detail.id] || 0)).toFixed(2) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <div class="mt-6">
            <Button
              @click="submit"
              :disabled="form.processing"
              class="bg-blue-600 hover:bg-blue-700 focus:ring-blue-500"
            >
              <span v-if="form.processing">Processing...</span>
              <span v-else>Create Order</span>
            </Button>
          </div>
        </div>
      </div>
      
      <div v-else class="bg-yellow-100 p-4 rounded">
        No available requests to convert to orders.
      </div>
    </div>
  </AppLayout>
</template>