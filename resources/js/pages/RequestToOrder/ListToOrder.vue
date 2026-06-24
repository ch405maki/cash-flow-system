<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button'
import { ref, computed, onMounted } from 'vue';
import FormHeader from '@/components/reports/header/formHeder.vue'
import { Printer, Rocket } from 'lucide-vue-next';
import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
import PageHeader from '@/components/PageHeader.vue';
import { Send } from 'lucide-vue-next';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { Skeleton } from '@/components/ui/skeleton'
import { requestToOrderService } from '@/services/requestToOrderService';
import { useToast } from 'vue-toastification'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

const toast = useToast()

interface RequestDetail {
  id: number;
  request_id: number;
  department_id: number;
  quantity: number;
  released_quantity: number;
  unit: string;
  item_description: string;
}

const props = defineProps<{
  requests?: Array<any>
}>()

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Request', href: '/request-to-order' },
  { title: 'Purchase Request List', href: '/' }
];

const loading = ref(true)
const requests = ref<any[]>([])

const allRequestDetails = computed(() => {
  return requests.value.flatMap((request: any) => 
    request.details.map((detail: any) => ({
      ...detail,
      available_quantity: detail.quantity - detail.released_quantity
    }))
  );
});

const selectedItems = ref<number[]>([])
const quantities = ref<Record<number, number>>({})
const notes = ref('')
const submitting = ref(false)

onMounted(async () => {
  try {
    const response = await requestToOrderService.listData()
    requests.value = response.data.requests || []
    selectedItems.value = allRequestDetails.value.map(d => d.id)
    allRequestDetails.value.forEach((detail: any) => {
      quantities.value[detail.id] = detail.available_quantity
    })
  } catch (error) {
    console.error('Failed to load list data:', error)
  } finally {
    loading.value = false
  }
})

const toggleSelectAll = (checked: boolean) => {
  selectedItems.value = checked ? allRequestDetails.value.map(d => d.id) : [];
};

const toggleItemSelection = (id: number, checked: boolean) => {
  if (checked) {
    selectedItems.value.push(id);
  } else {
    selectedItems.value = selectedItems.value.filter(itemId => itemId !== id);
  }
};

const submit = async () => {
  if (selectedItems.value.length === 0) {
    alert('Please select at least one item');
    return;
  }

  submitting.value = true

  try {
    const items = selectedItems.value.map(id => {
      const detail = allRequestDetails.value.find(d => d.id === id)!;
      return {
        request_id: detail.request_id,
        department_id: detail.department_id,
        detail_id: detail.id,
        quantity: quantities.value[id],
        unit: detail.unit,
        item_description: detail.item_description
      };
    });

    await requestToOrderService.store({
      notes: notes.value,
      items,
      request_ids: [...new Set(items.map(item => item.request_id))],
      department_ids: [...new Set(items.map(item => item.department_id))]
    })

    toast.success('Order created successfully')
    router.visit('/request-to-order')
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to create order')
  } finally {
    submitting.value = false
  }
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
      <div v-if="loading" class="space-y-4">
        <Skeleton class="h-8 w-64" />
        <Skeleton class="h-64 w-full" />
      </div>

      <template v-else>
        <div class="flex justify-between items-center">
          <PageHeader 
            title="Create Purchase Request" 
            subtitle="Fill out request details and specify required items"
          />
          <Button size="sm" @click="printArea"> <Printer />Print List</Button>
        </div>

        <div v-if="allRequestDetails.length > 0">
          <div class="mb-4">
            <Label for="order-notes" class="block font-medium">Notes</Label>
            <Textarea
              id="order-notes"
              v-model="notes"
              class="w-full"
              rows="3"
              placeholder="Add any notes for this order..."
            />
          </div>
          
          <div class="mb-2">
            <div class="flex items-center space-x-2">
              <Checkbox
                :checked="selectedItems.length === allRequestDetails.length"
                :indeterminate="selectedItems.length > 0 && selectedItems.length < allRequestDetails.length"
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
                <TableHead class="w-[40px]">Select</TableHead>
                <TableHead>Item Description</TableHead>
                <TableHead class="text-right w-[40px]">Unit</TableHead>
                <TableHead class="text-right w-[40px]">Quantity</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="detail in allRequestDetails" :key="detail.id">
                <TableCell class="font-medium">
                  <Checkbox
                    :checked="selectedItems.includes(detail.id)"
                    @update:checked="(checked) => toggleItemSelection(detail.id, checked)"
                    class="h-4 w-4"
                  />
                </TableCell>
                <TableCell>{{ detail.item_description }}</TableCell>
                <TableCell class="text-right">{{ detail.unit }}</TableCell>
                <TableCell class="text-right">{{ detail.quantity }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
          
          <div class="mt-6 flex space-x-2">
            <Button
              @click="submit"
              :disabled="submitting || selectedItems.length === 0"
            >
              <Send />
              <span v-if="submitting">Processing...</span>
              <span v-else>Submit Request</span>
            </Button>
            <Button
              variant="outline"
              @click="submit"
              :disabled="submitting || selectedItems.length === 0"
            >
              <span v-if="submitting">Processing...</span>
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
      </template>
    </div>

    <div id="print-section" class="hidden print:block">
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
                <TableCell class="text-right">{{ detail.quantity }}</TableCell>
                <TableCell class="text-right">{{ detail.unit }}</TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
    </div>
  </AppLayout>
</template>
