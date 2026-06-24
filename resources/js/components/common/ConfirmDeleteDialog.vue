<script setup lang="ts">
import { ref } from 'vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Button } from '@/components/ui/button';
import { Trash2 } from 'lucide-vue-next';

interface Props {
    itemName?: string;
    buttonSize?: 'default' | 'sm' | 'lg' | 'icon';
}

withDefaults(defineProps<Props>(), {
    itemName: 'this item',
    buttonSize: 'sm',
});

const emit = defineEmits<{ confirm: [] }>();
const open = ref(false);

function handleConfirm() {
    emit('confirm');
    open.value = false;
}
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogTrigger as-child>
            <Button variant="destructive" :size="buttonSize" @click.stop>
                <Trash2 class="h-4 w-4 mr-1" />
                Delete
            </Button>
        </AlertDialogTrigger>
        <AlertDialogContent @click.stop>
            <AlertDialogHeader>
                <AlertDialogTitle>Are you sure?</AlertDialogTitle>
                <AlertDialogDescription>
                    This will permanently delete {{ itemName }}. This action cannot be undone.
                </AlertDialogDescription>
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <AlertDialogAction @click="handleConfirm">Delete</AlertDialogAction>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
