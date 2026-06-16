<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, router } from '@inertiajs/vue3'
import axios from 'axios'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue, SelectGroup } from '@/components/ui/select'
import { Textarea } from '@/components/ui/textarea'
import { Check, ChevronsUpDown } from 'lucide-vue-next'
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'
import { useToast } from 'vue-toastification'
import { purchaseOrderService } from '@/services/purchaseOrderService'
import { ref, onMounted, computed } from 'vue'
import { Skeleton } from '@/components/ui/skeleton'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'

interface PurchaseOrderDetail {
  quantity: number
  unit: string
  item_description: string
  unit_price: number
  amount: number
  editing?: boolean
  original?: PurchaseOrderDetail
}

const props = defineProps<{ purchaseOrderId: number }>()

const toast = useToast()
const loading = ref(true)
const departments = ref<Array<{ id: number; department_name: string }>>([])
const purchaseOrderNo = ref('')
const units = ref<Array<{id: number, name: string}>>([])
const isLoadingUnits = ref(false)

const breadcrumbs = computed<BreadcrumbItem[]>(() => [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Orders', href: '/purchase-order' },
  { title: `Edit ${purchaseOrderNo.value || ''}`, href: '' },
])

const form = ref({
  payee: '',
  check_payable_to: '',
  date: new Date().toISOString().split('T')[0],
  purpose: '',
  tin_no: '',
  department_id: '',
  details: [] as PurchaseOrderDetail[],
})

onMounted(async () => {
  try {
    const response = await purchaseOrderService.editData(props.purchaseOrderId)
    const po = response.data.purchaseOrder

    departments.value = response.data.departments
    purchaseOrderNo.value = po.po_no

    form.value.payee = po.payee || ''
    form.value.check_payable_to = po.check_payable_to || ''
    form.value.date = po.date || form.value.date
    form.value.purpose = po.purpose || ''
    form.value.tin_no = po.tin_no || ''
    form.value.department_id = po.department_id ? String(po.department_id) : ''
    form.value.details = (po.details || []).map((d: any) => ({
      quantity: Number(d.quantity) || 0,
      unit: d.unit || 'pc',
      item_description: d.item_description || '',
      unit_price: Number(d.unit_price) || 0,
      amount: Number(d.amount) || 0,
      editing: false,
    }))
  } catch (error) {
    console.error('Failed to load purchase order for edit:', error)
    toast.error('Failed to load purchase order')
  }

  try {
    const unitResponse = await axios.get('/api/units')
    if (unitResponse.data.success) {
      units.value = unitResponse.data.data
    }
  } catch (error) {
    console.error('Error loading units:', error)
  } finally {
    loading.value = false
  }
})

const newItem = ref<PurchaseOrderDetail>({
  quantity: 1,
  unit: 'pc',
  item_description: '',
  unit_price: 0,
  amount: 0,
})

const editItem = (index: number) => {
  form.value.details.forEach((item, i) => {
    if (i !== index && item.editing) cancelEdit(i)
  })
  form.value.details[index].editing = true
  form.value.details[index].original = { ...form.value.details[index] }
}

const saveEdit = (index: number) => {
  const item = form.value.details[index]
  item.amount = item.quantity === 0 ? item.unit_price : item.quantity * item.unit_price
  item.editing = false
  delete item.original
}

const cancelEdit = (index: number) => {
  if (form.value.details[index].original) {
    form.value.details[index] = { ...form.value.details[index].original }
  }
  form.value.details[index].editing = false
  delete form.value.details[index].original
}

const handleKeyDown = (event: KeyboardEvent, index: number) => {
  if (event.key === 'Enter') saveEdit(index)
  else if (event.key === 'Escape') cancelEdit(index)
}

const addItem = () => {
  if (!newItem.value.item_description) {
    toast.error('Item description is required')
    return
  }

  newItem.value.amount = newItem.value.quantity === 0
    ? newItem.value.unit_price
    : newItem.value.quantity * newItem.value.unit_price

  form.value.details.push({ ...newItem.value })
  newItem.value = {
    quantity: 1,
    unit: 'pc',
    item_description: '',
    unit_price: 0,
    amount: 0,
  }
}

