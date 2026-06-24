<script setup lang="ts">
import SearchInput from './SearchInput.vue';
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectLabel,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Filter } from 'lucide-vue-next';

interface StatusOption {
    value: string;
    label: string;
}

interface Props {
    search: string;
    status?: string;
    statusOptions?: StatusOption[];
    statusLabel?: string;
    showStatusFilter?: boolean;
    showSearch?: boolean;
}

withDefaults(defineProps<Props>(), {
    status: 'all',
    statusOptions: () => [],
    statusLabel: 'Status',
    showStatusFilter: false,
    showSearch: true,
});

const emit = defineEmits<{
    'update:search': [value: string];
    'update:status': [value: string];
}>();
</script>

<template>
    <div class="flex items-center gap-3 flex-wrap">
        <SearchInput
            v-if="showSearch"
            :model-value="search"
            @update:model-value="emit('update:search', $event)"
            placeholder="Search..."
        />
        <div v-if="showStatusFilter && statusOptions.length" class="flex items-center gap-2">
            <Filter class="h-4 w-4 text-muted-foreground" />
            <Select
                :model-value="status"
                @update:model-value="emit('update:status', $event)"
            >
                <SelectTrigger class="w-[160px]">
                    <SelectValue :placeholder="statusLabel" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectLabel>{{ statusLabel }}</SelectLabel>
                        <SelectItem value="all">All</SelectItem>
                        <SelectItem v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
        <slot name="extra-filters" />
    </div>
</template>
