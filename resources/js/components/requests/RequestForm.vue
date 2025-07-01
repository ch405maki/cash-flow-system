<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate } from '@/lib/utils';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { CirclePlus, Send, ChevronLeft } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';

const toast = useToast();

const props = defineProps<{
  departments: Array<any>,
  authUser: {
    id: number,
    department_id: number
  }
}>();

interface RequestItem {
  quantity: number;
  unit: string;
  item_description: string;
}

// Form data
const form = ref({
  request_date: new Date().toISOString().split('T')[0],
  purpose: '',
  status: 'pending',
  department_id: props.authUser.department_id,
  user_id: props.authUser.id,
  items: [] as RequestItem[],
});

const newItem = ref<RequestItem>({
  quantity: 1,
  unit: 'pcs',
  item_description: ''
});

const submitting = ref(false);
const showPreview = ref(false);

const unitOptions = [
  { value: 'pcs', label: 'Piece/s' },
  { value: 'box', label: 'Box/es' },
  { value: 'set', label: 'Set/s' },
  { value: 'kg', label: 'Kilogram/s' },
  { value: 'pack', label: 'Pack/s' }
];

const addItem = () => {
  if (!newItem.value.item_description) {
    toast.error('Item description is required');
    return;
  }

  form.value.items.push({ ...newItem.value });
  resetNewItem();
};

const removeItem = (index: number) => {
  form.value.items.splice(index, 1);
};

const resetNewItem = () => {
  newItem.value = {
    quantity: 1,
    unit: 'pcs',
    item_description: ''
  };
};

const showConfirmation = () => {
  if (form.value.items.length === 0) {
    toast.error('Please add at least one item');
    return;
  }

  if (!form.value.purpose) {
    toast.error('Purpose is required');
    return;
  }

  showPreview.value = true;
};

const submitRequest = async () => {
  if (submitting.value) return;

  submitting.value = true;
  try {
    const response = await axios.post('/api/requests', form.value);

    toast.success('Request created successfully!', {
      timeout: 3000
    });

    // Reset form
    form.value = {
      request_date: new Date().toISOString().split('T')[0],
      purpose: '',
      status: 'pending',
      department_id: props.authUser.department_id,
      user_id: props.authUser.id,
      items: []
    };
    resetNewItem();
    showPreview.value = false;
    setTimeout(() => {
      window.location.href = '/request';
    }, 2000);
  } catch (error) {
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        for (const field in errors) {
          toast.error(errors[field][0], {
            timeout: 5000
          });
        }
      } else {
        toast.error('An error occurred while submitting the request', {
          timeout: 3000
        });
      }
    } else {
      toast.error('An unexpected error occurred', {
        timeout: 3000
      });
    }
  } finally {
    submitting.value = false;
  }
};

const capitalizeWords = (str: string): string => {
  return str
    .toLowerCase()
    .replace(/\b\w/g, char => char.toUpperCase());
};
</script>

