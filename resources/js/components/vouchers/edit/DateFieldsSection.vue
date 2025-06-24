<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

defineProps<{
  form: any
}>()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="grid gap-2">
      <Label for="issue_date">Issue Date *</Label>
      <Input id="issue_date" type="date" v-model="form.issue_date" required />
    </div>

    <div class="grid gap-2">
      <Label for="payment_date">Payment Date *</Label>
      <Input id="payment_date" type="date" v-model="form.payment_date" required />
    </div>

    <div class="grid gap-2">
      <Label for="delivery_date">Delivery Date *</Label>
      <Input id="delivery_date" type="date" v-model="form.delivery_date" required />
    </div>
  </div>
</template>