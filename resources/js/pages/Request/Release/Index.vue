<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { toast } from '@/components/ui/toast'
import axios from 'axios'
import { ref } from 'vue'
import RequestInfoTable from '@/components/requests/releasing/RequestInfoTable.vue'
import RequestStatusBadge from '@/components/requests/releasing/RequestStatusBadge.vue'
import ItemsTable from '@/components/requests/releasing/ItemsTable.vue'
import ReleaseControls from '@/components/requests/releasing/ReleaseControls.vue'

const props = defineProps({
  request: {
    type: Object,
    required: true,
  },
  departments: {
    type: Array,
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
const releaseItems = async () => { /* ... */ }
const toggleSelectAll = (checked: boolean) => { /* ... */ }

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