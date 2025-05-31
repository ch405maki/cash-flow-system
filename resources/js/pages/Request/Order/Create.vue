<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

const toast = useToast()

interface OrderItem {
  request_id: string
  department_id: string
  quantity: number
  unit: string
  item_description: string
}

const breadcrumbs = [
  { title: 'Create Request To Order', href: '/request-to-order/create' },
]

const form = useForm({
  notes: '',
  newItem: {
    request_id: '',
    department_id: '',
    quantity: 1,
    unit: '',
    item_description: ''
  },
  items: [] as OrderItem[]
})

// These should ideally be fetched from your backend
const departments = [
  { id: '1', name: 'IT Department' },
  { id: '2', name: 'Finance' },
  { id: '3', name: 'Operations' }
]

const requests = [
  { id: '1', name: 'Request #1001' },
  { id: '2', name: 'Request #1002' },
  { id: '3', name: 'Request #1003' }
]

const addItem = () => {
  if (!form.newItem.request_id || !form.newItem.department_id || !form.newItem.quantity) {
    toast.error('Please fill all required fields')
    return
  }

  if (!form.newItem.item_description) {
    toast.error('Please enter an item description')
    return
  }

  form.items.push({ ...form.newItem })
  form.newItem = {
    request_id: '',
    department_id: '',
    quantity: 1,
    unit: '',
    item_description: ''
  }
  
  toast.success('Item added to order')
}

const removeItem = (index: number) => {
  form.items.splice(index, 1)
  toast.info('Item removed from order')
}

const submitForm = () => {
  if (form.items.length === 0) {
    toast.error('Please add at least one item')
    return
  }

  form.post(route('request-to-orders.store'), {
    preserveScroll: true,
    onSuccess: () => {
      toast.success('Order created successfully!')
      form.reset()
      form.items = []
    },
    onError: (errors) => {
      if (errors.items) {
        toast.error('There were errors with your items')
      } else {
        toast.error('Failed to create order. Please try again.')
      }
    }
  })
}
</script>

<template>
  <Head title="Create Request To Order" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6 p-6">
      <div class="space-y-2">
        <h1 class="text-2xl font-bold">Create New Order</h1>
        <p class="text-muted-foreground">Fill out the form below to create a new request to order</p>
      </div>

      <div class="space-y-6">
        <!-- Notes Section -->
        <div class="rounded-lg border p-6 shadow-sm">
          <h2 class="mb-4 text-lg font-semibold">Order Information</h2>
          <div class="space-y-4">
            <Textarea
              id="notes"
              v-model="form.notes"
              placeholder="Additional notes or instructions..."
              class="min-h-[100px]"
            />
          </div>
        </div>

        <!-- Items Section -->
        <div class="rounded-lg border p-6 shadow-sm">
          <h2 class="mb-4 text-lg font-semibold">Order Items</h2>
          
          <!-- Input Row -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">
            <div class="md:col-span-3">
              <Select v-model="form.newItem.request_id">
                <SelectTrigger>
                  <SelectValue placeholder="Select Request *" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="request in requests"
                    :key="request.id"
                    :value="request.id"
                  >
                    {{ request.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="md:col-span-2">
              <Select v-model="form.newItem.department_id">
                <SelectTrigger>
                  <SelectValue placeholder="Department *" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem
                    v-for="dept in departments"
                    :key="dept.id"
                    :value="dept.id"
                  >
                    {{ dept.name }}
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="md:col-span-1">
              <Input
                v-model.number="form.newItem.quantity"
                type="number"
                step="1"
                min="1"
                placeholder="Qty *"
              />
            </div>

            <div class="md:col-span-1">
              <Input
                v-model="form.newItem.unit"
                placeholder="Unit"
              />
            </div>

            <div class="md:col-span-4">
              <Input
                v-model="form.newItem.item_description"
                placeholder="Item Description *"
              />
            </div>

            <div class="md:col-span-1 flex items-center">
              <Button
                type="button"
                @click="addItem"
                class="w-full"
              >
                Add
              </Button>
            </div>
          </div>

          <!-- Items Table -->
          <div class="rounded-md border">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Request</TableHead>
                  <TableHead>Department</TableHead>
                  <TableHead>Qty</TableHead>
                  <TableHead>Unit</TableHead>
                  <TableHead>Description</TableHead>
                  <TableHead class="w-[100px]">Actions</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow v-if="form.items.length === 0">
                  <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                    No items added yet. Add items using the form above.
                  </TableCell>
                </TableRow>
                <TableRow v-for="(item, index) in form.items" :key="index">
                  <TableCell class="font-medium">
                    {{ requests.find(r => r.id === item.request_id)?.name || 'N/A' }}
                  </TableCell>
                  <TableCell>
                    {{ departments.find(d => d.id === item.department_id)?.name || 'N/A' }}
                  </TableCell>
                  <TableCell>{{ item.quantity }}</TableCell>
                  <TableCell>{{ item.unit || '-' }}</TableCell>
                  <TableCell class="max-w-[200px] truncate">{{ item.item_description }}</TableCell>
                  <TableCell>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="removeItem(index)"
                      class="text-red-600 hover:text-red-700"
                    >
                      Remove
                    </Button>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex justify-end gap-4">
          <Button variant="outline" type="button" @click="$inertia.visit(route('request-to-order.index'))">
            Cancel
          </Button>
          <Button
            type="submit"
            @click="submitForm"
            :disabled="form.processing || form.items.length === 0"
          >
            <span v-if="form.processing">Processing...</span>
            <span v-else>Submit Order</span>
          </Button>
        </div>
      </div>
    </div>
  </AppLayout>
</template>