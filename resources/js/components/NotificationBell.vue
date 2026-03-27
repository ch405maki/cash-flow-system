<script setup>
import { computed } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { Bell, X } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
    SheetTrigger,
    SheetClose,
    SheetFooter,
} from '@/components/ui/sheet'
import { Separator } from '@/components/ui/separator'

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
    <Sheet>
        <!-- Bell Button with Badge -->
        <SheetTrigger as-child>
            <button 
                class="group inline-flex items-center rounded-full p-2 transition-all duration-300 bg-blue-50 hover:bg-blue-100 dark:bg-blue-900/20 dark:hover:bg-blue-800/30 ring-1 ring-blue-200 dark:ring-blue-800 relative"
            >
                <Bell class="h-4 w-4 text-blue-600" />
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
        </SheetTrigger>

        <!-- Sheet Content (Right Drawer) -->
        <SheetContent side="right" class="w-full sm:max-w-md p-0 flex flex-col h-full">
            <!-- Header with close button -->
            <SheetHeader class="p-6 pb-2">
                <SheetTitle>Notifications</SheetTitle>
                <SheetDescription>
                    Stay updated with your latest notifications
                </SheetDescription>
            </SheetHeader>

            <!-- Mark all as read button (if unread exist) -->
            <div v-if="unreadCount > 0" class="px-6 py-2">
                <Button 
                    variant="outline" 
                    size="sm"
                    @click="markAllAsRead"
                    class="w-full text-xs"
                >
                    Mark all as read
                </Button>
            </div>

            <Separator />

            <!-- Notifications List - Scrollable area -->
            <div class="flex-1 overflow-y-auto px-6 py-4">
                <div v-if="notifications.length === 0" class="flex flex-col items-center justify-center h-full text-center">
                    <Bell class="h-12 w-12 text-muted-foreground/50 mb-4" />
                    <p class="text-sm text-muted-foreground">No notifications yet</p>
                    <p class="text-xs text-muted-foreground/70 mt-1">We'll notify you when something arrives</p>
                </div>
                
                <div 
                    v-for="notification in notifications" 
                    :key="notification.id"
                    class="p-4 mb-2 rounded-lg border last:mb-0 cursor-pointer transition-all hover:shadow-md"
                    :class="[
                        !notification.read_at 
                            ? 'bg-blue-50/50 dark:bg-blue-900/10 border-blue-200 dark:border-blue-800' 
                            : 'bg-background hover:bg-accent/50'
                    ]"
                    @click="markAsRead(notification)"
                >
                    <div class="flex items-start gap-3">
                        <!-- Unread indicator dot -->
                        <span 
                            v-if="!notification.read_at"
                            class="relative flex h-2 w-2 mt-2 shrink-0"
                        >
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-blue-500"></span>
                        </span>
                        
                        <div class="flex-1">
                            <p class="text-sm font-medium">{{ notification.data.message }}</p>
                            <p class="text-xs text-muted-foreground mt-1">{{ notification.created_at }}</p>
                            
                            <!-- Show link preview if exists -->
                            <p v-if="notification.data.link" class="text-xs text-blue-600 mt-2 truncate">
                                Click to view details →
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer with View All link -->
            <SheetFooter class="p-6 pt-2 border-t">
                <Button 
                    variant="link" 
                    @click="goToAllNotifications"
                    class="w-full text-sm text-blue-600"
                >
                    View all notifications
                </Button>
            </SheetFooter>
        </SheetContent>
    </Sheet>
</template>