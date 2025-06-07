<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import { FileText, Clock, CheckCircle, XCircle } from 'lucide-vue-next';

defineProps({
    canvases: Array,
});

const statusIcons = {
    pending: Clock,
    approved: CheckCircle,
    rejected: XCircle,
};

const statusColors = {
    pending: 'text-yellow-500',
    approved: 'text-green-500',
    rejected: 'text-red-500',
};
</script>

<template>
    <Head title="My Canvases" />
    
    <AppLayout>
        <div class="max-w-7xl mx-auto p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">My Canvases</h1>
                <a :href="route('canvas.create')" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Upload New
                </a>
            </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                File
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Remarks
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Uploaded
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="canvas in canvases" :key="canvas.id" class="hover:bg-gray-50">
                            <!-- In your Index.vue -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <FileText class="flex-shrink-0 h-5 w-5 text-gray-400" />
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ canvas.original_filename }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ canvas.file_path }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <component 
                                        :is="statusIcons[canvas.status]" 
                                        :class="['flex-shrink-0 h-5 w-5', statusColors[canvas.status]]" 
                                    />
                                    <span class="ml-2 capitalize">{{ canvas.status }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-500 max-w-xs truncate">
                                    {{ canvas.remarks || '-' }}
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(canvas.created_at).toLocaleDateString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>