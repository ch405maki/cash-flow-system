<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { SidebarGroup, SidebarGroupLabel } from '@/components/ui/sidebar';
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


const executiveMainItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
];
const executiveApprovalItems: NavItem[] = [
  {
    title: 'Order Request',
    href: '/for-approval',
    icon: FileCheck2,
  },
  {
    title: 'Purchase Request',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
];
const executiveReportItems: NavItem[] = [
  {
    title: 'Reports',
    href: '/reports',
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
    href: '/reports',
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

const accountingNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
  {
    title: 'Reports',
    href: '/reports',
    icon: BarChart3,
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
          <SidebarGroup class="px-2 py-0">
            <div v-if="user?.role === 'executive_director'">
              <SidebarGroupLabel>Platform</SidebarGroupLabel>
              <NavMain :items="executiveMainItems" />
              <SidebarGroupLabel>For Approval</SidebarGroupLabel>
              <NavMain :items="executiveApprovalItems" />
              <SidebarGroupLabel>Reports</SidebarGroupLabel>
              <NavMain :items="executiveReportItems" />
            </div>
            
            <div v-if="user?.role === 'accounting'">
              <SidebarGroupLabel>Platform</SidebarGroupLabel>
              <NavMain :items="accountingNavItems" />
            </div>

            <div v-if="user?.role === 'property_custodian'">
              <SidebarGroupLabel>Platform</SidebarGroupLabel>
              <NavMain :items="propertyNavItems" />
            </div>

            <div v-if="user?.role === 'purchasing'">
              <SidebarGroupLabel>Platform</SidebarGroupLabel>
              <NavMain :items="purchasingNavItems" />
            </div>

            <div v-if="user?.role === 'staff' || user?.role === 'department_head'">
              <SidebarGroupLabel>Platform</SidebarGroupLabel>
              <NavMain :items="staffNavItems" />
            </div>
          </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
