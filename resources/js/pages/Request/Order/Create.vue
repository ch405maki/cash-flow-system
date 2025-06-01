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
  quantity: number
  unit: string
  item_description: string
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: 'Create Request To Order', href: '/' },
]

const form = useForm({
  notes: '',
  newItem: {
    quantity: 1,
    unit: '',
    item_description: ''
  },
  items: [] as OrderItem[]
})

const addItem = () => {
  if (!form.newItem.quantity) {
    toast.error('Please fill all required fields')
    return
  }

  if (!form.newItem.item_description) {
    toast.error('Please enter an item description')
    return
  }

  form.items.push({ ...form.newItem })
  form.newItem = {
    quantity: 1,
    unit: '',
    item_description: ''
  }
  
  toast.success('Item added to order')
}

const unitOptions = ['pc', 'box', 'kg', 'liter', 'pack'];

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
        <div class="">
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
        <div class="">
          <h2 class="mb-4 text-lg font-semibold">Order Items</h2>
          
          <!-- Input Row -->
          <div class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6">

            <div class="md:col-span-1">
              <Input
                v-model.number="form.newItem.quantity"
                type="number"
                step="1"
                min="1"
                placeholder="Qty *"
              />
            </div>

            <div class="md:col-span-2">
              <Select v-model="form.newItem.unit">
                <SelectTrigger class="w-full">
                  <SelectValue placeholder="Select a unit" />
                </SelectTrigger>
                <SelectContent>
                  <SelectGroup>
                    <SelectItem value="pc">pc</SelectItem>
                    <SelectItem value="box">box</SelectItem>
                    <SelectItem value="kg">kg</SelectItem>
                    <SelectItem value="liter">liter</SelectItem>
                    <SelectItem value="pack">pack</SelectItem>
                  </SelectGroup>
                </SelectContent>
              </Select>
            </div>

            <div class="md:col-span-8">
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
          <Button variant="outline" type="button" @click="$inertia.visit(route('request-to-order.index'))">
            Save Draft
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