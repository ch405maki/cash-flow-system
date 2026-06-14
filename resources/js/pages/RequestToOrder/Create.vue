<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted } from "vue"
import AppLayout from '@/layouts/AppLayout.vue'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Textarea } from '@/components/ui/textarea'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from '@/components/ui/select'
import { ShoppingCart } from 'lucide-vue-next';
import { Skeleton } from '@/components/ui/skeleton'
import { requestToOrderService } from '@/services/requestToOrderService';
import axios from 'axios';

const toast = useToast()

interface OrderItem {
  quantity: number
  unit: string
  item_description: string
  editing?: boolean
  original?: OrderItem
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Request', href: '/request-to-order' },
  { title: 'Create Purchase Request', href: '/' },
]

const loading = ref(true)
const requests = ref<any[]>([])
const units = ref<Array<{id: number, name: string}>>([])
const submitting = ref(false)

const form = ref({
  notes: '',
  newItem: {
    quantity: 1,
    unit: '',
    item_description: ''
  },
  items: [] as OrderItem[]
})

onMounted(async () => {
  try {
    const [dataResponse, unitsResponse] = await Promise.all([
      requestToOrderService.createData(),
      axios.get('/api/units'),
    ])
    requests.value = dataResponse.data.requests || []
    if (unitsResponse.data.success) {
      units.value = unitsResponse.data.data
    }
  } catch (error) {
    console.error('Failed to load create data:', error)
  } finally {
    loading.value = false
  }
})

const editItem = (index: number) => {
  form.value.items.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i)
    }
  })
  form.value.items[index].editing = true
  form.value.items[index].original = { ...form.value.items[index] }
}

const saveEdit = (index: number) => {
  form.value.items[index].editing = false
  delete form.value.items[index].original
  toast.success('Item updated')
}

const cancelEdit = (index: number) => {
  if (form.value.items[index].original) {
    form.value.items[index] = { ...form.value.items[index].original }
  }
  form.value.items[index].editing = false
  delete form.value.items[index].original
}

const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Enter') {
    saveEdit(index)
  } else if (event.key === 'Escape') {
    cancelEdit(index)
  }
}

const addItem = () => {
  if (!form.value.newItem.quantity) {
    toast.error('Please fill all required fields')
    return
  }

  if (!form.value.newItem.item_description) {
    toast.error('Please enter an item description')
    return
  }

  form.value.items.push({ ...form.value.newItem })
  form.value.newItem = {
    quantity: 1,
    unit: '',
    item_description: ''
  }
  toast.success('Item added to order')
}

const removeItem = (index: number) => {
  form.value.items.splice(index, 1)
  toast.info('Item removed from order')
}

const submitForm = async () => {
  if (form.value.items.length === 0) {
    toast.error('Please add at least one item')
    return
  }

  form.value.items.forEach((item, index) => {
    if (item.editing) {
      cancelEdit(index)
    }
  })

  submitting.value = true

  try {
    const payload = {
      notes: form.value.notes,
      items: form.value.items.map(item => ({
        quantity: item.quantity,
        unit: item.unit,
        item_description: item.item_description,
      })),
    }

    await requestToOrderService.storeManual(payload)

    toast.success('Order created successfully!')
    form.value = {
      notes: '',
      newItem: { quantity: 1, unit: '', item_description: '' },
      items: [],
    }
  } catch (error: any) {
    if (error.response?.data?.errors?.items) {
      toast.error('There were errors with your items')
    } else {
      toast.error('Failed to create order. Please try again.')
    }
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <Head title="Create Purchase Request" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-4 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-64 w-full" />
    </div>

    <template v-else>
      <div class="space-y-6 p-6">
        <div class="space-y-2">
          <h1 class="text-2xl font-bold">Create New Purchase Request</h1>
          <p class="text-muted-foreground">Fill out the form below to create a new purchase request</p>
        </div>

        <div class="space-y-6">
          <div class="">
            <h2 class="mb-4 text-lg font-semibold">Purchase Information</h2>
            <div class="space-y-4">
              <Textarea
                id="notes"
                v-model="form.notes"
                placeholder="Additional notes or instructions..."
                class="min-h-[100px]"
              />
            </div>
          </div>

          <div class="">
            <h2 class="mb-4 text-lg font-semibold">Purchase Items</h2>

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
                      <SelectItem
                        v-for="unit in units"
                        :key="unit.id"
                        :value="unit.name"
                      >
                        {{ unit.name }}
                      </SelectItem>
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
                  :disabled="submitting || !form.newItem.quantity || !form.newItem.unit || !form.newItem.item_description"
                >
                  <ShoppingCart/>
                  Add
                </Button>
              </div>
            </div>

            <div>
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Qty</TableHead>
                    <TableHead>Unit</TableHead>
                    <TableHead>Description</TableHead>
                    <TableHead v-if="form.items.some(item => item.editing)" class="w-[180px]">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-if="form.items.length === 0">
                    <TableCell :colspan="form.items.some(item => item.editing) ? 4 : 3" class="text-center py-8 text-muted-foreground">
                      No items added yet. Add items using the form above.
                    </TableCell>
                  </TableRow>
                  <TableRow 
                    v-for="(item, index) in form.items" 
                    :key="index"
                    @click="editItem(index)"
                    :class="{
                      'hover:bg-gray-50 cursor-pointer': true,
                      'bg-blue-50': item.editing
                    }"
                  >
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
                      <div v-if="!item.editing">{{ item.unit || '-' }}</div>
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
                              v-for="unit in units"
                              :key="unit.id"
                              :value="unit.name"
                            >
                              {{ unit.name }}
                            </SelectItem>
                          </SelectGroup>
                        </SelectContent>
                      </Select>
                    </TableCell>

                    <TableCell class="max-w-[200px]">
                      <div v-if="!item.editing" class="truncate">{{ item.item_description }}</div>
                      <Input 
                        v-else
                        v-model="item.item_description"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        class="w-full"
                      />
                    </TableCell>

                    <TableCell v-if="form.items.some(i => i.editing)">
                      <div class="flex space-x-2">
                        <template v-if="item.editing">
                          <Button variant="outline" size="sm" @click.stop="saveEdit(index)">Save</Button>
                          <Button variant="outline" size="sm" @click.stop="cancelEdit(index)">Cancel</Button>
                          <Button variant="destructive" size="sm" @click.stop="removeItem(index)">Remove</Button>
                        </template>
                      </div>
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table> 
            </div>
          </div>

          <div class="flex justify-end gap-4">
            <Button variant="outline" type="button" @click="router.visit('/request-to-order')">
              Cancel
            </Button>
            <Button
              type="submit"
              @click="submitForm"
              :disabled="submitting || form.items.length === 0"
            >
              <span v-if="submitting">Processing...</span>
              <span v-else>Submit Request</span>
            </Button>
          </div>
        </div>
      </div>
    </template>
  </AppLayout>
</template>
