<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import {
    NavigationMenu,
    NavigationMenuItem,
    NavigationMenuLink,
    NavigationMenuList,
    navigationMenuTriggerStyle,
} from '@/components/ui/navigation-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import UserMenuContent from '@/components/UserMenuContent.vue';
import { getInitials } from '@/composables/useInitials';
import type { BreadcrumbItem, NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Menu, Search, Settings } from 'lucide-vue-next';
import { computed } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItem[];
}

const props = withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const page = usePage();
const auth = computed(() => page.props.auth);

const isCurrentRoute = computed(() => (url: string) => page.url === url);

const activeItemStyles = computed(
    () => (url: string) => (isCurrentRoute.value(url) ? 'text-neutral-900 dark:bg-neutral-800 dark:text-neutral-100' : ''),
);

// Group related nav items
const requestNavItems: NavItem[] = [
    {
        title: 'Requests',
        href: '/request',
        icon: LayoutGrid,
    },
    {
        title: 'Requests To Order',
        href: '/request-to-order',
        icon: LayoutGrid,
    },
    {
        title: 'Approved Request',
        href: '/approved-request',
        icon: LayoutGrid,
    },
];

const purchaseNavItems: NavItem[] = [
    {
        title: 'Purchase Order',
        href: '/purchase-orders',
        icon: LayoutGrid,
    },
];

const voucherNavItems: NavItem[] = [
    {
        title: 'Vouchers',
        href: '/vouchers',
        icon: LayoutGrid,
    },
];

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
];

const rightNavItems: NavItem[] = [
    {
        title: 'Configurations',
        href: '/configuration/users',
        icon: Settings,
    },
];
</script>

