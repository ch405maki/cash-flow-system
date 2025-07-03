<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea';

defineProps({
    form: Object
});
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="grid gap-2">
            <Label for="issue_date">Issue Date (to supplier) *</Label>
            <Input id="issue_date" type="date" v-model="form.issue_date"  />
        </div>
        <div class="grid gap-2">
            <Label for="delivery_date">Delivery Date *</Label>
            <Input id="delivery_date" type="date" v-model="form.delivery_date" />
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-1 gap-6 mb-6">
        <div class="grid gap-2">
            <Label for="payment_date">Remarks</Label>
            <Textarea
                id="remarks"
                v-model="form.remarks"
                placeholder="Enter remarks here"
                class="min-h-[100px] mt-2"
                />
        </div>
    </div>
</template>