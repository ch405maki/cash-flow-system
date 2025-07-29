<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/composables/useInitials';
import { computed } from 'vue';

interface Props {
    user: {
        id: number;
        first_name: string;
        last_name: string;
        email: string;
        profile_picture?: {
            file_path: string;
        };
    };
    showEmail?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    showEmail: false,
});

const { getInitials } = useInitials();

// Compute avatar URL from profile picture relationship
const avatarUrl = computed(() => {
    return props.user.profile_picture 
        ? `/storage/${props.user.profile_picture.file_path}`
        : null;
});
</script>

<template>
    <div class="flex items-center gap-3">
        <Avatar class="h-8 w-8 overflow-hidden rounded-full ring-1 ring-neutral-200 dark:ring-neutral-700">
            <AvatarImage 
                v-if="avatarUrl" 
                :src="avatarUrl" 
                :alt="`${user.first_name} ${user.last_name}`" 
                class="object-cover"
            />
            <AvatarFallback class="rounded-lg text-black dark:text-white">
                {{ getInitials(user.first_name) }}{{ getInitials(user.last_name) }}
            </AvatarFallback>
        </Avatar>

        <div class="grid flex-1 text-left text-sm leading-tight">
            <span class="truncate font-medium">
                {{ user.first_name }} {{ user.last_name }}
            </span>
            <span 
                v-if="showEmail" 
                class="truncate text-xs text-muted-foreground"
            >
                {{ user.email }}
            </span>
        </div>
    </div>
</template>