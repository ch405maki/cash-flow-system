<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Bell } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'

const page = usePage()

const notifications = computed(() => page.props.notifications?.list || [])
const unreadCount = computed(() => page.props.notifications?.unread_count || 0)

const markAsRead = (notification) => {
    if (!notification.read_at) {
        router.patch(route('notifications.read', notification.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                if (notification.data.link) window.location.href = notification.data.link
            }
        })
    } else if (notification.data.link) {
        window.location.href = notification.data.link
    }
}

const markAllAsRead = () => {
    router.patch(route('notifications.read-all'), {}, {
        preserveScroll: true
    })
}

const goToAllNotifications = () => {
    router.visit(route('notifications.index'))
}
</script>

<template>
    <Popover>
        <!-- Bell Button with Badge -->
        <PopoverTrigger as-child>
            <button 
                class="group inline-flex items-center rounded-full p-2 transition-all duration-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-800/30 ring-1 ring-blue-200 dark:ring-blue-800 relative"
            >
                <Bell class="h-4 w-4" />
                <span 
                    v-if="unreadCount > 0" 
                    class="absolute -top-2 -right-2 flex h-5 w-5 items-center justify-center"
                >
                    <!-- Ping animation ring - centered behind badge -->
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-red-400 opacity-75"></span>
                    <!-- Badge -->
                    <span class="relative flex h-5 w-5 items-center justify-center rounded-full bg-red-600 text-[10px] font-medium text-white ring-2 ring-white dark:ring-neutral-900">
                        {{ unreadCount > 9 ? '9+' : unreadCount }}
                    </span>
                </span>
            </button>
        </PopoverTrigger>

        <!-- Popover Content -->
        <PopoverContent class="w-80 p-0" align="end">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <h4 class="text-sm font-semibold">Notifications</h4>
                <Button 
                    v-if="unreadCount > 0"
                    variant="ghost" 
                    size="sm"
                    @click="markAllAsRead"
                    class="h-auto px-2 py-1 text-xs text-blue-600"
                >
                    Mark all as read
                </Button>
            </div>

            <!-- Notifications List -->
            <div class="max-h-96 overflow-y-auto">
                <div v-if="notifications.length === 0" class="p-4 text-center text-sm text-muted-foreground">
                    No notifications
                </div>
                
                <div 
                    v-for="notification in notifications" 
                    :key="notification.id"
                    class="p-4 border-b last:border-b-0 cursor-pointer transition-colors"
                    :class="{ 'hover:underline': !notification.read_at }"
                    @click="markAsRead(notification)"
                >
                    <p class="text-sm">{{ notification.data.message }}</p>
                    <p class="text-xs text-muted-foreground mt-1">{{ notification.created_at }}</p>
                </div>
            </div>

            <!-- Footer -->
            <div class="p-4 border-t flex justify-center">
                <Button variant="link" size="sm" @click="goToAllNotifications" class="text-xs text-blue-600">
                    View all notifications
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>