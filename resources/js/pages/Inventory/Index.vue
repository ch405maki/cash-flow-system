<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Skeleton } from '@/components/ui/skeleton';
import { 
  Search, 
  Package, 
  RefreshCw, 
  Filter, 
  X 
} from 'lucide-vue-next';
import axios from 'axios';
import { useToast } from 'vue-toastification';
import PageHeader from '@/components/PageHeader.vue';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table'

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Inventory',
        href: '/inventory',
    },
];

const toast = useToast();

// State
const items = ref<any[]>([]);
const loading = ref(true);
const searchQuery = ref('');
const selectedCategory = ref<string>('all');

// Computed - Unique categories for filter
const categories = computed(() => {
  const uniqueCategories = new Map();
  
  items.value.forEach(item => {
    if (item.category && item.category.id) {
      uniqueCategories.set(item.category.id, {
        id: item.category.id,
        name: item.category.name
      });
    }
  });
  
  return Array.from(uniqueCategories.values());
});

// Computed - Filtered items based on search and category
const filteredItems = computed(() => {
  let filtered = items.value;
  
  // Filter by category
  if (selectedCategory.value !== 'all') {
    filtered = filtered.filter(item => 
      item.category?.id?.toString() === selectedCategory.value
    );
  }
  
  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(item => 
      item.name.toLowerCase().includes(query) ||
      item.product_code.toLowerCase().includes(query) ||
      item.category?.name.toLowerCase().includes(query) ||
      item.unit?.name.toLowerCase().includes(query)
    );
  }
  
  return filtered;
});

// Get stock status class
const getStockStatusClass = (quantity: number, minStock: number) => {
    if (quantity <= 0) return 'text-red-600 font-semibold';
    if (quantity <= minStock) return 'text-yellow-600 font-semibold';
    return 'text-green-600';
};

// Get stock status text
const getStockStatusText = (quantity: number, minStock: number) => {
    if (quantity <= 0) return 'Out of Stock';
    if (quantity <= minStock) return 'Low Stock';
    return 'In Stock';
};

// Get stock status color for badge
const getStockStatusBadgeClass = (quantity: number, minStock: number) => {
    if (quantity <= 0) return 'bg-red-100 text-red-800';
    if (quantity <= minStock) return 'bg-yellow-100 text-yellow-800';
    return 'bg-green-100 text-green-800';
};

// Fetch items from API
const fetchItems = async () => {
    loading.value = true;
    try {
        const response = await axios.get('http://192.168.0.145/api/items');
        items.value = response.data;
    } catch (error) {
        console.error('Error fetching items:', error);
        toast.error('Failed to load inventory items');
    } finally {
        loading.value = false;
    }
};

// Refresh data
const refreshData = () => {
    fetchItems();
    toast.info('Refreshing inventory data...');
};

// Clear all filters
const clearAllFilters = () => {
    searchQuery.value = '';
    selectedCategory.value = 'all';
};

// Clear search only
const clearSearch = () => {
    searchQuery.value = '';
};

// Format date
const formatDate = (dateString: string) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

// Check if any filters are active
const hasActiveFilters = computed(() => {
    return searchQuery.value !== '' || selectedCategory.value !== 'all';
});

onMounted(() => {
    fetchItems();
});
</script>