<template>
    <div>
        <div class="border-b border-sidebar-border/80">
            <div class="mx-auto flex h-16 items-center px-4 md:max-w-7xl">
                <!-- Mobile Menu -->
                <div class="lg:hidden">
                    <Sheet>
                        <SheetTrigger :as-child="true">
                            <Button variant="ghost" size="icon" class="mr-2 h-9 w-9">
                                <Menu class="h-5 w-5" />
                            </Button>
                        </SheetTrigger>
                        <SheetContent side="left" class="w-[300px] p-6">
                            <SheetTitle class="sr-only">Navigation Menu</SheetTitle>
                            <SheetHeader class="flex justify-start text-left">
                                <AppLogoIcon class="size-6 fill-current text-black dark:text-white" />
                            </SheetHeader>
                            <div class="flex h-full flex-1 flex-col justify-between space-y-4 py-6">
                                <nav class="-mx-3 space-y-1">
                                    <Link
                                        v-for="item in mainNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles(item.href)"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        {{ item.title }}
                                    </Link>
                                    
                                    <!-- Request dropdown items -->
                                    <div class="px-3 py-2">
                                        <div class="text-sm font-medium text-neutral-500">Requests</div>
                                        <div class="mt-1 space-y-1 pl-4">
                                            <Link
                                                v-for="item in requestNavItems"
                                                :key="item.title"
                                                :href="item.href"
                                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                                {{ item.title }}
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <!-- Purchase dropdown items -->
                                    <div class="px-3 py-2">
                                        <div class="text-sm font-medium text-neutral-500">Purchasing</div>
                                        <div class="mt-1 space-y-1 pl-4">
                                            <Link
                                                v-for="item in purchaseNavItems"
                                                :key="item.title"
                                                :href="item.href"
                                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                                {{ item.title }}
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="px-3 py-2">
                                        <div class="text-sm font-medium text-neutral-500">Voucher</div>
                                        <div class="mt-1 space-y-1 pl-4">
                                            <Link
                                                v-for="item in voucherNavItems"
                                                :key="item.title"
                                                :href="item.href"
                                                class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                                {{ item.title }}
                                            </Link>
                                        </div>
                                    </div>
                                    
                                    <Link
                                        href="/reports"
                                        class="flex items-center gap-x-3 rounded-lg px-3 py-2 text-sm font-medium hover:bg-accent"
                                        :class="activeItemStyles('/reports')"
                                    >
                                        <LayoutGrid class="h-5 w-5" />
                                        Reports
                                    </Link>
                                </nav>
                                <div class="flex flex-col space-y-4">
                                    <a
                                        v-for="item in rightNavItems"
                                        :key="item.title"
                                        :href="item.href"
                                        class="flex items-center space-x-2 text-sm font-medium"
                                    >
                                        <component v-if="item.icon" :is="item.icon" class="h-5 w-5" />
                                        <span>{{ item.title }}</span>
                                    </a>
                                </div>
                            </div>
                        </SheetContent>
                    </Sheet>
                </div>

                <Link :href="route('dashboard')" class="flex items-center gap-x-2">
                    <AppLogo />
                </Link>

                <!-- Desktop Menu -->
                <div class="hidden h-full lg:flex lg:flex-1">
                    <NavigationMenu class="ml-10 flex h-full items-stretch">
                        <NavigationMenuList class="flex h-full items-stretch space-x-2">
                            <!-- Dashboard -->
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <Link href="/dashboard">
                                    <NavigationMenuLink
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles('/dashboard'), 'h-9 cursor-pointer px-3']"
                                    >
                                        <LayoutGrid class="mr-2 h-4 w-4" />
                                        Dashboard
                                    </NavigationMenuLink>
                                </Link>
                                <div
                                    v-if="isCurrentRoute('/dashboard')"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                            
                            <!-- Requests Dropdown -->
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        :class="[navigationMenuTriggerStyle(), 'h-9 cursor-pointer px-3']"
                                    >
                                        <LayoutGrid class="mr-2 h-4 w-4" />
                                        Requests
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-48">
                                        <DropdownMenuItem v-for="item in requestNavItems" :key="item.title" as-child>
                                            <Link
                                                :href="item.href"
                                                class="w-full"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                {{ item.title }}
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </NavigationMenuItem>
                            
                            <!-- Purchasing Dropdown -->
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        :class="[navigationMenuTriggerStyle(), 'h-9 cursor-pointer px-3']"
                                    >
                                        <LayoutGrid class="mr-2 h-4 w-4" />
                                        Purchasing
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-48">
                                        <DropdownMenuItem v-for="item in purchaseNavItems" :key="item.title" as-child>
                                            <Link
                                                :href="item.href"
                                                class="w-full"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                {{ item.title }}
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </NavigationMenuItem>

                            <!-- Purchasing Dropdown -->
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <DropdownMenu>
                                    <DropdownMenuTrigger
                                        :class="[navigationMenuTriggerStyle(), 'h-9 cursor-pointer px-3']"
                                    >
                                        <LayoutGrid class="mr-2 h-4 w-4" />
                                        Voucher
                                    </DropdownMenuTrigger>
                                    <DropdownMenuContent class="w-48">
                                        <DropdownMenuItem v-for="item in voucherNavItems" :key="item.title" as-child>
                                            <Link
                                                :href="item.href"
                                                class="w-full"
                                                :class="activeItemStyles(item.href)"
                                            >
                                                {{ item.title }}
                                            </Link>
                                        </DropdownMenuItem>
                                    </DropdownMenuContent>
                                </DropdownMenu>
                            </NavigationMenuItem>
                            
                            <!-- Reports -->
                            <NavigationMenuItem class="relative flex h-full items-center">
                                <Link href="/reports">
                                    <NavigationMenuLink
                                        :class="[navigationMenuTriggerStyle(), activeItemStyles('/reports'), 'h-9 cursor-pointer px-3']"
                                    >
                                        <LayoutGrid class="mr-2 h-4 w-4" />
                                        Reports
                                    </NavigationMenuLink>
                                </Link>
                                <div
                                    v-if="isCurrentRoute('/reports')"
                                    class="absolute bottom-0 left-0 h-0.5 w-full translate-y-px bg-black dark:bg-white"
                                ></div>
                            </NavigationMenuItem>
                        </NavigationMenuList>
                    </NavigationMenu>
                </div>

                <div class="ml-auto flex items-center space-x-2">
                    <div class="relative flex items-center space-x-1">
                        <Button variant="ghost" size="icon" class="group h-9 w-9 cursor-pointer">
                            <Search class="size-5 opacity-80 group-hover:opacity-100" />
                        </Button>

                        <div class="hidden space-x-1 lg:flex">
                            <template v-for="item in rightNavItems" :key="item.title">
                                <TooltipProvider :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger>
                                            <Button variant="ghost" size="icon" as-child class="group h-9 w-9 cursor-pointer">
                                                <a :href="item.href">
                                                    <span class="sr-only">{{ item.title }}</span>
                                                    <component :is="item.icon" class="size-5 opacity-80 group-hover:opacity-100" />
                                                </a>
                                            </Button>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>{{ item.title }}</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>
                            </template>
                        </div>
                    </div>

                    <DropdownMenu>
                        <DropdownMenuTrigger :as-child="true">
                            <Button
                                variant="ghost"
                                size="icon"
                                class="relative size-10 w-auto rounded-full p-1 focus-within:ring-2 focus-within:ring-primary"
                            >
                                <Avatar class="size-8 overflow-hidden rounded-full">
                                    <AvatarImage v-if="auth.user.avatar" :src="auth.user.avatar" :alt="auth.user.first_name" />
                                    <AvatarFallback class="rounded-lg bg-neutral-200 font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                        {{ getInitials(auth.user?.first_name) }}
                                    </AvatarFallback>
                                </Avatar>
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end" class="w-56">
                            <UserMenuContent :user="auth.user" />
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </div>

        <div v-if="props.breadcrumbs.length > 1" class="flex w-full border-b border-sidebar-border/70">
            <div class="mx-auto flex h-12 w-full items-center justify-start px-4 text-neutral-500 md:max-w-7xl">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </div>
        </div>
    </div>
</template>