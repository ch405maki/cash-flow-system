<script setup lang="ts">
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { PlusCircle } from 'lucide-vue-next';

interface DialogField {
    key: string;
    label: string;
    type: 'text' | 'textarea' | 'select' | 'number';
    placeholder?: string;
    required?: boolean;
    options?: { value: string; label: string }[];
}

interface Props {
    title: string;
    description?: string;
    fields: DialogField[];
    modelValue: Record<string, any>;
    isEditing?: boolean;
    triggerLabel?: string;
    open?: boolean;
    saveLabel?: string;
}

const props = withDefaults(defineProps<Props>(), {
    description: '',
    isEditing: false,
    triggerLabel: 'Add New',
    open: false,
    saveLabel: 'Save',
});

const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>];
    'save': [];
    'update:open': [value: boolean];
}>();
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogTrigger as-child v-if="!isEditing">
            <Button variant="default">
                <PlusCircle class="h-4 w-4 mr-1" />
                {{ triggerLabel }}
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description">{{ description }}</DialogDescription>
            </DialogHeader>
            <div class="grid gap-4 py-4">
                <div v-for="field in fields" :key="field.key" class="grid grid-cols-4 items-center gap-4">
                    <label :for="'field-' + field.key" class="text-right text-sm">
                        {{ field.label }}
                    </label>
                    <template v-if="field.type === 'textarea'">
                        <textarea
                            :id="'field-' + field.key"
                            :value="modelValue[field.key] ?? ''"
                            @input="emit('update:modelValue', { ...modelValue, [field.key]: ($event.target as HTMLTextAreaElement).value })"
                            :placeholder="field.placeholder"
                            class="col-span-3 flex h-20 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        />
                    </template>
                    <template v-else-if="field.type === 'select' && field.options">
                        <select
                            :id="'field-' + field.key"
                            :value="modelValue[field.key] ?? ''"
                            @change="emit('update:modelValue', { ...modelValue, [field.key]: ($event.target as HTMLSelectElement).value })"
                            class="col-span-3 flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        >
                            <option value="" disabled>Select...</option>
                            <option v-for="opt in field.options" :key="opt.value" :value="opt.value">
                                {{ opt.label }}
                            </option>
                        </select>
                    </template>
                    <template v-else>
                        <input
                            :id="'field-' + field.key"
                            :type="field.type"
                            :value="modelValue[field.key] ?? ''"
                            @input="emit('update:modelValue', { ...modelValue, [field.key]: ($event.target as HTMLInputElement).value })"
                            :placeholder="field.placeholder"
                            :required="field.required"
                            class="col-span-3 flex h-10 w-full rounded-md border border-input bg-transparent px-3 py-2 text-sm shadow-sm"
                        />
                    </template>
                </div>
                <slot name="extra-fields" />
            </div>
            <DialogFooter>
                <Button type="submit" @click="emit('save')">
                    {{ saveLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
