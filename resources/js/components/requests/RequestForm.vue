<script setup lang="ts">
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { formatDateTime } from '@/lib/utils';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { CirclePlus, Trash, ShoppingBasket, Send, ChevronLeft, Check, ChevronsUpDown } from 'lucide-vue-next';
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table';
import PageHeader from '@/components/PageHeader.vue';
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from "@/components/ui/combobox"

const toast = useToast();

const props = defineProps<{
  departments: Array<any>,
  authUser: {
    id: number,
    department_id: number
  }
  reorderRequest?: any
}>();

// NEW: Products from inventory
const inventoryProducts = ref<Array<{id: number, product_code: string, name: string}>>([]);
const isLoadingProducts = ref(false);

interface RequestItem {
  item_id?: number;
  product_code?: string;
  quantity: number;
  unit: string;
  item_description: string;
  editing?: boolean;
  original?: RequestItem;
}

const editItem = (index: number) => {
  form.value.items.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i);
    }
  });
  
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
  items: [] as RequestItem[],
});

// NEW: Selected product from combobox
const selectedProduct = ref<{id: number, product_code: string, name: string} | null>(null);

const newItem = ref<RequestItem>({
  item_id: undefined,
  product_code: '',
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

// NEW: Handle product selection from combobox
const onProductSelect = (product: typeof inventoryProducts.value[0]) => {
  selectedProduct.value = product;
  newItem.value.item_id = product.id;
  newItem.value.product_code = product.product_code;
  newItem.value.item_description = product.name;
};

// NEW: Clear selected product
const clearSelectedProduct = () => {
  selectedProduct.value = null;
  newItem.value.item_id = undefined;
  newItem.value.product_code = '';
  newItem.value.item_description = '';
};

const addItem = () => {
  if (!newItem.value.item_description) {
    toast.error('Please select a product or enter item description');
    return;
  }

  if (!newItem.value.quantity || newItem.value.quantity <= 0) {
    toast.error('Quantity must be greater than 0');
    return;
  }

  form.value.items.push({ ...newItem.value });
  resetNewItem();
};

const removeItem = (index: number) => {
  form.value.items.splice(index, 1);
};

const resetNewItem = () => {
  selectedProduct.value = null;
  newItem.value = {
    item_id: undefined,
    product_code: '',
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

// NEW: Load products from inventory
const loadInventoryProducts = async () => {
  isLoadingProducts.value = true;
  try {
    const response = await axios.get('/api/inventory/products');
    if (response.data.success) {
      inventoryProducts.value = response.data.data;
      console.log('Loaded products:', inventoryProducts.value);
    } else {
      toast.error('Failed to load products from inventory');
    }
  } catch (error) {
    console.error('Error loading products:', error);
    toast.error('Could not connect to inventory system');
  } finally {
    isLoadingProducts.value = false;
  }
};

onMounted(() => {
  // Load products from inventory
  loadInventoryProducts();
  
  // First try to use the prop from Laravel
  if (props.reorderRequest && props.reorderRequest.id) {
    populateFormFromReorder(props.reorderRequest);
    sessionStorage.removeItem('reorderRequest');
  } 
  else {
    const storedReorder = sessionStorage.getItem('reorderRequest');
    if (storedReorder) {
      try {
        const reorderData = JSON.parse(storedReorder);
        if (reorderData && reorderData.details) {
          populateFormFromReorder(reorderData);
        }
      } catch (error) {
        console.error('Error parsing reorder data from session storage:', error);
        sessionStorage.removeItem('reorderRequest');
      }
    }
  }
});

const populateFormFromReorder = (reorderRequest: any) => {
  form.value.items = [];
  form.value.purpose = `REORDER: ${reorderRequest.purpose || ''}`;
  
  if (reorderRequest.details && reorderRequest.details.length > 0) {
    reorderRequest.details.forEach((detail: any) => {
      form.value.items.push({
        item_id: detail.item_id,  // Preserve item_id if exists
        product_code: detail.product_code,
        quantity: detail.released_quantity,
        unit: detail.unit,
        item_description: detail.item_description,
        editing: false
      });
    });
    
    toast.success(`Populated ${reorderRequest.details.length} items from request ${reorderRequest.request_no}`);
  }
};
</script>

<template>
  <div class="space-y-4">
    <PageHeader 
      :title="reorderRequest ? 'Re-order Request' : 'New Request'" 
      :subtitle="reorderRequest ? `${form.items.length} items have been pre-populated from request #${reorderRequest.request_no}. You can modify them before submitting.` : 'Create a new request with items and details'"
    />
    <form @submit.prevent="showConfirmation" class="space-y-4">
      <!-- Purpose -->
      <div>
        <Label for="purpose" required>Purpose</Label>
        <Textarea
          id="purpose"
          v-model="form.purpose"
          placeholder="Enter purpose of the request"
          required
          class="min-h-[100px] mt-2"
        />
      </div>

      <!-- Items Section -->
      <PageHeader 
        title="Request Items" 
        :subtitle="reorderRequest ? `items ready for review, edit and add items to your request` : 'Select products from inventory or add custom items'"
      />

      <!-- New Item Form -->
      <div class="grid grid-cols-1 gap-4">
                <!-- Product Combobox (from inventory) -->
        <div class="space-y-2">
          <Label>Select Product from Inventory (Optional)</Label>
          <Combobox 
            v-model="selectedProduct" 
            @update:model-value="onProductSelect"
            :filter-function="(query, product) => {
              return product.name.toLowerCase().includes(query.toLowerCase()) ||
                    product.product_code.toLowerCase().includes(query.toLowerCase())
            }"
            class="max-w-lg"
          >
            <ComboboxAnchor class="flex items-center w-full">
              <div class="relative w-full items-center">
                <ComboboxInput
                  placeholder="Search products by name or code..."
                  :display-value="(val) => val ? `${val.name} (${val.product_code})` : ''"
                  class="py-2.5 px-4 pr-10 text-sm w-full"
                />
                <ComboboxTrigger
                  class="absolute end-0 inset-y-0 flex items-center justify-center px-3"
                >
                  <ChevronsUpDown class="size-4 text-muted-foreground" />
                </ComboboxTrigger>
              </div>
              <div v-if="selectedProduct" class="text-sm text-green-600">
                <Button variant="destructive" size="sm" @click="clearSelectedProduct" class="ml-2">Clear</Button>
              </div>
            </ComboboxAnchor>

            <ComboboxList class="min-w-[400px] w-full">
              <ComboboxEmpty>
                <div class="text-sm text-muted-foreground px-4 py-6 text-center">
                  <span v-if="isLoadingProducts">Loading products...</span>
                  <span v-else>No products found</span>
                </div>
              </ComboboxEmpty>

              <ComboboxGroup>
                <ComboboxItem
                  v-for="product in inventoryProducts"
                  :key="product.id"
                  :value="product"
                  class="py-3 px-4"
                >
                  <div class="flex flex-col">
                    <span class="font-medium text-sm">{{ product.name }}</span>
                  </div>
                  <ComboboxItemIndicator>
                    <Check class="ml-auto h-4 w-4" />
                  </ComboboxItemIndicator>
                </ComboboxItem>
              </ComboboxGroup>
            </ComboboxList>
          </Combobox>
        </div>

        <!-- OR Divider -->
        <div class="relative">
          <div class="absolute inset-0 flex items-center">
            <span class="w-full border-t" />
          </div>
          <div class="relative flex justify-center text-xs uppercase">
            <span class="bg-background px-2 text-muted-foreground">Or add custom item</span>
          </div>
        </div>

        <!-- Manual Item Entry -->
        <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
          <!-- Description -->
          <div class="md:col-span-6 space-y-2">
            <Label for="item_description" required>Description</Label>
            <Input 
              id="item_description" 
              v-model="newItem.item_description" 
              placeholder="Item description"
              :disabled="!!selectedProduct"
            />
          </div>

          <!-- Quantity -->
          <div class="md:col-span-2 space-y-2">
            <Label for="quantity" required>Quantity</Label>
            <Input 
              id="quantity" 
              type="number" 
              v-model.number="newItem.quantity" 
              min="1" 
            />
          </div>

          <!-- Unit -->
          <div class="md:col-span-2 space-y-2">
            <Label for="unit" required>Unit</Label>
            <Combobox v-model="newItem.unit">
              <ComboboxAnchor>
                <div class="relative w-full items-center">
                  <ComboboxInput
                    placeholder="Select or type a unit..."
                    :display-value="(val) => val || ''"
                    @update:model-value="(val) => newItem.unit = val"
                    @change="(e) => newItem.unit = e.target.value"
                  />
                  <ComboboxTrigger
                    class="absolute end-0 inset-y-0 flex items-center justify-center px-3"
                  >
                    <ChevronsUpDown class="size-4 text-muted-foreground" />
                  </ComboboxTrigger>
                </div>
              </ComboboxAnchor>
              <ComboboxList>
                <ComboboxEmpty>
                  <span class="text-sm text-muted-foreground px-2 py-1">
                    No match found — type to add custom unit.
                  </span>
                </ComboboxEmpty>
                <ComboboxGroup>
                  <ComboboxItem
                    v-for="unit in unitOptions"
                    :key="unit.value"
                    :value="unit.value"
                  >
                    {{ unit.label }}
                    <ComboboxItemIndicator>
                      <Check class="ml-auto h-4 w-4" />
                    </ComboboxItemIndicator>
                  </ComboboxItem>
                </ComboboxGroup>
              </ComboboxList>
            </Combobox>
          </div>

          <!-- Add Button -->
          <div class="sm:col-span-2 flex justify-end">
            <Button 
              type="button" 
              @click="addItem" 
              :disabled="!newItem.item_description.trim() || !newItem.quantity || !newItem.unit || submitting"
            >
              <ShoppingBasket class="h-4 w-4" />
              <span class="sr-only sm:not-sr-only">Add</span>
            </Button>
          </div>
        </div>
      </div>

      <!-- Items Table -->
      <div v-if="form.items.length != 0" class="max-h-64 overflow-y-auto mt-4">
        <Table>
          <TableHeader class="sticky top-0">
            <TableRow>
              <TableHead class="w-1/2">Description</TableHead>
              <TableHead class="w-20">Qty</TableHead>
              <TableHead class="w-24">Unit</TableHead>
              <TableHead class="w-24">Product Code</TableHead>
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
              
              <TableCell>
                <div v-if="!item.editing">
                  <span v-if="item.product_code" class="text-xs text-muted-foreground">{{ item.product_code }}</span>
                  <span v-else class="text-xs text-gray-400">-</span>
                </div>
                <Input 
                  v-else
                  v-model="item.product_code"
                  placeholder="Product code"
                  @click.stop
                  class="w-full"
                />
              </TableCell>
              
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
          </TableBody>
        </Table>
      </div>
      <div v-if="form.items.length === 0">
        <Table class="flex flex-col items-center justify-center py-6 space-y-2">
          <ShoppingBasket class="h-6 w-6" />
          <p>No items added yet</p>
        </Table>
      </div>

      <!-- Submit Button -->
      <div v-if="form.items.length != 0" class="flex justify-end">
        <Button type="submit" :disabled="submitting">
          <Send class="h-4 w-4" />
          {{ submitting ? 'Submitting...' : 'Review and Submit Request' }}
        </Button>
      </div>
    </form>

    <!-- Preview Dialog -->
    <Dialog v-model:open="showPreview">
      <DialogContent class="max-h-screen overflow-y-auto sm:max-w-2xl">
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
            <div class="overflow-hidden">
              <div class="max-h-[60vh] overflow-y-auto">
                <Table>
                  <TableHeader class="sticky top-0">
                    <TableRow>
                      <TableHead>Description</TableHead>
                      <TableHead>Quantity</TableHead>
                      <TableHead>Unit</TableHead>
                      <TableHead>Product Code</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    <TableRow v-for="(item, index) in form.items" :key="index">
                      <TableCell>{{ item.item_description }}</TableCell>
                      <TableCell>{{ item.quantity }}</TableCell>
                      <TableCell>
                        {{ unitOptions.find(u => u.value === item.unit)?.label || item.unit }}
                      </TableCell>
                      <TableCell>
                        <span class="text-xs">{{ item.product_code || '-' }}</span>
                      </TableCell>
                    </TableRow>
                    <TableRow v-if="form.items.length === 0">
                      <TableCell :colspan="4" class="text-center">
                        <ShoppingBasket />
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