<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDate, formatDateTime } from '@/lib/utils';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { CirclePlus, Trash, Send, ChevronLeft } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';


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
  editing?: boolean; // Add this line
  original?: RequestItem; // To store original values when editing
}

const editItem = (index: number) => {
  // Exit any other editing modes first
  form.value.items.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i);
    }
  });
  
  // Set editing mode for this item
  form.value.items[index].editing = true;
  form.value.items[index].original = { ...form.value.items[index] };
};

const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Enter') {
    saveEdit(index);
  } else if (event.key === 'Escape') {
    cancelEdit(index);
  }
};

const saveEdit = (index: number) => {
  form.value.items[index].editing = false;
  delete form.value.items[index].original;
};

const cancelEdit = (index: number) => {
  if (form.value.items[index].original) {
    form.value.items[index] = { ...form.value.items[index].original };
  }
  form.value.items[index].editing = false;
  delete form.value.items[index].original;
};

// Form data
const form = ref({
  request_date: new Date().toISOString(), 
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
      request_date: new Date().toISOString(),
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
  <div class="space-y-4">
    <PageHeader 
      title="New Request" 
      subtitle="Create a new request with items and details"
    />
    <form @submit.prevent="showConfirmation" class="space-y-6">
      <!-- Purpose -->
      <div>
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
      <div>
        <PageHeader 
          title="Request Items" 
        />

        <!-- New Item Form -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <!-- Description -->
          <div class="md:col-span-6 space-y-2">
            <Label for="item_description">Description</Label>
            <Input 
              id="item_description" 
              v-model="newItem.item_description" 
              placeholder="Item description"
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
              :disabled="!newItem.item_description.trim() || !newItem.quantity || !newItem.unit || submitting"
            >
              <CirclePlus class="h-4 w-4" />
              Add Item
            </Button>
          </div>
        </div>

        <!-- Items Table -->
        <div class="max-h-64 overflow-y-auto mt-4">
          <Table>
            <TableHeader class="sticky top-0">
              <TableRow>
                <TableHead class="w-1/2">Description</TableHead>
                <TableHead class="w-20">Qty</TableHead>
                <TableHead class="w-24">Unit</TableHead>
                <TableHead 
                  v-if="form.items.some(item => item.editing)" 
                  class="w-28 text-right"
                >
                  Actions
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="(item, index) in form.items"
                :key="index"
                @click="editItem(index)"
                :class="{
                  'cursor-pointer': true,
                }"
              >
                <!-- Description Column -->
                <TableCell>
                  <div v-if="!item.editing">{{ item.item_description }}</div>
                  <Input 
                    v-else
                    v-model="item.item_description"
                    @click.stop
                    @keydown="handleKeyDown($event, index)"
                    @input="item.item_description = capitalizeWords(item.item_description)"
                    class="w-full"
                  />
                </TableCell>
                
                <!-- Quantity Column -->
                <TableCell>
                  <div v-if="!item.editing">{{ item.quantity }}</div>
                  <Input 
                    v-else
                    type="number" 
                    v-model.number="item.quantity" 
                    min="1"
                    @click.stop
                    @keydown="handleKeyDown($event, index)"
                    class="w-full"
                  />
                </TableCell>
                
                <!-- Unit Column -->
                <TableCell>
                  <div v-if="!item.editing">
                    {{ unitOptions.find(u => u.value === item.unit)?.label || item.unit }}
                  </div>
                  <Select 
                    v-else 
                    v-model="item.unit" 
                    @click.stop
                  >
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
                </TableCell>
                
                <!-- Actions Column -->
                <TableCell 
                  v-if="item.editing"
                  class="flex justify-end space-x-2"
                >
                  <Button
                    variant="outline"
                    size="sm"
                    @click.stop="saveEdit(index)"
                  >
                    Save
                  </Button>
                  <Button
                    variant="outline"
                    size="sm"
                    @click.stop="cancelEdit(index)"
                  >
                    Cancel
                  </Button>
                  <Button
                    variant="destructive"
                    size="sm"
                    @click.stop="removeItem(index)"
                  >
                    <Trash />
                  </Button>
                </TableCell>
              </TableRow>

              <TableRow v-if="form.items.length === 0">
                <TableCell :colspan="4" class="text-center text-sm text-gray-500">
                  No items added yet
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <Button type="submit" :disabled="submitting">
          <Send class="mr-2 h-4 w-4" />
          {{ submitting ? 'Submitting...' : 'Review and Submit Request' }}
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
              <p class="text-sm sm:text-base">{{ formatDateTime(form.request_date) }}</p>
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
            <!-- In the Preview Dialog section -->
            <div class="overflow-hidden">
              <div class="max-h-[60vh] overflow-y-auto">
                <Table>
                  <TableHeader class="sticky top-0">
                    <TableRow>
                      <TableHead>Description</TableHead>
                      <TableHead>Quantity</TableHead>
                      <TableHead>Unit</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, index) in form.items" :key="index">
                      <TableCell>{{ item.item_description }}</TableCell>
                      <TableCell>{{ item.quantity }}</TableCell>
                      <TableCell>
                        {{ unitOptions.find(u => u.value === item.unit)?.label || item.unit }}
                      </TableCell>
                    </TableRow>
                    <TableRow v-if="form.items.length === 0">
                      <TableCell :colspan="3" class="text-center">
                        No items added yet
                      </TableCell>
                    </TableRow>
                  </TableBody>
                </Table>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="flex flex-col-reverse gap-2 sm:flex-row justify-between space-x-2 sm:gap-0">
          <Button variant="outline" @click="showPreview = false" class="w-full sm:w-auto">
            <ChevronLeft class="h-4 w-4" />
            Back to Edit
          </Button>
          <Button type="button" @click="submitRequest" :disabled="submitting" class="w-full sm:w-auto">
            <Send class="h-4 w-4" />
            {{ submitting ? 'Submitting...' : 'Submit Request' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>