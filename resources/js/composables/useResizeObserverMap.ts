import type { ComponentPublicInstance, VNodeRef } from 'vue';
import { onBeforeUnmount, reactive, ref } from 'vue';

export function useResizeObserverMap() {
    const contentRefs = ref<Record<number | string, HTMLElement | null>>({});
    const heights = reactive<Record<number | string, number>>({});
    const observers: Record<number | string, ResizeObserver> = {};

    function setContentRef(id: number | string): VNodeRef {
        return (el: Element | ComponentPublicInstance | null) => {
            const htmlEl = el instanceof HTMLElement ? el : el && '$el' in el ? ((el as any).$el as HTMLElement) : null;

            contentRefs.value[id] = htmlEl;

            if (htmlEl) {
                if (observers[id]) observers[id].disconnect();

                observers[id] = new ResizeObserver((entries) => {
                    for (const entry of entries) {
                        if (entry.contentRect) {
                            const measuredHeight = Math.round(entry.contentRect.height);
                            heights[id] = measuredHeight;
                        }
                    }
                });

                observers[id].observe(htmlEl);
            }
        };
    }

    onBeforeUnmount(() => {
        Object.values(observers).forEach((observer) => observer.disconnect());
    });

    return {
        contentRefs,
        heights,
        setContentRef,
    };
}
