<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Printer, Rocket } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import PageHeader from '@/components/PageHeader.vue';
import { Send } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
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
  { title: 'Purchase Request', href: '/request-to-order' },
  { title: 'Purchase Request List', href: '/' }
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

const toggleSelectAll = (checked: boolean) => {
  form.selectedItems = checked ? allRequestDetails.value.map(d => d.id) : [];
  form.request_ids = checked 
    ? [...new Set(allRequestDetails.value.map(d => d.request_id))] 
    : [];
  form.department_ids = checked
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

const toggleItemSelection = (id: number, checked: boolean) => {
  if (checked) {
    form.selectedItems.push(id);
  } else {
    form.selectedItems = form.selectedItems.filter(itemId => itemId !== id);
  }
};
</script>

<template>
  <Head title="To Order List" />
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
      <div class="flex justify-between items-center">
        <PageHeader 
          title="Create Purchase Request" 
          subtitle="Fill out request details and specify required items"
        />
        <Button size="sm" @click="printArea"> <Printer />Print List</Button>
      </div>
      <div v-if="Object.keys(form.errors).length > 0" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        <p v-for="(error, field) in form.errors" :key="field" class="text-sm">
          {{ error }}
        </p>
      </div>
      
      <div v-if="allRequestDetails.length > 0">
        <div class="mb-4">
          <Label for="order-notes" class="block font-medium">Notes</Label>
          <Textarea
            id="order-notes"
            v-model="form.notes"
            class="w-full"
            rows="3"
            placeholder="Add any notes for this order..."
          />
        </div>
        
        <div class="mb-2">
          <div class="flex items-center space-x-2">
            <Checkbox
              :checked="form.selectedItems.length === allRequestDetails.length"
              :indeterminate="form.selectedItems.length > 0 && form.selectedItems.length < allRequestDetails.length"
              @update:checked="toggleSelectAll"
            />
            <Label class="text-sm font-medium">
              Select All Items
            </Label>
          </div>
        </div>
        
        <Table>
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
                <Checkbox
                  :checked="form.selectedItems.includes(detail.id)"
                  @update:checked="(checked) => toggleItemSelection(detail.id, checked)"
                  class="h-4 w-4"
                />
              </TableCell>
              <TableCell>{{ detail.item_description }}</TableCell>
              <TableCell class="text-right">{{ detail.unit }}</TableCell>
              <TableCell class="text-right">
                {{ detail.quantity }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
        
        <div class="mt-6 flex space-x-2">
          <Button
            @click="submit"
            :disabled="form.processing || form.selectedItems.length === 0"
          >
            <Send />
            <span v-if="form.processing">Processing...</span>
            <span v-else>Submit Request</span>
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
      
      <div v-else>
        <Alert variant="success" class="relative pr-10">
          <Rocket class="h-4 w-4 text-green-500" />
          <AlertTitle>Note</AlertTitle>
          <AlertDescription>
            No available items to order.
          </AlertDescription>
        </Alert>
      </div>
    </div>

    <!-- printed area -->
    <div id="print-section"  class="hidden print:block">
        <div :bordered="false">
            <FormHeader text="Request to Order List" :bordered="false" />
        </div>
        <div class="overflow-x-auto rounded-md border">
          <Table>
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