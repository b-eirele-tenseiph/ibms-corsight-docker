<script setup lang="ts">
import { Pagination, PaginationContent, PaginationEllipsis, PaginationItem, PaginationNext, PaginationPrevious } from '@/components/ui/pagination';
import { useBreakpoints } from '@/composables/useBreakpoints';
import type { Paginate } from '@/types';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{ paginate: Paginate<any> }>();

const { isMobile, isSmallMobile } = useBreakpoints();
const isMobileView = computed(() => isMobile.value || isSmallMobile.value);

const currentPage = computed(() => Number(props.paginate.current_page));
const perPage = computed(() => Number(props.paginate.per_page));
const lastPage = computed(() => Number(props.paginate.last_page));
const total = computed(() => Number(props.paginate.total));

const changePage = (pageNum: number) => {
    router.reload({ data: { page: pageNum }, preserveUrl: true });
};

const visiblePages = computed(() => {
    const current = currentPage.value;
    const totalPages = lastPage.value;
    const limit = isMobileView.value ? 3 : 7;

    if (totalPages <= limit) return Array.from({ length: totalPages }, (_, i) => i + 1);

    if (isMobileView.value) {
        const start = Math.max(1, Math.min(current - 1, totalPages - 2));
        return [start, start + 1, start + 2];
    }

    if (current <= 4) return [1, 2, 3, 4, 5, '...', totalPages];
    if (current > totalPages - 4) return [1, '...', totalPages - 4, totalPages - 3, totalPages - 2, totalPages - 1, totalPages];
    return [1, '...', current - 1, current, current + 1, '...', totalPages];
});

const itemRange = computed(() => {
    return {
        start: total.value === 0 ? 0 : (currentPage.value - 1) * perPage.value + 1,
        end: Math.min(currentPage.value * perPage.value, total.value),
    };
});
</script>

<template>
    <Pagination
        :page="currentPage"
        :items-per-page="perPage"
        :total="total"
        @update:page="changePage"
        class="flex flex-col gap-2 font-semibold sm:flex-row sm:justify-between"
    >
        <div class="flex justify-center sm:items-center">
            <p class="text-secondary-text fluid-text-xs-sm">
                {{ $t('layout.pagination.item', { start: itemRange.start, end: itemRange.end, total }) }}
            </p>
        </div>

        <PaginationContent class="fluid-text-xs-sm flex items-center justify-center sm:justify-start">
            <PaginationPrevious
                class="hover:bg-accent transition-colors disabled:pointer-events-none disabled:opacity-50"
                :disabled="currentPage === 1"
                @click="changePage(currentPage - 1)"
            />

            <div class="flex items-center gap-1">
                <template v-for="(page, i) in visiblePages" :key="i">
                    <PaginationItem
                        v-if="typeof page === 'number'"
                        :value="page"
                        :is-active="page === currentPage"
                        @click="changePage(page)"
                        class="hover:bg-accent/70"
                    >
                        {{ page }}
                    </PaginationItem>

                    <PaginationEllipsis v-else class="size-8" />
                </template>
            </div>

            <PaginationNext
                class="hover:bg-accent transition-colors disabled:pointer-events-none disabled:opacity-50"
                :disabled="currentPage === lastPage"
                @click="changePage(currentPage + 1)"
            />
        </PaginationContent>
    </Pagination>
</template>