<template>
    <Head title="Inventory" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
                <PageHeader 
                    class="capitalize"
                    title="Inventory Management"
                    subtitle="Manage and track your inventory items"
                />
                <Button 
                    @click="refreshData" 
                    variant="outline" 
                    size="sm"
                    :disabled="loading"
                    class="h-9"
                >
                    <RefreshCw :class="['h-4 w-4', loading && 'animate-spin']" />
                    Refresh
                </Button>
            </div>

            <!-- Filters Section -->
            <div class="flex flex-col sm:flex-row gap-3">
                <!-- Search Input -->
                <div class="relative flex-1 max-w-md">
                    <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-gray-400" />
                    <Input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search by name, code, category, or unit..."
                        class="pl-9 h-9"
                    />
                    <button
                        v-if="searchQuery"
                        @click="clearSearch"
                        class="absolute right-3 top-1/2 transform -translate-y-1/2"
                    >
                        <X class="h-4 w-4 text-gray-400" />
                    </button>
                </div>

                <!-- Category Filter Dropdown -->
                <div class="w-full sm:w-64">
                    <Select v-model="selectedCategory">
                        <SelectTrigger class="h-9">
                            <div class="flex items-center gap-2">
                                <Filter class="h-4 w-4 text-gray-400" />
                                <SelectValue placeholder="All Categories" />
                            </div>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">
                                <div class="flex items-center gap-2">
                                    <Package class="h-4 w-4" />
                                    All Categories
                                </div>
                            </SelectItem>
                            <SelectItem 
                                v-for="category in categories" 
                                :key="category.id" 
                                :value="category.id.toString()"
                            >
                                {{ category.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <!-- Clear Filters Button -->
                <Button
                    v-if="hasActiveFilters"
                    @click="clearAllFilters"
                    variant="ghost"
                    size="sm"
                    class="h-9"
                >
                    <X class="h-4 w-4 mr-2" />
                    Clear Filters
                </Button>
            </div>

            <!-- Results Info -->
            <div class="flex justify-between items-center text-sm">
                <div class="text-gray-500">
                    Showing {{ filteredItems.length }} of {{ items.length }} items
                </div>
                <div v-if="selectedCategory !== 'all'" class="text-xs text-primary">
                    Filtered by: {{ categories.find(c => c.id.toString() === selectedCategory)?.name }}
                </div>
            </div>

            <!-- Loading Skeleton -->
            <div v-if="loading" class="rounded-lg border shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y">
                        <thead>
                            <tr>
                                <th v-for="i in 8" :key="i" class="px-4 py-3">
                                    <Skeleton class="h-4 w-20" />
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y ">
                            <tr v-for="i in 5" :key="i">
                                <td v-for="j in 8" :key="j" class="px-4 py-3">
                                    <Skeleton class="h-4 w-full" />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Table -->
            <div v-else class="overflow-hidden">
                <div class="overflow-x-auto">
                    <Table class="min-w-full divide-y">
                        <TableHeader>
                            <TableRow>
                                <TableHead >Product Code</TableHead>
                                <TableHead >Item Name</TableHead>
                                <TableHead >Category</TableHead>
                                <TableHead >Quantity</TableHead>
                                <TableHead >Unit</TableHead>
                                <TableHead >Min Stock</TableHead>
                                <TableHead >Status</TableHead>
                                <TableHead >Last Updated</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow 
                                v-for="item in filteredItems" 
                                :key="item.id"
                                class="transition-colors"
                            >
                                <TableCell>
                                    {{ item.product_code }}
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <Package class="h-4 w-4 text-gray-400 mr-2" />
                                        <span class="text-sm font-medium">{{ item.name }}</span>
                                    </div>
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ item.category?.name || 'Uncategorized' }}
                                    </span>
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap text-sm text-right font-medium">
                                    <span :class="getStockStatusClass(item.quantity, item.min_stock)">
                                        {{ item.quantity }}
                                    </span>
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.unit?.name || 'N/A' }}
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ item.min_stock }}
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 py-1 text-xs rounded-full font-medium',
                                        getStockStatusBadgeClass(item.quantity, item.min_stock)
                                    ]">
                                        {{ getStockStatusText(item.quantity, item.min_stock) }}
                                    </span>
                                </TableCell>
                                <TableCell class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                                    {{ formatDate(item.updated_at) }}
                                </TableCell>
                            </TableRow>
                            
                            <!-- Empty State -->
                            <TableRow v-if="filteredItems.length === 0">
                                <TableCell colspan="8" class="px-4 py-12 text-center text-gray-500">
                                    <Package class="h-12 w-12 mx-auto text-gray-300 mb-3" />
                                    <p class="text-sm">No items found</p>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ hasActiveFilters ? 'Try adjusting your filters' : 'No inventory items available' }}
                                    </p>
                                    <Button 
                                        v-if="hasActiveFilters"
                                        @click="clearAllFilters" 
                                        variant="outline" 
                                        size="sm"
                                        class="mt-3"
                                    >
                                        Clear All Filters
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                
                <!-- Footer Stats -->
                <div class="px-4 py-3">
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-2 text-sm">
                        <div class="text-gray-500">
                            Total Items: <span class="font-medium text-gray-900">{{ items.length }}</span>
                            <span v-if="hasActiveFilters" class="text-xs text-gray-400 ml-2">
                                (Filtered: {{ filteredItems.length }})
                            </span>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                <span class="text-xs text-gray-600">In Stock</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                                <span class="text-xs text-gray-600">Low Stock</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 rounded-full bg-red-500"></div>
                                <span class="text-xs text-gray-600">Out of Stock</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>