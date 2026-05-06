<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref, reactive, computed } from 'vue'
import RequestInfoTable from '@/components/requests/releasing/RequestInfoTable.vue'
import RequestStatusBadge from '@/components/requests/releasing/RequestStatusBadge.vue'
import ItemsTable from '@/components/requests/releasing/ItemsTable.vue'
import ReleaseControls from '@/components/requests/releasing/ReleaseControls.vue'
import { ArrowLeft } from 'lucide-vue-next';

const toast = useToast()

const props = defineProps({
  request: { type: Object, required: true },
  departments: { type: Array, required: true },
  current_user: { type: Object, required: true },
  inventoryStatus: { type: Object, required: true },
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
]

const selectedItems = ref<number[]>([])
const isReleasing = ref(false)

const form = reactive({
  details: props.request.details.map(detail => ({
    id: detail.id,
    quantity: detail.quantity,
    released_quantity: detail.released_quantity || 0,
    release_now: 0,
    unit: detail.unit,
    item_description: detail.item_description
  }))
})

// Simple validation: returns object with item IDs as keys and boolean (invalid or not) as values
const validationErrors = computed(() => {
  const errors: Record<number, boolean> = {}
  form.details.forEach(d => {
    if (selectedItems.value.includes(d.id)) {
      const remaining = d.quantity - d.released_quantity
      errors[d.id] = d.release_now > remaining || d.release_now <= 0
    } else {
      errors[d.id] = false
    }
  })
  return errors
})

// Check if any selected item has invalid quantity
const hasInvalidQuantities = computed(() => {
  return Object.values(validationErrors.value).some(v => v === true)
})

const updateReleasedQuantity = ({ id, value }: { id: number, value: number }) => {
  const detail = form.details.find(d => d.id === id)
  if (detail) {
    detail.release_now = value
    // validationErrors will auto-update via computed
  }
}

const toggleSelectAll = ({ checked, shouldAutoFill }: { checked: boolean, shouldAutoFill?: boolean }) => {
  if (checked) {
    selectedItems.value = form.details.map(d => d.id)
    if (shouldAutoFill) {
      form.details.forEach(d => {
        const remaining = d.quantity - d.released_quantity
        d.release_now = remaining > 0 ? remaining : 0
      })
    }
  } else {
    selectedItems.value = []
    form.details.forEach(d => { d.release_now = 0 })
  }
}

const releaseItems = async () => {
  if (selectedItems.value.length === 0) {
    toast.warning('Please select at least one item to release', { timeout: 3000 })
    return
  }

  if (hasInvalidQuantities.value) {
    toast.error('Cannot release: some quantities exceed available amount or are invalid')
    return
  }

  const itemsToRelease = form.details
    .filter(d => selectedItems.value.includes(d.id))
    .map(d => ({
      request_detail_id: d.id,
      quantity: Number(d.release_now)
    }))

  if (itemsToRelease.some(item => item.quantity <= 0)) {
    toast.error('Release quantity must be greater than 0')
    return
  }

  isReleasing.value = true
  
  try {
    const response = await axios.post(`/api/requests/${props.request.id}/release`, {
      items: itemsToRelease,
      notes: 'Items released from request',
      user_id: props.current_user.id,
    })

    toast.success('Items released successfully')
    selectedItems.value = []
    setTimeout(() => { window.location.reload() }, 1000)
    
  } catch (error: any) {
    console.error('Release error:', error)
    let errorMessage = 'Failed to release items'
    if (error.response?.data?.errors?.quantity) {
      errorMessage = error.response.data.errors.quantity.join(', ')
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message
    }
    toast.error(errorMessage)
  } finally {
    isReleasing.value = false
  }
}
</script>

<template>
  <Head title="Edit Request Items" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Request Information</h1>
        <Link :href="route('request.index')">
          <Button variant="outline"><ArrowLeft />Back</Button>
        </Link>
      </div>
      
      <RequestInfoTable :request="request">
        <template #status-badge>
          <RequestStatusBadge :status="request.status" />
        </template>
      </RequestInfoTable>

      <div class="pt-4 pb-6">
        <h1 class="text-xl font-bold">Partially Release Requested Items (Editable)</h1>
        <div class="flex justify-between items-center mb-2">
          <h3 class="text-sm font-medium">Items List</h3>
        </div>
        
        <form @submit.prevent="submit" class="space-y-4">
          <ItemsTable
            :details="props.request.details"
            :form-details="form.details"
            :selected-items="selectedItems"
            :inventory-status="inventoryStatus"
            :validation-errors="validationErrors"
            @update:selectedItems="(items) => selectedItems = items"
            @update:releasedQuantity="updateReleasedQuantity"
            @removeDetail="removeDetail"
          />
          
          <ReleaseControls
            :selected-items="selectedItems"
            :details="form.details"
            :is-releasing="isReleasing"
            :has-invalid-quantities="hasInvalidQuantities"
            @toggleSelectAll="toggleSelectAll"
            @releaseItems="releaseItems"
          >
            <template #cancel-button>
              <Button
                type="button"
                variant="outline"
                size="sm"
                as-child
              >
                <Link :href="route('request.index')">Cancel</Link>
              </Button>
            </template>
          </ReleaseControls>
        </form>
      </div>
    </div>
  </AppLayout>
</template>