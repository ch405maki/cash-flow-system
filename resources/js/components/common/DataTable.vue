<script setup lang="ts">
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import EmptyState from './EmptyState.vue';

export interface Column {
    key: string;
    label: string;
    sortable?: boolean;
    class?: string;
    width?: string;
}

interface Props {
    columns: Column[];
    data: any[];
    loading?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
}

const props = withDefaults(defineProps<Props>(), {
    loading: false,
    emptyTitle: 'No records found',
    emptyDescription: 'There are no items to display.',
});

const emit = defineEmits<{
    'row-click': [item: any];
}>();
</script>

<template>
    <div>
        <div v-if="data.length === 0 && !loading">
            <EmptyState :title="emptyTitle" :description="emptyDescription" />
        </div>
        <Table v-else>
            <TableHeader>
                <TableRow>
                    <TableHead
                        v-for="col in columns"
                        :key="col.key"
                        :class="col.class"
                        :style="col.width ? { width: col.width } : undefined"
                    >
                        {{ col.label }}
                    </TableHead>
                    <TableHead class="text-right w-[100px]">Actions</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow
                    v-for="(row, idx) in data"
                    :key="row.id ?? idx"
                    @click="emit('row-click', row)"
                    class="hover:cursor-pointer"
                >
                    <TableCell
                        v-for="col in columns"
                        :key="col.key"
                        :class="col.class"
                    >
                        <slot :name="'cell-' + col.key" :item="row" :value="row[col.key]">
                            {{ row[col.key] ?? '-' }}
                        </slot>
                    </TableCell>
                    <TableCell class="text-right" @click.stop>
                        <slot name="actions" :item="row" />
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
        <slot name="pagination" />
    </div>
</template>
