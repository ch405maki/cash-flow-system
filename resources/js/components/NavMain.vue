<script setup lang="ts">
import { ref, watchEffect } from "vue"
import { 
    SidebarGroup, 
    SidebarGroupLabel, 
    SidebarMenu, 
    SidebarMenuButton, 
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem
} from '@/components/ui/sidebar';
import { Collapsible, CollapsibleTrigger, CollapsibleContent } from '@/components/ui/collapsible';
import { ChevronDown } from 'lucide-vue-next';
import { Link, usePage } from '@inertiajs/vue3';
import type { NavItem } from '@/types';
import type { SharedData } from '@/types';

    interface DropdownNavItem extends NavItem {
    children?: NavItem[];
    isOpen?: boolean;
    }

    const props = defineProps<{
    items: DropdownNavItem[] | Ref<DropdownNavItem[]>;
    groupLabel?: string;
    }>()

    const page = usePage<SharedData>()

    // ✅ make a local reactive copy so Collapsible can toggle it
    const localItems = ref<DropdownNavItem[]>([])

    watchEffect(() => {
    const arr = Array.isArray(props.items) ? props.items : props.items.value
    // clone so we can mutate isOpen safely
    localItems.value = arr.map(i => ({ ...i }))
    })
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel v-if="groupLabel">{{ groupLabel }}</SidebarGroupLabel>
        <SidebarMenu>
        <template v-for="item in localItems" :key="item.title">
            <!-- Normal item -->
            <SidebarMenuItem v-if="!item.children">
            <SidebarMenuButton as-child :is-active="item.href === page.url" :tooltip="item.title">
                <Link :href="item.href">
                <component :is="item.icon" class="w-4 h-4" />
                <span>{{ item.title }}</span>
                </Link>
            </SidebarMenuButton>
            </SidebarMenuItem>

            <!-- Dropdown -->
            <template v-else>
            <Collapsible v-model:open="item.isOpen">
                <SidebarMenuItem>
                <CollapsibleTrigger asChild>
                    <SidebarMenuButton :is-active="item.children?.some(c => c.href === page.url)">
                    <component :is="item.icon" class="w-4 h-4" />
                    <span>{{ item.title }}</span>
                    <ChevronDown class="w-4 h-4 ml-auto transition-transform" :class="{ 'rotate-180': item.isOpen }"/>
                    </SidebarMenuButton>
                </CollapsibleTrigger>
                </SidebarMenuItem>

                <CollapsibleContent>
                <SidebarMenuSub>
                    <SidebarMenuSubItem v-for="child in item.children" :key="child.title">
                    <SidebarMenuButton as-child :is-active="child.href === page.url">
                        <Link :href="child.href">
                        <span>{{ child.title }}</span>
                        </Link>
                    </SidebarMenuButton>
                    </SidebarMenuSubItem>
                </SidebarMenuSub>
                </CollapsibleContent>
            </Collapsible>
            </template>
        </template>
        </SidebarMenu>
    </SidebarGroup>
</template>
