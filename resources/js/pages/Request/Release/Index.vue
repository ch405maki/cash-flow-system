<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useToast } from 'vue-toastification'
import axios from 'axios'
import { ref } from 'vue'
import RequestInfoTable from '@/components/requests/releasing/RequestInfoTable.vue'
import RequestStatusBadge from '@/components/requests/releasing/RequestStatusBadge.vue'
import ItemsTable from '@/components/requests/releasing/ItemsTable.vue'
import ReleaseControls from '@/components/requests/releasing/ReleaseControls.vue'

const toast = useToast()

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
  departments: {
    type: Array,
    required: true,
  },
  current_user: {
    type: Object,
    required: true,
  },
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Request', href: '/request' },
  { title: `${props.request.request_no}`, href: '' },
]

const selectedItems = ref<number[]>([])
const isReleasing = ref(false)
const processing = ref(false)

const form = ref({
  details: props.request.details.map(detail => ({
    id: detail.id,
    quantity: detail.quantity,
    released_quantity: detail.released_quantity || 0,
    unit: detail.unit,
    item_description: detail.item_description
  }))
})

// Methods remain the same as in your original file
const submit = async () => { /* ... */ }
const addDetail = () => { /* ... */ }
const removeDetail = (index: number) => { /* ... */ }
const releaseItems = async () => {
  if (selectedItems.value.length === 0) {
    toast.warning('Please select at least one item to release', {
      timeout: 3000
    })
    return
  }

  // Prepare items to release
  const itemsToRelease = form.value.details
    .filter(detail => selectedItems.value.includes(detail.id))
    .map(item => ({
      request_detail_id: item.id,
      quantity: Number(item.released_quantity)
    }));

  if (itemsToRelease.some(item => item.quantity <= 0)) {
    toast.error('Release quantity must be greater than 0');
    return;
  }

  isReleasing.value = true;
  
  try {
    const response = await axios.post(`/api/requests/${props.request.id}/release`, {
      items: itemsToRelease,
      notes: 'Items released from request',
      user_id: props.current_user.id,
    });

    // Update local state with the response data
    form.value.details = form.value.details.map(detail => {
      const updatedDetail = response.data.data.request.details.find(
        (d: any) => d.id === detail.id
      );
      return updatedDetail ? {
        ...detail,
        quantity: updatedDetail.quantity,
        released_quantity: updatedDetail.released_quantity
      } : detail;
    });

    toast.success('Items released successfully');

    // Clear selection after successful release
    selectedItems.value = [];
    
  } catch (error: any) {
    console.error('Release error:', error);
    
    let errorMessage = 'Failed to release items';
    if (error.response?.data?.errors?.quantity) {
      errorMessage = error.response.data.errors.quantity.join(', ');
    } else if (error.response?.data?.message) {
      errorMessage = error.response.data.message;
    }

    toast.error(errorMessage);
  } finally {
    isReleasing.value = false;
  }
};

const toggleSelectAll = ({ checked, shouldAutoFill }: { checked: boolean, shouldAutoFill?: boolean }) => {
  if (checked) {
    // Select all item IDs
    selectedItems.value = form.value.details.map(detail => detail.id)
    
    // Auto-fill all release quantities with remaining quantities
    if (shouldAutoFill) {
      form.value.details.forEach((detail, index) => {
        const remainingQuantity = detail.quantity - detail.released_quantity
        if (remainingQuantity > 0) {
          form.value.details[index].released_quantity = remainingQuantity
        }
      })
    }
  } else {
    // Deselect all
    selectedItems.value = []
  }
}


const updateReleasedQuantity = ({ index, value }: { index: number, value: number }) => {
  form.value.details[index].released_quantity = value
}
</script>

<template>
  <Head title="Edit Request Items" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Request Information</h1>
        <Link :href="route('request.index')">
          <Button variant="outline"> Back to Requests </Button>
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
            :details="form.details"
            :selected-items="selectedItems"
            @update:selectedItems="(items) => selectedItems = items"
            @update:releasedQuantity="updateReleasedQuantity"
            @removeDetail="removeDetail"
          />
          
          <ReleaseControls
            :selected-items="selectedItems"
            :details="form.details"
            :is-releasing="isReleasing"
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