<script setup lang="ts">
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Button } from '@/components/ui/button'
import { Trash2 } from 'lucide-vue-next'


defineProps({
    form: Object
});

const emit = defineEmits(['file-selected']);

const handleFileChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        emit('file-selected', input.files[0]);
        input.value = '';
    }
};
</script>

<template>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="grid gap-2">
            <Label for="issue_date">Issue Date (to supplier)</Label>
            <Input id="issue_date" type="date" v-model="form.issue_date" />
        </div>
        <div class="grid gap-2">
            <Label for="delivery_date">Delivery Date</Label>
            <Input id="delivery_date" type="date" v-model="form.delivery_date" />
        </div>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="grid gap-2">
            <Label for="receipt">Receipt</Label>
            <Input 
                id="receipt" 
                type="file" 
                accept="image/*,.pdf"
                @change="handleFileChange"
            />
            <p class="text-sm text-muted-foreground">Supports JPG, PNG, PDF (Max 2MB)</p>
        </div>
        
        <div class="grid gap-2">
            <Label for="remarks">Remarks</Label>
            <Textarea
                id="remarks"
                v-model="form.remarks"
                placeholder="Enter remarks here"
                class="min-h-[100px]"
            />
        </div>
    </div>
</template>