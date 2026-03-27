<template>
    <div v-if="links.length > 3" class="flex flex-wrap justify-center gap-1">
        <template v-for="(link, key) in links" :key="key">
            <Button
                v-if="link.url"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                @click="() => router.visit(link.url)"
                v-html="link.label"
                :disabled="link.active"
            />
            <Button
                v-else
                variant="outline"
                size="sm"
                disabled
                v-html="link.label"
            />
        </template>
    </div>
</template>

<script setup>
import { router } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'

defineProps({
    links: Array
})
</script>