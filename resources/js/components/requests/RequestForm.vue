<script setup lang="ts">
import { ref } from 'vue';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';

const toast = useToast();

const props = defineProps<{
  departments: Array<any>,
  authUser: {
    id: number,
    department_id: number
  }
}>();

// Form data
const form = ref({
  request_date: new Date().toISOString().split('T')[0],
  purpose: '',
  status: 'pending',
  department_id: props.authUser.department_id,
  user_id: props.authUser.id,
  items: [
    {
      quantity: 1,
      unit: 'pcs',
      item_description: ''
    }
  ]
});


const statusOptions = [
  { value: 'pending', label: 'Pending' },
  { value: 'approved', label: 'Approved' },
  { value: 'rejected', label: 'Rejected' }
];

const unitOptions = [
  { value: 'pcs', label: 'Pieces' },
  { value: 'box', label: 'Box' },
  { value: 'set', label: 'Set' },
  { value: 'kg', label: 'Kilogram' }
];

const addItem = () => {
  form.value.items.push({
    quantity: 1,
    unit: 'pcs',
    item_description: ''
  });
};

const removeItem = (index: number) => {
  form.value.items.splice(index, 1);
};

const submitRequest = async () => {
  try {
    const response = await axios.post('/api/requests', form.value);
    
    toast.success('Request created successfully!', {
      timeout: 3000
    });
    
    // Reset form after successful submission
    form.value = {
      request_date: new Date().toISOString().split('T')[0],
      purpose: '',
      status: 'pending',
      department_id: props.authUser.department_id,    
      user_id: props.authUser.id,
      items: [
        {
          quantity: 1,
          unit: 'pcs',
          item_description: ''
        }
      ]
    };
    
  } catch (error) {
    if (axios.isAxiosError(error)) {
      if (error.response?.status === 422) {
        // Handle validation errors
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
  }
};
</script>

<template>
  <div>
    <form @submit.prevent="submitRequest" class="space-y-6">
      <!-- Purpose -->
      <div>
        <Label for="purpose">Purpose</Label>
        <Textarea
          id="purpose"
          v-model="form.purpose"
          placeholder="Enter purpose of the request"
          required
          class="min-h-[100px]"
        />
      </div>

      <!-- Items List -->
      <div>
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-medium">Request Items</h3>
          <Button type="button" variant="outline" @click="addItem">
            Add Item
          </Button>
        </div>

        <div v-for="(item, index) in form.items" :key="index" class="mb-6 space-y-4 border-b pb-6">
          <div class="grid grid-cols-1 gap-4 md:grid-cols-12">
            <!-- Quantity -->
            <div class="md:col-span-2">
              <Label :for="`quantity-${index}`">Quantity</Label>
              <Input
                :id="`quantity-${index}`"
                v-model.number="item.quantity"
                type="number"
                min="1"
                required
              />
            </div>

            <!-- Unit -->
            <div class="md:col-span-2">
              <Label :for="`unit-${index}`">Unit</Label>
              <Select v-model="item.unit" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select unit" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="unit in unitOptions"
                    :key="unit.value"
                    :value="unit.value"
                  >
                    {{ unit.label }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <!-- Description -->
            <div class="md:col-span-7">
              <Label :for="`description-${index}`">Description</Label>
              <Input
                :id="`description-${index}`"
                v-model="item.item_description"
                placeholder="Item description"
                required
              />
            </div>

            <!-- Remove Button -->
            <div class="md:col-span-1 flex items-end">
              <Button
                type="button"
                variant="destructive"
                size="sm"
                @click="removeItem(index)"
                :disabled="form.items.length <= 1"
              >
                Remove
              </Button>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Button -->
      <div class="flex justify-end">
        <Button type="submit">
          Submit Request
        </Button>
      </div>
    </form>
  </div>
</template>