const removeItem = (index: number) => {
  form.value.details.splice(index, 1)
}

const calculateTotal = () => form.value.details.reduce((sum, item) => sum + item.amount, 0)

const submitForm = async () => {
  try {
    await purchaseOrderService.update(props.purchaseOrderId, {
      payee: form.value.payee,
      check_payable_to: form.value.check_payable_to,
      date: form.value.date,
      purpose: form.value.purpose,
      tin_no: form.value.tin_no,
      department_id: form.value.department_id,
      details: form.value.details.map((item) => ({
        quantity: item.quantity,
        unit: item.unit,
        item_description: item.item_description,
        unit_price: item.unit_price,
        amount: item.amount,
      })),
    })

    toast.success('Purchase Order updated successfully')
    router.visit(`/purchase-order/${props.purchaseOrderId}`)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to update purchase order')
  }
}
</script>

<template>
  <Head title="Edit Purchase Order" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div v-if="loading" class="p-6 space-y-4">
      <Skeleton class="h-8 w-64" />
      <Skeleton class="h-12 w-full" />
      <Skeleton class="h-64 w-full" />
    </div>

    <div v-else class="p-6 space-y-6">
      <form @submit.prevent="submitForm" class="space-y-2">
        <div class="flex">
          <h1 class="text-2xl font-bold">Edit Purchase Order {{ purchaseOrderNo }}</h1>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 flex items-center">
          <div class="space-y-2 md:col-span-2">
            <Label for="payee">Company Name</Label>
            <Input id="payee" v-model="form.payee" placeholder="e.g. Acme Corporation" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="space-y-2 md:col-span-2">
            <Label for="check_payable_to">Check Payable To</Label>
            <Input id="check_payable_to" v-model="form.check_payable_to" placeholder="e.g. Acme Corporation" required />
          </div>

          <div class="space-y-2 md:col-span-1">
            <Label for="date">P. O. Date</Label>
            <Input id="date" type="date" v-model="form.date" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="space-y-2">
            <Label for="department_id">Department</Label>
            <Select v-model="form.department_id" required>
              <SelectTrigger>
                <SelectValue placeholder="Select department" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="dept in departments" :key="dept.id" :value="dept.id.toString()">
                  {{ dept.department_name }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2 md:col-span-1">
            <Label for="tin_no">Company TIN</Label>
            <Input id="tin_no" type="text" v-model="form.tin_no" placeholder="e.g. 123-456-789-000" required />
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
          <div class="space-y-2">
            <Label for="purpose">Purpose</Label>
            <Textarea id="purpose" v-model="form.purpose" placeholder="Describe the purpose of this purchase order..." required />
          </div>
        </div>

        <div class="space-y-2">
          <h2 class="text-xl font-semibold mt-4">Items</h2>
          <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
              <div class="space-y-2 md:col-span-6">
                <Label for="item_description">Description</Label>
                <Input id="item_description" v-model="newItem.item_description" placeholder="Enter item description" />
              </div>

              <div class="space-y-2 md:col-span-1">
                <Label for="quantity">Quantity</Label>
                <Input id="quantity" type="number" v-model.number="newItem.quantity" min="0" placeholder="0" />
              </div>

              <div class="md:col-span-2 space-y-2">
                <Label for="unit">Unit</Label>
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
                        <span v-if="isLoadingUnits">Loading units...</span>
                        <span v-else>No match found — type to add custom unit.</span>
                      </span>
                    </ComboboxEmpty>
                    <ComboboxGroup>
                      <ComboboxItem
                        v-for="unit in units"
                        :key="unit.id"
                        :value="unit.name"
                      >
                        {{ unit.name }}
                        <ComboboxItemIndicator>
                          <Check class="ml-auto h-4 w-4" />
                        </ComboboxItemIndicator>
                      </ComboboxItem>
                    </ComboboxGroup>
                  </ComboboxList>
                </Combobox>
              </div>

              <div class="space-y-2 md:col-span-2">
                <Label for="unit_price">Unit Price</Label>
                <Input id="unit_price" type="number" step="0.01" v-model.number="newItem.unit_price" min="0" placeholder="0.00" />
              </div>

              <Button type="button" @click="addItem" class="w-full md:col-span-1">
                Add Item
              </Button>
            </div>

            <div class="overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead class="w-[200px]">Description</TableHead>
                    <TableHead>Quantity</TableHead>
                    <TableHead>Unit</TableHead>
                    <TableHead>Unit Price</TableHead>
                    <TableHead>Amount</TableHead>
                    <TableHead class="text-right" v-if="form.details.some(item => item.editing)">Actions</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow
                    v-for="(item, index) in form.details"
                    :key="index"
                    @click="editItem(index)"
                    :class="{ 'hover:bg-muted/50 cursor-pointer': true, 'bg-muted': item.editing }"
                  >
                    <TableCell>
                      <div v-if="!item.editing">{{ item.item_description }}</div>
                      <Input
                        v-else
                        v-model="item.item_description"
                        placeholder="Enter item description"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        class="w-full"
                      />
                    </TableCell>

                    <TableCell>
                      <div v-if="!item.editing">{{ item.quantity }}</div>
                      <Input
                        v-else
                        type="number"
                        v-model.number="item.quantity"
                        min="0"
                        placeholder="0"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        @change="item.amount = item.quantity === 0 ? item.unit_price : item.quantity * item.unit_price"
                        class="w-full"
                      />
                    </TableCell>

                    <TableCell>
                      <div v-if="!item.editing">{{ item.unit }}</div>
                      <Combobox v-else v-model="item.unit" @click.stop>
                        <ComboboxAnchor>
                          <ComboboxInput
                            placeholder="Select or type a unit..."
                            :display-value="(val) => val || ''"
                            @update:model-value="(val) => item.unit = val"
                            @change="(e) => item.unit = e.target.value"
                            class="w-full"
                          />
                        </ComboboxAnchor>
                        <ComboboxList>
                          <ComboboxEmpty class="text-sm text-muted-foreground px-2 py-1">
                            <span v-if="isLoadingUnits">Loading units...</span>
                            <span v-else>No match found — type to add custom unit.</span>
                          </ComboboxEmpty>
                          <ComboboxGroup>
                            <ComboboxItem
                              v-for="unit in units"
                              :key="unit.id"
                              :value="unit.name"
                            >
                              {{ unit.name }}
                              <ComboboxItemIndicator>
                                <Check class="ml-auto h-4 w-4" />
                              </ComboboxItemIndicator>
                            </ComboboxItem>
                          </ComboboxGroup>
                        </ComboboxList>
                      </Combobox>
                    </TableCell>

                    <TableCell>
                      <div v-if="!item.editing">{{ item.unit_price.toFixed(2) }}</div>
                      <Input
                        v-else
                        type="number"
                        step="0.01"
                        v-model.number="item.unit_price"
                        min="0"
                        placeholder="0.00"
                        @click.stop
                        @keydown="handleKeyDown($event, index)"
                        @change="item.amount = item.quantity === 0 ? item.unit_price : item.quantity * item.unit_price"
                        class="w-full"
                      />
                    </TableCell>

                    <TableCell>{{ item.amount.toFixed(2) }}</TableCell>

                    <TableCell class="text-right" v-if="item.editing">
                      <div class="flex justify-end gap-2">
                        <Button variant="outline" size="sm" @click.stop="saveEdit(index)">Save</Button>
                        <Button variant="outline" size="sm" @click.stop="cancelEdit(index)">Cancel</Button>
                        <Button variant="destructive" size="sm" @click.stop="removeItem(index)">Remove</Button>
                      </div>
                    </TableCell>
                  </TableRow>

                  <TableRow v-if="form.details.length === 0">
                    <TableCell :colspan="form.details.some(item => item.editing) ? 6 : 5" class="h-24 text-center">
                      No items added yet
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>

            <div class="flex justify-end">
              <div class="text-lg font-semibold">Total Amount: {{ calculateTotal().toFixed(2) }}</div>
            </div>
          </div>
        </div>

        <div class="flex justify-end space-x-4">
          <Button type="button" variant="outline" @click="router.visit(`/purchase-order/${props.purchaseOrderId}`)">Cancel</Button>
          <Button type="submit">Update Purchase Order</Button>
        </div>
      </form>
    </div>
  </AppLayout>
</template>
