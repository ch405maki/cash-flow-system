<script setup lang="ts">
import { ref, onMounted } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
  SquarePen,
  ListCollapse,
  LayoutDashboard,
  FileText,
  ClipboardCheck,
  FileCheck2,
  ShoppingCart,
  ReceiptText,
  BarChart3,
  Settings,
  Package,
  Users, 
  ShieldCheck,
  Building2,
  PencilLine,
  Logs 
} from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

interface DropdownNavItem extends NavItem {
    children?: NavItem[];
    isOpen?: boolean;
}

const user = usePage().props.auth.user;

// Load saved state from localStorage
const loadSavedState = () => {
    try {
        const saved = localStorage.getItem('sidebar-dropdown-state');
        return saved ? JSON.parse(saved) : {};
    } catch (e) {
        console.error('Failed to load sidebar state:', e);
        return {};
    }
};

// Save state to localStorage
const saveState = (groupId: string, itemId: string, isOpen: boolean) => {
    try {
        const currentState = loadSavedState();
        if (!currentState[groupId]) {
            currentState[groupId] = {};
        }
        currentState[groupId][itemId] = isOpen;
        localStorage.setItem('sidebar-dropdown-state', JSON.stringify(currentState));
    } catch (e) {
        console.error('Failed to save sidebar state:', e);
    }
};

// Initialize items with saved state
const initializeItems = (items: DropdownNavItem[], groupId: string): DropdownNavItem[] => {
    const savedState = loadSavedState();
    return items.map(item => ({
        ...item,
        isOpen: savedState[groupId]?.[item.title] ?? item.isOpen ?? false
    }));
};

// Executive Nav
const executiveMainItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
];

const executiveApprovalItems: NavItem[] = [
  {
    title: 'Request For Purchase',
    href: '/for-approval',
    icon: FileCheck2,
  },
  {
    title: 'Canvas Approval',
    href: '/canvas',
    icon: ListCollapse,
  },
  {
    title: 'P. O. Approval',
    href: '/purchase-orders',
    icon: ShoppingCart,
  },
];

// Purchasing Nav
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
];

const purchasingCanvasNavItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Canvas',
      href: '#',
      icon: ShoppingCart,
      isOpen: false,
      children: [
        { title: 'Draft', href: '/canvas' },
        { title: 'Submitted', href: '/canvas/approval?status=submitted' },
        { title: 'Approved', href: '/canvas/approval?status=approved' },
        { title: 'P. O. Created', href: '/canvas/approval?status=poCreated' },
      ],
    },
  ], 'purchasingCanvas')
);

const purchasingPONavItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Purchase Order',
      href: '#',
      icon: ShoppingCart,
      isOpen: false,
      children: [
        { title: 'Draft', href: '/purchase-orders?status=draft' },
        { title: 'For EOD Approval', href: '/purchase-orders?status=forEOD' },
        { title: 'P. O. Status', href: '/purchase-orders?status=approved' },
      ],
    },
  ], 'purchasingPO')
);

// Property Custodian
const custodianNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Department Requests',
    href: '/request',
    icon: FileText,
  },
  {
    title: 'On Process Orders',
    href: '/released-order',
    icon: Package,
  },
  {
    title: 'Inventory',
    href: '/inventory',
    icon: Package,
  },
];

const custodianApprovalItems: NavItem[] = [
  {
    title: 'Create',
    href: '/request-to-order',
    icon: Package,
  },
  {
    title: 'For EOD Approval',
    href: '/for-approval',
    icon: ClipboardCheck,
  },
  {
    title: 'P. O. Status',
    href: '/to-order/on-process',
    icon: ClipboardCheck,
  },
];

const custodianReportItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Reports',
      href: '/reports',
      icon: BarChart3,
      isOpen: false,
      children: [
        { title: 'Approved Request', href: '/reports/request?status=forPO' },
        { title: 'Completed Request', href: '/reports/request?status=released' },
        { title: 'Rejected Request', href: '/reports/request?status=rejected' },
      ],
    },
  ], 'custodianReports')
);

// Staff
const staffNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Create Request',
    href: '/request/create',
    icon: SquarePen,
  },
];

const staffRequestItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Request',
      href: '/reports',
      icon: FileText,
      isOpen: false,
      children: [
        { title: 'Create', href: '/request/create' },
        { title: 'Pending', href: '/request' },
        { title: 'On Process', href: '/request/to-receive' },
        { title: 'Completed', href: '/request/released' },
        { title: 'Rejected', href: '/request/rejected' },
      ],
    },
  ], 'staffRequests')
);

