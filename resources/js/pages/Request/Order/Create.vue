<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

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
  items: Array<{
    request_id: number;
    department_id: number;
    detail_id: number;
    quantity: number;
    unit: string;
    item_description: string;
  }>;
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

const form = useForm<FormData>({
  notes: '',
  items: []
});

// Initialize form with all available items
form.items = props.requests.flatMap(request => 
  request.details.map(detail => ({
    request_id: request.id,
    department_id: request.department_id, // Include department_id
    detail_id: detail.id,
    quantity: detail.quantity - detail.released_quantity,
    unit: detail.unit,
    item_description: detail.item_description
  }))
);

const submit = () => {
  // Filter out items with zero quantity
  const validItems = form.items.filter(item => item.quantity > 0);
  
  if (validItems.length === 0) {
    alert('Please include at least one item with positive quantity');
    return;
  }

  form.post(route('request-to-orders.store'), {
    data: {
      notes: form.notes,
      items: validItems
    }
  });
};
</script>

<template>
  <Head title="Create Request To Order" />
  
  <AppLayout>
    <div class="p-4">
      <h1 class="text-2xl font-bold mb-6">Create Request to Order</h1>
      
      <div class="mb-4">
        <label class="block font-medium mb-2">Notes</label>
        <textarea v-model="form.notes" class="w-full p-2 border rounded"></textarea>
      </div>
      
      <div class="overflow-x-auto mb-6">
        <table class="w-full border-collapse">
          <thead>
            <tr class="bg-gray-100">
              <th class="p-3 text-left">Request #</th>
              <th class="p-3 text-left">Department</th>
              <th class="p-3 text-left">Item Description</th>
              <th class="p-3 text-left">Unit</th>
              <th class="p-3 text-left">Quantity</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in form.items" :key="index" class="border-b">
              <td class="p-3">
                {{ requests.find(r => r.id === item.request_id)?.request_no }}
              </td>
              <td class="p-3">
                {{ requests.find(r => r.id === item.request_id)?.department.name }}
              </td>
              <td class="p-3">{{ item.item_description }}</td>
              <td class="p-3">{{ item.unit }}</td>
              <td class="p-3">{{ item.quantity }}</td>
            </tr>
          </tbody>
        </table>
      </div>
      
      <Button @click="submit" :disabled="form.processing" class="bg-blue-600 text-white px-4 py-2 rounded">
        {{ form.processing ? 'Creating Order...' : 'Create Order' }}
      </Button>
    </div>
  </AppLayout>
</template>