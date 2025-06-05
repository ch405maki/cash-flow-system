<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Printer, ListChecks } from 'lucide-vue-next';
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

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
    department: { id: number, name: string };
    details: RequestDetail[];
  }>;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: 'To Order List', href: '/' }
];

// Flatten all request details with department_id
const allRequestDetails = computed(() => {
  return props.requests.flatMap(request => 
    request.details.map(detail => ({
      ...detail,
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

const printArea = () =>{
  const printContents = document.getElementById('print-section')?.innerHTML;
  const originalContents = document.body.innerHTML;

  if (printContents) {
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
    location.reload();
  } else {
    console.error('Print section not found');
  }
}
</script>

<template>
  <Head title="To Order List" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold mb-4">Create Request to Order</h1>
        <Button size="sm" @click="printArea"> <Printer />Print List</Button>
      </div>
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
        
        <div class="overflow-x-auto rounded-md border">
          <Table>
            <TableCaption>A list of items to order.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[40px]">
                  Select
                </TableHead>
                <TableHead>Item Description</TableHead>
                <TableHead class="text-right w-[40px]">Unit</TableHead>
                <TableHead class="text-right w-[40px]">
                  Quantity
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="detail in allRequestDetails" :key="detail.id">
                <TableCell class="font-medium">
                  <input
                    type="checkbox"
                    v-model="form.selectedItems"
                    :value="detail.id"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  >
                </TableCell>
                <TableCell>{{ detail.item_description }}</TableCell>
                <TableCell class="text-right">{{ detail.unit }}</TableCell>
                <TableCell class="text-right">
                  {{ detail.quantity }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        
        <div class="mt-6 flex space-x-2">
          <Button
            @click="submit"
            :disabled="form.processing || form.selectedItems.length === 0"
          >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Create Order</span>
          </Button>
          <Button
            variant="outline"
            @click="submit"
            :disabled="form.processing || form.selectedItems.length === 0"
          >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Mark As Processed</span>
          </Button>
        </div>
      </div>
      
      <div v-else class="bg-yellow-100 p-4 rounded">
        No available items to order.
      </div>
    </div>

    <!-- printed area -->
    <div id="print-section"  class="hidden print:block">
        <div :bordered="false">
            <FormHeader text="Request to Order List" :bordered="false" />
        </div>
        <div class="overflow-x-auto rounded-md border">
          <Table>
            <TableCaption>A list of items to order.</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead>Item Description</TableHead>
                <TableHead class="text-right w-[40px]">Quantity</TableHead>
                <TableHead class="text-right w-[40px]">Unit</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="detail in allRequestDetails" :key="detail.id">
                <TableCell>{{ detail.item_description }}</TableCell>
                <TableCell class="text-right">
                  {{ detail.quantity }}
                </TableCell>
                <TableCell class="text-right">{{ detail.unit }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
    </div>
  </AppLayout>
</template>