// Accounting
const accountingNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'For Voucher',
    href: '/for-voucher',
    icon: ReceiptText,
  },
  {
    title: 'Vouchers',
    href: '/vouchers',
    icon: ReceiptText,
  },
  {
    title: 'Voucher For Audit',
    href: '/voucher-approval',
    icon: ReceiptText,
  },
];

const accountingCheckNavItems: NavItem[] = [
  {
    title: 'For Check',
    href: '/approved-voucher',
    icon: ReceiptText,
  },
];

const accountingAuditNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Purchase Canvas',
    href: '/canvas',
    icon: ListCollapse,
  },
  {
    title: 'Vouchers',
    href: '/voucher-approval',
    icon: ReceiptText,
  },
];

const reportItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Reports',
      href: '/reports',
      icon: BarChart3,
      isOpen: false,
      children: [
        { title: 'Released Request Summary', href: '/reports/request-released' },
        { title: 'Petty Cash Summary', href: '/reports/petty-cash' },
        { title: 'Purchase Order Summary', href: '/reports/po-summary' },
        { title: 'Voucher Summary', href: '/reports/voucher-summary' },
      ],
    },
  ], 'reports')
);

const bursarReportItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Reports',
      href: '/reports',
      icon: BarChart3,
      isOpen: false,
      children: [
        { title: 'Petty Cash Summary', href: '/reports/petty-cash' },
      ],
    },
  ], 'bursarReports')
);

const purchasingReportItems = ref<DropdownNavItem[]>(
  initializeItems([
    {
      title: 'Reports',
      href: '/reports',
      icon: BarChart3,
      isOpen: false,
      children: [
        { title: 'Purchase Order Summary', href: '/reports/po-summary' },
      ],
    },
  ], 'purchasingReports')
);

const footerNavItems: NavItem[] = [
  {
    title: 'Settings',
    href: '/settings/password',
    icon: Settings,
  },
];

const adminNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Users',
    href: '/configuration/users',
    icon: Users,
  },
  {
    title: 'Profile Pictures',
    href: '/configuration/profile-pictures',
    icon: Users,
  },
  {
    title: 'User Access',
    href: '/configuration/users-access',
    icon: ShieldCheck,
  },
  {
    title: 'Departments',
    href: '/configuration/departments',
    icon: Building2,
  },
  {
    title: 'Signatory',
    href: '/configuration/signatory',
    icon: PencilLine,
  },
  {
    title: 'Accounts',
    href: '/configuration/account',
    icon: FileText,
  },
  {
    title: 'Petty Cash Fund',
    href: '/petty-cash/fund',
    icon: FileText,
  },
  {
    title: 'Logs',
    href: '/logs',
    icon: Logs,
  },
];

// Pettycash
const pettyCashNavItems: NavItem[] = [
  {
    title: 'Petty Cash',
    href: '/petty-cash',
    icon: SquarePen,
  },
];

const bursarNavItems: NavItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
    icon: LayoutDashboard,
  },
  {
    title: 'Petty Cash',
    href: '/bursar/petty-cash',
    icon: SquarePen,
  },
  {
    title: 'Create Request',
    href: '/request/create',
    icon: SquarePen,
  },
];

