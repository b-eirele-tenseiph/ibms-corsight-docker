<script setup lang="ts">
import AmenityDetailsImageCarousel from '@/components/staff/amenity/AmenityDetailsImageCarousel.vue';
import { Sheet, SheetClose, SheetContent } from '@/components/ui/sheet';
import { getCategoryBadgeClass } from '@/lib/badge-colors';
import { Icon } from '@iconify/vue';
import { computed, ref } from 'vue';

type AttributeItem = { name: string; description?: string | null };
type Photo = { id: string; file_path: string };
type Amenity = {
    id: string;
    title: string;
    description?: string | null;
    amenity_category?: { name: string } | null;
    photos?: Photo[];
    attributes?: AttributeItem[] | null;
};

const props = defineProps<{ amenity: Amenity | null; open: boolean }>();
const emit = defineEmits<{ (e: 'close'): void }>();

const cat = computed(() => props.amenity?.amenity_category?.name ?? '—');
const attributes = computed<AttributeItem[]>(() => {
    const raw = (props.amenity?.attributes ?? []) as any;
    return Array.isArray(raw) ? raw.filter((x) => x && typeof x.name === 'string') : [];
});

/** Normalize photos so the carousel can just render them */
const photos = computed(() =>
    (props.amenity?.photos ?? []).map((p) => ({
        id: p.id,
        file_path: p.file_path?.startsWith('http') ? p.file_path : `/storage/${p.file_path}`,
    })),
);

/** Carousel state */
const showCarousel = ref(false);
const startIndex = ref(0);

function openCarousel(index: number) {
    startIndex.value = index;
    showCarousel.value = true;
}
</script>

<template>
    <Sheet
        :open="open"
        @update:open="
            (v) => {
                if (!v) emit('close');
            }
        "
    >
        <SheetContent
            :showClose="false"
            side="right"
            class="3xl:min-w-[700px] bg-card w-full gap-y-0 rounded-tl-[8px] rounded-bl-[8px] sm:min-w-[500px] xl:min-w-[600px]"
        >
            <!-- Header -->
            <div class="border-page-border flex items-center gap-2 border-b p-4">
                <SheetClose as-child>
                    <button class="hover:bg-muted/50 cursor-pointer rounded p-1" aria-label="Close">
                        <Icon icon="lucide:arrow-left-to-line" class="h-5 w-5 rotate-180" />
                    </button>
                </SheetClose>

                <span class="ml-auto inline-block rounded px-2 py-1 text-xs font-semibold" :class="getCategoryBadgeClass(cat)">
                    {{ cat }}
                </span>
            </div>

            <!-- Body (scrolls) -->
            <div class="max-h-[calc(100vh-56px)] space-y-4 overflow-y-auto p-6">
                <h2 class="text-primary-text text-lg font-semibold">
                    {{ props.amenity?.title }}
                </h2>
                <p class="text-secondary-text text-sm leading-relaxed">
                    {{ props.amenity?.description }}
                </p>

                <!-- Labeled divider -->
                <div class="my-3 flex items-center gap-2">
                    <span class="text-secondary-text text-xs font-medium">{{ $t('page.amenities.label.amenity_detail') }}</span>
                    <hr class="border-page-border flex-1 border-0 border-t" />
                </div>

                <!-- Two-column attributes -->
                <div v-if="attributes.length" class="mt-2">
                    <div v-for="(attr, i) in attributes" :key="attr.name + i" class="grid grid-cols-1 items-start gap-x-6 py-1 sm:grid-cols-2">
                        <div class="text-secondary-text text-xs font-medium">
                            {{ attr.name }}
                        </div>
                        <div class="text-primary-text text-sm break-words whitespace-pre-wrap sm:text-right">
                            {{ attr.description || '—' }}
                        </div>
                    </div>
                </div>
                <p class="text-secondary-muted-text text-xs">{{ $t('page.amenities.state.empty_detail') }}</p>

                <!-- Photos -->
                <div>
                    <h3 class="text-primary-text mb-2 text-sm font-medium">{{ $t('page.amenities.label.amenity_photo') }}</h3>
                    <div class="flex w-full flex-wrap gap-2.5">
                        <img
                            v-for="(p, i) in photos"
                            :key="p.id"
                            :src="p.file_path"
                            class="aspect-square h-30 w-30 cursor-zoom-in rounded-md object-cover"
                            loading="lazy"
                            decoding="async"
                            @click="openCarousel(i)"
                        />
                    </div>
                </div>
            </div>
        </SheetContent>
    </Sheet>

    <!-- Full-screen image carousel dialog -->
    <AmenityDetailsImageCarousel :photos="photos" :open="showCarousel" :initialIndex="startIndex" @close="showCarousel = false" />
</template>
