<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3'
import { ref, onMounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { useToast } from 'vue-toastification'
import { Button } from '@/components/ui/button'
import { Textarea } from '@/components/ui/textarea'
import { Skeleton } from '@/components/ui/skeleton'
import { requestToOrderService } from '@/services/requestToOrderService'
import axios from 'axios'
import ItemInput from '@/components/requestToOrder/create/ItemInput.vue'
import ItemsTable from '@/components/requestToOrder/create/ItemsTable.vue'

interface OrderItem {
  quantity: number
  unit: string
  item_description: string
  editing?: boolean
  original?: OrderItem
}

const toast = useToast()
const loading = ref(true)
const units = ref<Array<{ id: number; name: string }>>([])
const submitting = ref(false)

const notes = ref('')
const items = ref<OrderItem[]>([])

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/units')
    if (data.success) {
      units.value = data.data
    }
  } catch (error) {
    console.error('Failed to load units:', error)
  } finally {
    loading.value = false
  }
})

function addItem(item: { quantity: number; unit: string; item_description: string }) {
  items.value.push({ ...item })
  toast.success('Item added to order')
}

function editItem(index: number) {
  items.value.forEach((item, i) => {
    if (i !== index && item.editing) {
      cancelEdit(i)
    }
  })
  items.value[index].editing = true
  items.value[index].original = { ...items.value[index] }
}

function saveEdit(index: number) {
  items.value[index].editing = false
  delete items.value[index].original
  toast.success('Item updated')
}

function cancelEdit(index: number) {
  if (items.value[index].original) {
    items.value[index] = { ...items.value[index].original }
  }
  items.value[index].editing = false
  delete items.value[index].original
}

function handleKeydown(event: KeyboardEvent, index: number) {
  if (event.key === 'Enter') saveEdit(index)
  else if (event.key === 'Escape') cancelEdit(index)
}

function removeItem(index: number) {
  items.value.splice(index, 1)
  toast.info('Item removed from order')
}

async function submitForm() {
  if (items.value.length === 0) {
    toast.error('Please add at least one item')
    return
  }

  items.value.forEach((item, index) => {
    if (item.editing) cancelEdit(index)
  })

  submitting.value = true

  try {
    await requestToOrderService.storeManual({
      notes: notes.value,
      items: items.value.map(item => ({
        quantity: item.quantity,
        unit: item.unit,
        item_description: item.item_description,
      })),
    })

    toast.success('Order created successfully!')
    notes.value = ''
    items.value = []
  } catch (error: any) {
    toast.error(error.response?.data?.errors?.items ? 'There were errors with your items' : 'Failed to create order. Please try again.')
  } finally {
    submitting.value = false
  }
}

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Purchase Request', href: '/request-to-order' },
  { title: 'Create Purchase Request', href: '/' },
]
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
          <div>
            <h2 class="mb-4 text-lg font-semibold">Purchase Information</h2>
            <Textarea v-model="notes" placeholder="Additional notes or instructions..." class="min-h-[100px]" />
          </div>

          <div>
            <h2 class="mb-4 text-lg font-semibold">Purchase Items</h2>
            <ItemInput :units="units" :submitting="submitting" @add="addItem" />

            <ItemsTable
              :items="items"
              :units="units"
              @edit="editItem"
              @save="saveEdit"
              @cancel="cancelEdit"
              @remove="removeItem"
              @keydown="handleKeydown"
            />
          </div>

          <div class="flex justify-end gap-4">
            <Button variant="outline" type="button" @click="router.visit('/request-to-order')">Cancel</Button>
            <Button type="submit" @click="submitForm" :disabled="submitting || items.length === 0">
              <span v-if="submitting">Processing...</span>
              <span v-else>Submit Request</span>
            </Button>
          </div>
        </div>
      </div>
    </template>
  </AppLayout>
</template>
