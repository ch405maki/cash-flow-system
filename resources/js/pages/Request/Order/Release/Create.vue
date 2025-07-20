<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { ArrowLeft } from 'lucide-vue-next'
import { formatDate } from '@/lib/utils'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { useToast } from 'vue-toastification'

const toast = useToast()
const { order } = defineProps<{
  order: {
    id: number
    order_no: string
    details: Array<{
      id: number
      item_description: string
      quantity: number
      unit: string
      remaining_quantity: number
      releases_sum_quantity_released?: number
    }>
  }
}>()

// Add debug logging
onMounted(() => {
  console.log('Order details:', order.details.map(d => ({
    id: d.id,
    ordered: d.quantity,
    released: d.releases_sum_quantity_released,
    remaining: d.remaining_quantity
  })))
})

const form = useForm({
  release_date: new Date().toISOString().split('T')[0],
  items: order.details.map(detail => ({
    detail_id: detail.id,
    quantity: detail.remaining_quantity,
    notes: '',
    selected: false // Add selected flag
  })),
})

function goBack() {
  window.history.back()
}

function validateForm() {
  // Check if at least one item is selected
  const hasSelectedItems = form.items.some(item => item.selected)
  if (!hasSelectedItems) {
    toast.error('Please select at least one item to release')
    return false
  }

  // Validate quantities for selected items
  for (const item of form.items) {
    if (item.selected) {
      const detail = order.details.find(d => d.id === item.detail_id)
      if (item.quantity <= 0) {
        toast.error(`Quantity must be greater than 0 for ${detail?.item_description}`)
        return false
      }
      if (item.quantity > (detail?.remaining_quantity || 0)) {
        toast.error(`Cannot release more than remaining quantity for ${detail?.item_description}`)
        return false
      }
    }
  }
  
  return true
}

function submitForm() {
  if (!validateForm()) return

  // Filter to only include selected items
  const payload = {
    release_date: form.release_date,
    items: form.items.filter(item => item.selected)
  }

  form.transform(() => payload).post(route('request-to-order.release.store', order.id), {
    onSuccess: () => {
      toast.success('Items released successfully')
    },
    onError: (errors) => {
      toast.error('Failed to release items')
    }
  })
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: `Order ${order.order_no}`, href: `/request-to-order/${order.id}` },
  { title: 'Release Items', href: '#' }
]
</script>

<template>
  <Head :title="`Release Items - ${order.order_no}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold">Release Items for Order {{ order.order_no }}</h1>
        <div class="space-x-2">
          <Button @click="goBack" variant="outline">
            <ArrowLeft class="w-4 h-4 mr-2" /> Back
          </Button>
        </div>
      </div>

      <div class="bg-white rounded-md shadow p-6">
        <form @submit.prevent="submitForm">
          <div class="grid grid-cols-1 gap-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <Label for="release_date">Release Date</Label>
                <Input
                  id="release_date"
                  v-model="form.release_date"
                  type="date"
                  class="w-full mt-1"
                />
                <p v-if="form.errors.release_date" class="text-sm text-red-500 mt-1">
                  {{ form.errors.release_date }}
                </p>
              </div>
            </div>
          </div>

          <h2 class="text-lg font-medium mb-4">Items to Release</h2>
          
          <div class="border rounded-md overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <Checkbox 
                      :checked="form.items.every(item => item.selected)"
                      @update:checked="val => form.items.forEach(item => item.selected = val)"
                    />
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Item</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ordered Qty</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remaining</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Release Qty</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(item, index) in form.items" :key="item.detail_id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Checkbox v-model:checked="item.selected" />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ order.details.find(d => d.id === item.detail_id)?.item_description }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ order.details.find(d => d.id === item.detail_id)?.quantity }}
                    {{ order.details.find(d => d.id === item.detail_id)?.unit }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    {{ order.details.find(d => d.id === item.detail_id)?.remaining_quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Input
                      v-model="item.quantity"
                      type="number"
                      :max="order.details.find(d => d.id === item.detail_id)?.remaining_quantity"
                      min="1"
                      class="w-24"
                      :disabled="!item.selected"
                    />
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <Input
                      v-model="item.notes"
                      type="text"
                      placeholder="Optional notes"
                      class="w-full"
                      :disabled="!item.selected"
                    />
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="flex justify-end mt-6">
            <Button type="submit" :disabled="form.processing">
              {{ form.processing ? 'Processing...' : 'Release Selected Items' }}
            </Button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>