import { ref, computed, type Ref } from 'vue';

export function usePagination<T>(items: Ref<T[]>, perPage: number = 20) {
    const currentPage = ref(1);
    const pageSize = ref(perPage);

    const totalPages = computed(() => Math.max(1, Math.ceil(items.value.length / pageSize.value)));

    const paginatedItems = computed(() => {
        const start = (currentPage.value - 1) * pageSize.value;
        return items.value.slice(start, start + pageSize.value);
    });

    const hasPrevPage = computed(() => currentPage.value > 1);
    const hasNextPage = computed(() => currentPage.value < totalPages.value);

    function goToPage(page: number) {
        currentPage.value = Math.max(1, Math.min(page, totalPages.value));
    }

    function nextPage() {
        if (hasNextPage.value) currentPage.value++;
    }

    function prevPage() {
        if (hasPrevPage.value) currentPage.value--;
    }

    function reset() {
        currentPage.value = 1;
    }

    return {
        currentPage,
        pageSize,
        totalPages,
        paginatedItems,
        hasPrevPage,
        hasNextPage,
        goToPage,
        nextPage,
        prevPage,
        reset,
    };
}
