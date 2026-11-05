import { useInfiniteScroll } from '@vueuse/core';
import { Ref, ref } from 'vue';

export function useScrollDetection(onBottom: () => void, hasMore: Ref<boolean>) {
    const scrollContainer = ref<HTMLElement | null>(null);

    useInfiniteScroll(
        scrollContainer,
        async () => {
            await onBottom();
        },
        {
            distance: 100,
            canLoadMore: () => hasMore.value,
        },
    );

    return { scrollContainer };
}