// Handle state updates from NavMain
const handleStateUpdate = (groupId: string, itemId: string, isOpen: boolean) => {
    saveState(groupId, itemId, isOpen);
    
    // Also update the local refs to keep UI in sync
    const updateItems = (items: Ref<DropdownNavItem[]>) => {
        const item = items.value.find(i => i.title === itemId);
        if (item) {
            item.isOpen = isOpen;
        }
    };

    // Update the appropriate ref based on groupId
    switch(groupId) {
        case 'purchasingCanvas':
            updateItems(purchasingCanvasNavItems);
            break;
        case 'purchasingPO':
            updateItems(purchasingPONavItems);
            break;
        case 'custodianReports':
            updateItems(custodianReportItems);
            break;
        case 'staffRequests':
            updateItems(staffRequestItems);
            break;
        case 'reports':
            updateItems(reportItems);
            break;
        case 'bursarReports':
            updateItems(bursarReportItems);
            break;
        case 'purchasingReports':
            updateItems(purchasingReportItems);
            break;
        case 'pettyCash':
            updateItems(pettyCashNavItems);
            break;
    }
};
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
          <div v-if="user?.role === 'admin'">
            <NavMain :items="adminNavItems" group-label="Configuration"/>
          </div>

          <div v-if="user?.role === 'executive_director'">
            <NavMain :items="executiveMainItems" group-label="Navigation"/>
            <NavMain :items="executiveApprovalItems" group-label="For Approval"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain 
                :items="pettyCashNavItems" 
                group-label="Petty Cash"
                group-id="pettyCash"
                @update:state="handleStateUpdate"
              />
            </div>
            <NavMain 
              :items="reportItems" 
              group-label="Reports"
              group-id="reports"
              @update:state="handleStateUpdate"
            />
          </div>
          
          <div v-if="user?.role === 'accounting'">
            <NavMain :items="accountingNavItems" group-label="Navigation"/>
            <NavMain :items="accountingCheckNavItems" group-label="Check"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain 
                :items="pettyCashNavItems" 
                group-label="Petty Cash"
                group-id="pettyCash"
                @update:state="handleStateUpdate"
              />
            </div>
            <NavMain 
              :items="staffRequestItems" 
              group-label="Request"
              group-id="staffRequests"
              @update:state="handleStateUpdate"
            />
            <NavMain 
              :items="reportItems" 
              group-label="Reports"
              group-id="reports"
              @update:state="handleStateUpdate"
            />
          </div>
          
          <div v-if="user?.role === 'audit'">
            <NavMain :items="accountingAuditNavItems" group-label="Audit"/>
            <NavMain 
              :items="pettyCashNavItems" 
              group-label="Petty Cash"
              group-id="pettyCash"
              @update:state="handleStateUpdate"
            />
            <NavMain 
              :items="reportItems" 
              group-label="Reports"
              group-id="reports"
              @update:state="handleStateUpdate"
            />
          </div>

          <div v-if="user?.role === 'bursar'">
            <NavMain :items="bursarNavItems" group-label="Petty Cash"/>
            <NavMain 
              :items="staffRequestItems" 
              group-label="Request"
              group-id="staffRequests"
              @update:state="handleStateUpdate"
            />
            <NavMain 
              :items="bursarReportItems" 
              group-label="Reports"
              group-id="bursarReports"
              @update:state="handleStateUpdate"
            />
          </div>

          <div v-if="user?.role === 'property_custodian'">
            <NavMain :items="custodianNavItems" group-label="Navigation"/>
            <NavMain :items="custodianApprovalItems" group-label="Request for Purchase"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain 
                :items="pettyCashNavItems" 
                group-label="Petty Cash"
                group-id="pettyCash"
                @update:state="handleStateUpdate"
              />
            </div>
            <NavMain 
              :items="custodianReportItems" 
              group-label="Reports"
              group-id="custodianReports"
              @update:state="handleStateUpdate"
            />
          </div>

          <div v-if="user?.role === 'purchasing'">
            <NavMain :items="purchasingNavItems" group-label="Navigation"/>
            <NavMain 
              :items="purchasingCanvasNavItems" 
              group-label="Transaction"
              group-id="purchasingCanvas"
              @update:state="handleStateUpdate"
            />
            <NavMain 
              :items="purchasingPONavItems"
              group-id="purchasingPO"
              @update:state="handleStateUpdate"
            />
            <div v-if="user?.is_petty_cash === 1">
              <NavMain 
                :items="pettyCashNavItems" 
                group-label="Petty Cash"
                group-id="pettyCash"
                @update:state="handleStateUpdate"
              />
            </div>
            <NavMain 
              :items="purchasingReportItems" 
              group-label="Reports"
              group-id="purchasingReports"
              @update:state="handleStateUpdate"
            />
          </div>

          <div v-if="user?.role === 'staff' || user?.role === 'department_head'">
            <NavMain :items="staffNavItems" group-label="Navigation"/>
            <div v-if="user?.is_petty_cash === 1">
              <NavMain 
                :items="pettyCashNavItems" 
                group-label="Petty Cash"
                group-id="pettyCash"
                @update:state="handleStateUpdate"
              />
            </div>
            <NavMain 
              :items="staffRequestItems" 
              group-label="Request"
              group-id="staffRequests"
              @update:state="handleStateUpdate"
            />
          </div>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems"/>
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>