<script setup lang="ts">
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Check, RefreshCcw, TriangleAlert } from 'lucide-vue-next';
import { formatCurrency } from '@/lib/utils';

const props = defineProps<{
  modelValue: string;
  error: string;
  checking: boolean;
  purchaseOrder?: {
    id: number;
    po_no: string;
    tin_no: string;
    payee?: string;
    check_payable_to?: string;
    amount?: number;
  };
}>();

const emit = defineEmits<{
  'update:modelValue': [value: string];
  blur: [value: string];
  suggest: [];
}>();
</script>

<template>
  <div class="flex items-center justify-end gap-2">
    <div class="relative">
      <div class="absolute left-3 top-1/2 -translate-y-1/2 text-sm text-muted-foreground pointer-events-none">
        #
      </div>

      <Input
        :model-value="modelValue"
        class="w-48 text-right font-bold text-lg h-9 pl-6 pr-8"
        :class="{ 'border-destructive': error }"
        placeholder="Voucher number"
        title="voucher number"
        @update:model-value="emit('update:modelValue', $event)"
        @blur="emit('blur', modelValue)"
      />

      <div v-if="checking" class="absolute right-2 top-1/2 -translate-y-1/2">
        <div class="h-4 w-4 animate-spin rounded-full border-2 border-primary border-t-transparent"></div>
      </div>

      <div v-else-if="modelValue && !error" class="absolute right-2 top-1/2 -translate-y-1/2">
        <Check class="h-4 w-4 text-green-600" />
      </div>
      <div v-else-if="modelValue && error" class="absolute right-2 top-1/2 -translate-y-1/2">
        <TriangleAlert class="h-4 w-4 text-red-600" />
      </div>
    </div>

    <Button
      type="button"
      variant="ghost"
      size="sm"
      @click="emit('suggest')"
      class="h-8 px-2"
      title="Generate next available number"
    >
      <RefreshCcw class="h-4 w-4" />
    </Button>
  </div>

  <p v-if="purchaseOrder?.po_no" class="text-sm text-muted-foreground mt-1">
    Purchase Order #: <span class="font-medium mr-2">{{ purchaseOrder?.po_no }}</span>
    Amount: <span class="font-medium">{{ formatCurrency(purchaseOrder?.amount) }}</span>
  </p>
</template>
