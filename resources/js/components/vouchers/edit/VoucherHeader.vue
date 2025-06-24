<script setup lang="ts">
import { CardTitle, CardDescription } from '@/components/ui/card'

defineProps<{
  voucherNo: string
}>()
</script>

<template>
  <div>
    <CardTitle>Voucher {{ voucherNo }}</CardTitle>
    <CardDescription>Update voucher details</CardDescription>
  </div>
</template>