<template>
  <div>
    <form @submit.prevent="showConfirmation" class="space-y-6">
      <!-- Purpose -->
      <div>
        <h2 class="text-xl font-semibold">New Request</h2>
        <Label for="purpose">Purpose</Label>
        <Textarea
          id="purpose"
          v-model="form.purpose"
          placeholder="Enter purpose of the request"
          required
          class="min-h-[100px] mt-2"
        />
      </div>

      <!-- Items Section -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <h3 class="text-xl font-semibold">Request Items</h3>
        </div>

        <!-- New Item Form -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <!-- Description -->
          <div class="md:col-span-6 space-y-2">
            <Label for="item_description">Description</Label>
            <Input 
              id="item_description" 
              v-model="newItem.item_description" 
              placeholder="Item description"
              @input="newItem.item_description = capitalizeWords(newItem.item_description)"
            />
          </div>

          <!-- Quantity -->
          <div class="md:col-span-2 space-y-2">
            <Label for="quantity">Quantity</Label>
            <Input 
              id="quantity" 
              type="number" 
              v-model.number="newItem.quantity" 
              min="1" 
            />
          </div>

          <!-- Unit -->
          <div class="md:col-span-2 space-y-2">
            <Label for="unit">Unit</Label>
            <Select v-model="newItem.unit">
              <SelectTrigger class="w-full">
                <SelectValue placeholder="Select unit" />
              </SelectTrigger>
              <SelectContent>
                <SelectGroup>
                  <SelectItem 
                    v-for="unit in unitOptions" 
                    :key="unit.value" 
                    :value="unit.value"
                  >
                    {{ unit.label }}
                  </SelectItem>
                </SelectGroup>
              </SelectContent>
            </Select>
          </div>

          <!-- Add Button -->
          <div class="md:col-span-2">
            <Button 
              type="button" 
              @click="addItem" 
              class="w-full"
            >
              <CirclePlus class="mr-2 h-4 w-4" />
              Add Item
            </Button>
          </div>
        </div>

        <!-- Items Table -->
        <div class="max-h-64 overflow-y-auto border rounded-lg mt-4">
          <!-- ONE table, table-fixed so the colgroup widths are respected -->
          <table class="min-w-full table-fixed divide-y divide-gray-100">
            <!-- Column widths (same for header & rows) -->
            <colgroup>
              <col class="w-1/2" />   <!-- Description -->
              <col class="w-20"  />   <!-- Qty -->
              <col class="w-24"  />   <!-- Unit -->
              <col class="w-28"  />   <!-- Actions -->
            </colgroup>

            <!-- Sticky header lives in the SAME table -->
            <thead class="bg-gray-100 h-10 sticky top-0 z-50">
              <tr class=" border-b">
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                  Description
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                  Qty
                </th>
                <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                  Unit
                </th>
                <th class="px-3 py-2 text-right text-xs font-medium text-gray-500 uppercase">
                  Actions
                </th>
              </tr>
            </thead>

            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="(item, index) in form.items" :key="index">
                <td class="px-3 py-2 text-sm text-gray-700 whitespace-normal break-words">
                  {{ item.item_description }}
                </td>
                <td class="px-3 py-2 text-sm text-left text-gray-700">
                  {{ item.quantity }}
                </td>
                <td class="px-3 py-2 text-sm text-gray-700 text-left">
                  {{ item.unit }}
                </td>
                <td class="px-3 py-2 text-sm text-right">
                  <Button
                    variant="destructive"
                    size="sm"
                    @click="removeItem(index)"
                  >
                    Remove
                  </Button>
                </td>
              </tr>

              <tr v-if="form.items.length === 0">
                <td colspan="4" class="px-3 py-4 text-center text-sm text-gray-500">
                  No items added yet
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <Button type="submit" :disabled="submitting">
          <Send class="mr-2 h-4 w-4" />
          {{ submitting ? 'Submitting...' : 'Review Request' }}
        </Button>
      </div>
    </form>

    <!-- Preview Dialog -->
  <Dialog v-model:open="showPreview">
    <DialogContent class="max-h-screen  overflow-y-auto sm:max-w-2xl">
      <DialogHeader>
        <DialogTitle>Review Your Request</DialogTitle>
      </DialogHeader>
      <div class="space-y-2">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 sm:gap-4">
          <div>
            <Label class="text-sm sm:text-base text-muted-foreground">Request Date</Label>
            <p class="text-sm sm:text-base">{{ formatDate(form.request_date) }}</p>
          </div>
          <div>
            <Label class="text-sm sm:text-base text-muted-foreground">Department</Label>
            <p class="text-sm sm:text-base">{{ departments.find(d => d.id === form.department_id)?.department_name }}</p>
          </div>
        </div>

        <div>
          <Label class="text-sm sm:text-base text-muted-foreground">Purpose</Label>
          <p class="text-sm sm:text-base whitespace-pre-line">{{ form.purpose }}</p>
        </div>

        <div class="space-y-2">
          <Label class="text-sm sm:text-base text-muted-foreground">Items</Label>
          <div class="border rounded-lg overflow-hidden">
            <!-- Scrollable container with max height -->
            <div class="max-h-[60vh] overflow-y-auto">
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                      <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Description
                      </th>
                      <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Quantity
                      </th>
                      <th class="px-4 py-2 text-left text-xs sm:text-sm font-medium text-gray-500 uppercase tracking-wider">
                        Unit
                      </th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(item, index) in form.items" :key="index">
                      <td class="px-4 py-3 whitespace-normal text-xs sm:text-sm text-gray-500">
                        {{ item.item_description }}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                        {{ item.quantity }}
                      </td>
                      <td class="px-4 py-3 whitespace-nowrap text-xs sm:text-sm text-gray-500">
                        {{ unitOptions.find(u => u.value === item.unit)?.label || item.unit }}
                      </td>
                    </tr>
                    <tr v-if="form.items.length === 0">
                      <td colspan="3" class="px-4 py-4 text-center text-sm text-gray-500">
                        No items added yet
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
      </div>

      <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-between sm:gap-0">
        <Button variant="outline" @click="showPreview = false" class="w-full sm:w-auto">
          <ChevronLeft class="h-4 w-4 mr-2" />
          Back to Edit
        </Button>
        <Button type="button" @click="submitRequest" :disabled="submitting" class="w-full sm:w-auto">
          <Send class="h-4 w-4 mr-2" />
          {{ submitting ? 'Submitting...' : 'Submit Request' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
  </div>
</template>