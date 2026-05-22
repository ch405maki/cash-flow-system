<!-- resources/js/pages/Request/Release/Index.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { type BreadcrumbItem } from '@/types'
import { Head, Link } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { useToast } from 'vue-toastification'
import { requestService } from '@/services/requestService'
import { ref, reactive, computed, watch, onMounted } from 'vue'

import RequestInfoTable from '@/components/requests/releasing/RequestInfoTable.vue'
import RequestStatusBadge from '@/components/requests/releasing/RequestStatusBadge.vue'
import ItemsTable from '@/components/requests/releasing/ItemsTable.vue'
import ReleaseControls from '@/components/requests/releasing/ReleaseControls.vue'
import SignatureDialog from '@/components/signature/SignatureDialog.vue'

import { ArrowLeft } from 'lucide-vue-next'
import type { SignatureResult } from '@/composables/useSignaturePad'

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
  // { title: `${props.request.request_no}`, href: '' },
]

// ── STATE ─────────────────────────────────────────────
const selectedItems = ref<number[]>([])
const isReleasing = ref(false)
const signatureDialogOpen = ref(false)

// ── FORM ──────────────────────────────────────────────
const form = reactive({
  details: props.request.details.map((detail: any) => ({
    id: detail.id,
    quantity: detail.quantity,
    released_quantity: detail.released_quantity || 0,
    release_now: 0,
    unit: detail.unit,
    item_description: detail.item_description,
  })),
})

// ── VALIDATION (explicit reactive) ──────────────────
const validationErrors = reactive<Record<number, boolean>>({})

const updateValidationForItem = (detail: any) => {
  if (selectedItems.value.includes(detail.id)) {
    const remaining = (Number(detail.quantity) || 0) - (Number(detail.released_quantity) || 0)
    validationErrors[detail.id] = detail.release_now > remaining || detail.release_now <= 0
  } else {
    validationErrors[detail.id] = false
  }
}

// Update validation when selection changes
watch(selectedItems, () => {
  form.details.forEach((d: any) => updateValidationForItem(d))
}, { deep: true })

const hasInvalidQuantities = computed(() =>
  Object.values(validationErrors).some(v => v === true)
)

// ── UPDATE QUANTITY ───────────────────────────────────
const updateReleasedQuantity = ({ id, value }: { id: number; value: number }) => {
  const detail = form.details.find((d: any) => d.id === id)
  if (detail) {
    detail.release_now = value
    updateValidationForItem(detail)
  }
}

// ── SELECT ALL ────────────────────────────────────────
const toggleSelectAll = ({ checked, shouldAutoFill }: { checked: boolean; shouldAutoFill?: boolean }) => {
  if (checked) {
    selectedItems.value = form.details.map((d: any) => d.id)

    if (shouldAutoFill) {
      form.details.forEach((d: any) => {
        const remaining = d.quantity - d.released_quantity
        d.release_now = remaining > 0 ? remaining : 0
        updateValidationForItem(d)
      })
    } else {
      form.details.forEach((d: any) => updateValidationForItem(d))
    }
  } else {
    selectedItems.value = []
    form.details.forEach((d: any) => {
      d.release_now = 0
      updateValidationForItem(d)
    })
  }
}

// ── LOAD SIGNOTEC LIBRARY ─────────────────────────────
onMounted(() => {
  // Check if library is already loaded
  if (!window.STPadServerLib) {
    console.log('Loading signotec library...')
    const script = document.createElement('script')
    script.src = '/js/STPadServerLib-3.5.0.js'
    script.onload = () => {
      console.log('signotec library loaded successfully')
      console.log('STPadServerLib available:', !!window.STPadServerLib)
      console.log('API methods:', window.STPadServerLib?.STPadServerLibApi ? Object.keys(window.STPadServerLib.STPadServerLibApi) : 'No API found')
      
      if (!window.STPadServerLib) {
        toast.error('Signature pad library loaded but not initialized correctly')
      }
    }
    script.onerror = (error) => {
      console.error('Failed to load signotec library:', error)
      toast.error('Failed to load signature pad library. Please refresh the page.')
    }
    document.head.appendChild(script)
  } else {
    console.log('signotec library already loaded')
  }
})

