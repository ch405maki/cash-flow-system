<script setup lang="ts">
import { ref } from 'vue'
import { ShoppingCart, Check, ChevronsUpDown } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Combobox, ComboboxAnchor, ComboboxEmpty, ComboboxGroup, ComboboxInput, ComboboxItem, ComboboxItemIndicator, ComboboxList, ComboboxTrigger } from '@/components/ui/combobox'
import { useToast } from 'vue-toastification'

const props = defineProps<{
  units: Array<{ id: number; name: string }>
  submitting: boolean
}>()

const emit = defineEmits<{
  add: [{ quantity: number; unit: string; item_description: string }]
}>()

const toast = useToast()

const quantity = ref(1)
const unit = ref('')
const description = ref('')

function addItem() {
  if (!quantity.value) {
    toast.error('Please fill all required fields')
    return
  }
  if (!description.value) {
    toast.error('Please enter an item description')
    return
  }
  if (!unit.value) {
    toast.error('Please select or type a unit')
    return
  }

  emit('add', {
    quantity: quantity.value,
    unit: unit.value,
    item_description: description.value,
  })

  quantity.value = 1
  unit.value = ''
  description.value = ''
}

function reset() {
  quantity.value = 1
  unit.value = ''
  description.value = ''
}

defineExpose({ reset })
</script>

<template>
  <div class="grid grid-cols-1 gap-4 md:grid-cols-12 mb-6">
    <!-- Quantity -->
    <div class="flex flex-col gap-1.5 md:col-span-2">
      <label class="text-sm font-medium text-muted-foreground">Quantity *</label>
      <Input
        v-model.number="quantity"
        type="number"
        step="1"
        min="1"
        placeholder="Qty"
        class="h-10 w-full"
      />
    </div>

    <!-- Unit -->
    <div class="flex flex-col gap-1.5 md:col-span-3">
      <label class="text-sm font-medium text-muted-foreground">Unit *</label>
      <Combobox v-model="unit">
        <ComboboxAnchor class="w-full">
          <div class="relative flex w-full items-center">
            <ComboboxInput
              placeholder="Select or type a unit..."
              :display-value="(val) => val || ''"
              @update:model-value="(val) => unit = val"
              @change="(e) => unit = (e.target as HTMLInputElement).value"
              class="h-10 w-full"
            />
            <ComboboxTrigger class="absolute end-0 inset-y-0 flex items-center justify-center px-3">
              <ChevronsUpDown class="size-4 text-muted-foreground" />
            </ComboboxTrigger>
          </div>
        </ComboboxAnchor>
        <ComboboxList>
          <ComboboxEmpty>
            <span class="text-sm text-muted-foreground px-2 py-1">No match found — type to add custom unit.</span>
          </ComboboxEmpty>
          <ComboboxGroup>
            <ComboboxItem v-for="u in units" :key="u.id" :value="u.name">
              {{ u.name }}
              <ComboboxItemIndicator>
                <Check class="ml-auto h-4 w-4" />
              </ComboboxItemIndicator>
            </ComboboxItem>
          </ComboboxGroup>
        </ComboboxList>
      </Combobox>
    </div>

    <!-- Description -->
    <div class="flex flex-col gap-1.5 md:col-span-5">
      <label class="text-sm font-medium text-muted-foreground">Item Description *</label>
      <Input
        v-model="description"
        placeholder="Enter item description"
        class="h-10 w-full"
      />
    </div>

    <!-- Add Button -->
    <div class="flex flex-col gap-1.5 md:col-span-2">
      <label class="hidden md:block invisible text-sm font-medium">Spacer</label>
      <Button
        type="button"
        @click="addItem"
        class="h-10 w-full"
        :disabled="submitting || !quantity || !unit || !description"
      >
        <ShoppingCart class="mr-2 h-4 w-4" />
        Add Item
      </Button>
    </div>
  </div>
</template>