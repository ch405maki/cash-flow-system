<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import PageHeader from '@/components/PageHeader.vue'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { type BreadcrumbItem } from '@/types';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'
import {
    Tabs,
    TabsList,
    TabsTrigger,
} from '@/components/ui/tabs'
import Pagination from '@/components/ui/pagination/customPagination.vue'
import { 
    Bell, 
    Check, 
    CheckCheck, 
    Clock, 
    Eye, 
    Trash2 
} from 'lucide-vue-next'
import { formatDateTime } from '@/lib/utils'


const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  }, {
    title: 'Notifications',
    href: '#',
  },
];

const props = defineProps({
    notifications: Object
})

const tabs = [
    { label: 'All', value: 'all' },
    { label: 'Unread', value: 'unread' }
]

// Default to 'unread' tab
const activeTab = ref('unread')

// Watch for tab changes and update URL if needed
watch(activeTab, (newTab) => {
    router.get(route('notifications.index', { tab: newTab }), {}, {
        preserveState: true,
        preserveScroll: true
    })
})

// Filter notifications based on active tab
const filteredNotifications = computed(() => {
    if (!props.notifications?.data) return []
    
    if (activeTab.value === 'unread') {
        return props.notifications.data.filter(n => !n.read_at)
    }
    return props.notifications.data
})

// Count unread notifications
const unreadCount = computed(() => {
    if (!props.notifications?.data) return 0
    return props.notifications.data.filter(n => !n.read_at).length
})

// Mark single notification as read
const markAsRead = (notification) => {
    if (!notification.read_at) {
        router.patch(route('notifications.read', notification.id), {}, {
            preserveScroll: true,
        })
    }
}

// Mark all as read
const markAllAsRead = () => {
    router.patch(route('notifications.read-all'), {}, {
        preserveScroll: true
    })
}

// View notification (mark as read and redirect)
const viewNotification = (notification) => {
    if (notification.data.link) {
        if (!notification.read_at) {
            router.patch(route('notifications.read', notification.id), {}, {
                preserveScroll: true,
                onSuccess: () => {
                    window.location.href = notification.data.link
                }
            })
        } else {
            window.location.href = notification.data.link
        }
    }
}

// Delete notification
const deleteNotification = (notification) => {
    if (confirm('Delete this notification?')) {
        router.delete(route('notifications.destroy', notification.id), {
            preserveScroll: true
        })
    }
}
</script>

<template>
    <Head title="Notifications" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-6">
            <!-- Page Header -->
            <PageHeader 
                title="Notifications"
                :subtitle="`${unreadCount} unread notification${unreadCount !== 1 ? 's' : ''}`"
            >
                <template #actions>
                    <Button 
                        v-if="unreadCount > 0"
                        variant="outline" 
                        size="sm"
                        @click="markAllAsRead"
                        class="w-full sm:w-auto"
                    >
                        <CheckCheck class="h-4 w-4 mr-2" />
                        Mark all as read
                    </Button>
                </template>
            </PageHeader>
            <!-- Tabs -->
            <Tabs :default-value="activeTab" @update:model-value="activeTab = $event">
                <TabsList class="grid w-full max-w-[400px] grid-cols-2">
                    <TabsTrigger 
                        v-for="tab in tabs" 
                        :key="tab.value"
                        :value="tab.value"
                        class="relative"
                    >
                        {{ tab.label }}
                        <Badge 
                            v-if="tab.value === 'unread' && unreadCount > 0"
                            variant="secondary"
                            class="ml-2"
                        >
                            {{ unreadCount }}
                        </Badge>
                    </TabsTrigger>
                </TabsList>
            </Tabs>

            <!-- Notifications Table - Removed Card wrapper -->
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Notification</TableHead>
                        <TableHead class="w-[150px]">Date</TableHead>
                        <TableHead class="w-[120px] text-right">Actions</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <template v-if="filteredNotifications.length === 0">
                        <TableRow>
                            <TableCell  class="h-32 text-center">
                                <div class="flex flex-col items-center justify-center text-muted-foreground">
                                    <Bell class="h-8 w-8 mb-2" />
                                    <p>No notifications</p>
                                    <p class="text-sm">
                                        {{ activeTab === 'unread' ? 'You have no unread notifications' : 'No notifications to show' }}
                                    </p>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                    
                    <TableRow 
                        v-for="notification in filteredNotifications" 
                        :key="notification.id"
                        :class="[
                            !notification.read_at ? 'bg-muted/30' : '',
                            'hover:bg-muted/50 transition-colors'
                        ]"
                    >
                        <!-- Notification content with status indicator -->
                        <TableCell>
                            <div class="flex items-start gap-3">
                                <span 
                                    v-if="!notification.read_at"
                                    class="mt-1.5 h-2 w-2 rounded-full bg-blue-600 flex-shrink-0"
                                    title="Unread"
                                ></span>
                                <span 
                                    v-else
                                    class="mt-1.5 h-2 w-2 rounded-full bg-slate-600 flex-shrink-0"
                                    title="Unread"
                                ></span>
                                <div class="space-y-1">
                                    <p class="text-sm font-medium text-foreground">
                                        {{ notification.data.title || 'Notification' }}
                                    </p>
                                    <p class="text-sm text-muted-foreground line-clamp-2">
                                        {{ notification.data.message }}
                                    </p>
                                </div>
                            </div>
                        </TableCell>

                        <!-- Date -->
                        <TableCell>
                            <div class="flex items-center gap-1 text-sm text-muted-foreground whitespace-nowrap">
                                <Clock class="h-3.5 w-3.5" />
                                {{ formatDateTime(notification.created_at) }}
                            </div>
                        </TableCell>

                        <!-- Actions -->
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Button 
                                    v-if="!notification.read_at"
                                    variant="ghost" 
                                    size="icon"
                                    @click="markAsRead(notification)"
                                    class="h-8 w-8"
                                    title="Mark as read"
                                >
                                    <Check class="h-4 w-4" />
                                </Button>
                                <Button 
                                    variant="ghost" 
                                    size="icon"
                                    @click="viewNotification(notification)"
                                    class="h-8 w-8"
                                    :title="notification.data.link ? 'View details' : 'No link available'"
                                    :disabled="!notification.data.link"
                                >
                                    <Eye class="h-4 w-4" />
                                </Button>
                                <Button 
                                    variant="ghost" 
                                    size="icon"
                                    @click="deleteNotification(notification)"
                                    class="h-8 w-8 text-destructive hover:text-destructive hover:bg-destructive/10"
                                    title="Delete"
                                >
                                    <Trash2 class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>

            <!-- Pagination -->
            <div v-if="notifications.meta && notifications.meta.last_page > 1" class="border-t px-6 py-4">
                <Pagination :links="notifications.meta.links" />
            </div>
        </div>
    </AppLayout>
</template>