// ── STEP 1: VALIDATE → OPEN SIGNATURE ────────────────
const releaseItems = () => {
  if (selectedItems.value.length === 0) {
    toast.warning('Please select at least one item', { timeout: 3000 })
    return
  }

  if (hasInvalidQuantities.value) {
    toast.error('Invalid quantities detected')
    return
  }

  const itemsToRelease = form.details
    .filter(d => selectedItems.value.includes(d.id))
    .map(d => ({
      request_detail_id: d.id,
      quantity: Number(d.release_now),
    }))

  if (itemsToRelease.some(i => i.quantity <= 0)) {
    toast.error('Quantity must be greater than 0')
    return
  }

  // Open signature dialog
  signatureDialogOpen.value = true
}

// ── STEP 2: AFTER SIGNATURE → RELEASE ────────────────
const handleSignatureConfirmed = async (signature: SignatureResult, signerName: string) => {
  if (!signature?.imageData) {
    toast.error('Signature is required')
    return
  }

  const itemsToRelease = form.details
    .filter(d => selectedItems.value.includes(d.id))
    .map(d => ({
      request_detail_id: d.id,
      quantity: Number(d.release_now),
    }))

  isReleasing.value = true

  try {
    await requestService.release(props.request.id, {
      items: itemsToRelease,
      notes: 'Items released from request',
      user_id: props.current_user.id,

      signature: {
        image: signature.imageData,
        signer_id: props.current_user.id,
        signer_name: signerName,
        signed_at: new Date().toISOString(),
      },
    })

    toast.success('Items released successfully')

    selectedItems.value = []
    signatureDialogOpen.value = false

    setTimeout(() => window.location.reload(), 1000)

  } catch (error: any) {
    console.error(error)
    toast.error(error.response?.data?.message || 'Release failed')
  } finally {
    isReleasing.value = false
  }
}

// ── HANDLE SIGNATURE CANCELLED ───────────────────────
const handleSignatureCancelled = () => {
  signatureDialogOpen.value = false
  }
</script>

<template>
  <Head title="Release Request Items" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 space-y-4">

      <!-- HEADER -->
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Request Information</h1>

        <Link :href="route('request.index')">
          <Button variant="outline">
            <ArrowLeft class="w-4 h-4 mr-2" />
            Back
          </Button>
        </Link>
      </div>

      <!-- REQUEST INFO -->
      <RequestInfoTable :request="request">
        <template #status-badge>
          <RequestStatusBadge :status="request.status" />
        </template>
      </RequestInfoTable>

      <!-- ITEMS -->
      <div class="pt-4 pb-6">
        <h1 class="text-xl font-bold">Release Items</h1>

        <form @submit.prevent class="space-y-4">

          <ItemsTable
            :details="props.request.details"
            :form-details="form.details"
            :selected-items="selectedItems"
            :inventory-status="inventoryStatus"
            :validation-errors="validationErrors"
            @update:selectedItems="(items) => selectedItems = items"
            @update:releasedQuantity="updateReleasedQuantity"
          />

          <ReleaseControls
            :selected-items="selectedItems"
            :details="form.details"
            :is-releasing="isReleasing"
            :has-invalid-quantities="hasInvalidQuantities"
            :is-signing="false"
            @toggleSelectAll="toggleSelectAll"
            @releaseItems="releaseItems"
          >
            <template #cancel-button>
              <Button type="button" variant="outline" size="sm" as-child>
                <Link :href="route('request.index')">Cancel</Link>
              </Button>
            </template>
          </ReleaseControls>

        </form>
      </div>
    </div>
  </AppLayout>

  <!-- SIGNATURE DIALOG -->
  <SignatureDialog
    v-model:open="signatureDialogOpen"
    :request-no="request.request_no"
    :signer-name="request.user?.first_name + ' ' + request.user?.last_name || 'Unknown User'"
    @confirmed="handleSignatureConfirmed"
    @cancelled="handleSignatureCancelled"
  />
</template>