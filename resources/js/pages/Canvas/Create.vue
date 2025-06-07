<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { UploadCloud } from 'lucide-vue-next';

const form = useForm({
    file: null,
    note: '',
    remarks: '',
});

const submit = () => {
    form.post(route('canvas.store'), {
        onSuccess: () => form.reset(),
    });
};
</script>

<template>
    <Head title="Upload Canvas" />
    
    <AppLayout>
        <div class="max-w-2xl mx-auto p-6">
            <h1 class="text-2xl font-bold mb-6">Upload New Canvas</h1>
            
            <form @submit.prevent="submit" class="space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-medium">Canvas File</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-md">
                        <div class="space-y-1 text-center">
                            <UploadCloud class="mx-auto h-12 w-12 text-gray-400" />
                            <div class="flex text-sm text-gray-600">
                                <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500">
                                    <span>Upload a file</span>
                                    <input type="file" class="sr-only" @change="form.file = $event.target.files[0]">
                                </label>
                                <p class="pl-1">or drag and drop</p>
                            </div>
                            <p class="text-xs text-gray-500">
                                PDF, DOCX, XLSX up to 10MB
                            </p>
                        </div>
                    </div>
                    <p v-if="form.errors.file" class="mt-2 text-sm text-red-600">
                        {{ form.errors.file }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="remarks" class="block text-sm font-medium">Remarks</label>
                    <textarea
                        id="remarks"
                        v-model="form.remarks"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <p v-if="form.errors.remarks" class="mt-2 text-sm text-red-600">
                        {{ form.errors.remarks }}
                    </p>
                </div>

                <div class="space-y-2">
                    <label for="note" class="block text-sm font-medium">Note</label>
                    <textarea
                        id="note"
                        v-model="form.note"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    ></textarea>
                    <p v-if="form.errors.note" class="mt-2 text-sm text-red-600">
                        {{ form.errors.note }}
                    </p>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        :class="{ 'opacity-50': form.processing }"
                    >
                        <span v-if="form.processing">Uploading...</span>
                        <span v-else>Upload Canvas</span>
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>