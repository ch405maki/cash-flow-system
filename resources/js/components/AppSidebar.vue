<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  LayoutDashboard,
  FileText,
  ClipboardCheck,
  FileCheck2,
  ShoppingCart,
  ReceiptText,
  BarChart3,
  Settings,
  Package,
  ListTodo,
} from 'lucide-vue-next';

import AppLogo from './AppLogo.vue';

const user = usePage().props.auth.user;

const executiveNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Requests',
    href: '/request',
    icon: FileText,
  },
  {
    title: 'Request Approval',
    href: '/for-approval',
    icon: ClipboardCheck,
  },
  {
    title: 'Purchase Approval',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
  {
    title: 'Reports',
    href: '/reports/vouchers',
    icon: BarChart3,
  },
];

const purchasingNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Approved Request',
    href: '/approved-request',
    icon: FileCheck2,
  },
  {
    title: 'Purchase Approval',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
  {
    title: 'Reports',
    href: '/reports/vouchers',
    icon: BarChart3,
  },
];

const propertyNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Requests',
    href: '/request',
    icon: FileText,
  },
  {
    title: 'Requests To Order',
    href: '/request-to-order',
    icon: Package,
  },
  {
    title: 'For Approval Request',
    href: '/for-approval',
    icon: ClipboardCheck,
  },
];

const staffNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Requests',
    href: '/request',
    icon: FileText,
  },
];

const footerNavItems: NavItem[] = [
  {
    title: 'Configurations',
    href: '/configuration/users',
    icon: Settings,
  },
];
</script>


<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain v-if="user?.role === 'executive_director'" :items="executiveNavItems" />
            <NavMain v-if="user?.role === 'property_custodian'" :items="propertyNavItems" />
            <NavMain v-if="user?.role === 'purchasing'" :items="purchasingNavItems" />
            <NavMain v-if="user?.role === 'staff' || user?.role === 'department_head'" :items="staffNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
