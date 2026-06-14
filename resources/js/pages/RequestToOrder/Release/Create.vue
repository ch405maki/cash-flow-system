<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { ArrowLeft } from 'lucide-vue-next'
import { formatDate } from '@/lib/utils'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { Skeleton } from '@/components/ui/skeleton'
import { useToast } from 'vue-toastification'
import { requestToOrderService } from '@/services/requestToOrderService'

const toast = useToast()

const props = defineProps<{
  orderId: number
}>()

const loading = ref(true)
const submitting = ref(false)
const order = ref<any>(null)

const form = ref({
  release_date: new Date().toISOString().split('T')[0],
  items: [] as Array<{
    detail_id: number
    quantity: number
    notes: string
    selected: boolean
  }>,
})

onMounted(async () => {
  try {
    const response = await requestToOrderService.releaseData(props.orderId)
    order.value = response.data.order
    order.value.details.forEach((d: any) => {
      d.remaining_quantity = d.quantity - (d.releases_sum_quantity_released ?? 0)
    })
    form.value.items = order.value.details.map((detail: any) => ({
      detail_id: detail.id,
      quantity: detail.remaining_quantity,
      notes: '',
      selected: false,
    }))
  } catch (error) {
    console.error('Failed to load release data:', error)
    toast.error('Failed to load order details')
  } finally {
    loading.value = false
  }
})

function goBack() {
  window.history.back()
}

function validateForm() {
  const hasSelectedItems = form.value.items.some(item => item.selected)
  if (!hasSelectedItems) {
    toast.error('Please select at least one item to release')
    return false
  }

  for (const item of form.value.items) {
    if (item.selected) {
      const detail = order.value?.details.find((d: any) => d.id === item.detail_id)
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

async function submitForm() {
  if (!validateForm() || !order.value) return

  submitting.value = true

  try {
    const payload = {
      release_date: form.value.release_date,
      items: form.value.items.filter(item => item.selected).map(item => ({
        detail_id: item.detail_id,
        quantity: item.quantity,
        notes: item.notes || null,
      })),
    }

    await requestToOrderService.release(props.orderId, payload)
    toast.success('Items released successfully')
    router.visit(`/request-to-order/${props.orderId}`)
  } catch (error: any) {
    toast.error(error.response?.data?.message || 'Failed to release items')
  } finally {
    submitting.value = false
  }
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request To Order', href: '/request-to-order' },
  { title: `Order #${props.orderId}`, href: `/request-to-order/${props.orderId}` },
  { title: 'Release Items', href: '#' },
]
</script>

<template>
  <Head :title="`Release Items - Order #${orderId}`" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <template v-if="loading">
        <Skeleton class="h-8 w-96" />
        <Skeleton class="h-32 w-full" />
        <Skeleton class="h-64 w-full" />
      </template>

      <template v-else-if="order">
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
                      {{ order.details.find((d: any) => d.id === item.detail_id)?.item_description }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      {{ order.details.find((d: any) => d.id === item.detail_id)?.quantity }}
                      {{ order.details.find((d: any) => d.id === item.detail_id)?.unit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      {{ order.details.find((d: any) => d.id === item.detail_id)?.remaining_quantity }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <Input
                        v-model="item.quantity"
                        type="number"
                        :max="order.details.find((d: any) => d.id === item.detail_id)?.remaining_quantity"
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
              <Button type="submit" :disabled="submitting">
                {{ submitting ? 'Processing...' : 'Release Selected Items' }}
              </Button>
            </div>
          </form>
        </div>
      </template>
    </div>
  </AppLayout>
</template>
