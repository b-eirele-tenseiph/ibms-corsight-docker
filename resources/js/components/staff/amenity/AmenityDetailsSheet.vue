<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Sheet, SheetClose, SheetContent } from '@/components/ui/sheet'; // Use raw sheet components
import { useModal } from '@/composables/useModal';
import { getCategoryBadgeClass } from '@/lib/badge-colors';
import { Amenity, AmenityPhoto } from '@/types';
import { Icon } from '@iconify/vue';
import AmenityDetailsImageCarousel from './AmenityDetailsImageCarousel.vue';

defineProps<{
    open: boolean;
    amenity: Amenity | null;
}>();

defineEmits(['close']);

const IMAGE_CAROUSEL_MODAL = useModal<{
    photos: AmenityPhoto[] | null;
    initialIndex: number;
}>();
</script>

<template>
    <Sheet
        :open="open"
        @update:open="
            (v) => {
                if (!v) $emit('close');
            }
        "
    >
        <SheetContent
            :showClose="false"
            side="right"
            class="3xl:min-w-[700px] bg-card w-full gap-y-0 rounded-tl-[8px] rounded-bl-[8px] p-0 sm:min-w-[500px] xl:min-w-[600px]"
        >
            <div class="border-page-border flex items-center gap-2 border-b p-4">
                <SheetClose as-child>
                    <button class="hover:bg-muted/50 cursor-pointer rounded p-1" aria-label="Close">
                        <Icon icon="lucide:arrow-left-to-line" class="h-5 w-5 rotate-180" />
                    </button>
                </SheetClose>

                <div class="ml-auto">
                    <Badge :class="getCategoryBadgeClass(amenity?.amenity_category?.name ?? '')" class="font-semibold">
                        {{ amenity?.amenity_category?.name ?? '—' }}
                    </Badge>
                </div>
            </div>

            <div class="max-h-[calc(100vh-60px)] space-y-6 overflow-y-auto p-6">
                <div class="flex flex-col gap-y-2">
                    <h1 class="text-primary-text text-lg font-bold">{{ amenity?.title }}</h1>
                    <p class="text-secondary-text text-sm leading-relaxed">{{ amenity?.description }}</p>
                </div>

                <div class="flex flex-col gap-y-4">
                    <div class="flex items-center gap-3">
                        <p class="text-secondary-text text-xs font-semibold whitespace-nowrap">
                            {{ $t('form.label.additional_details') }}
                        </p>
                        <hr class="border-page-border flex-1 border-t" />
                    </div>

                    <div class="flex flex-col gap-y-2">
                        <template v-if="amenity?.attributes?.length">
                            <div
                                v-for="(attribute, index) in amenity.attributes"
                                :key="index"
                                class="border-page-border/50 grid grid-cols-1 items-start gap-x-6 border-b py-1 pb-2 last:border-0 sm:grid-cols-2"
                            >
                                <div class="text-secondary-text text-xs font-medium">
                                    {{ attribute.name }}
                                </div>
                                <div class="text-primary-text text-sm break-words whitespace-pre-wrap sm:text-right">
                                    {{ attribute.description || '—' }}
                                </div>
                            </div>
                        </template>

                        <template v-else>
                            <p class="text-secondary-muted-text text-xs italic">
                                {{ $t('page.amenities.state.empty_detail') }}
                            </p>
                        </template>
                    </div>
                </div>

                <div class="flex flex-col gap-y-4">
                    <div class="flex items-center gap-x-4">
                        <p class="text-secondary-text text-xs font-semibold whitespace-nowrap">
                            {{ $t('form.label.amenity_photos') }}
                        </p>
                        <Badge variant="secondary" class="text-[10px]"> {{ amenity?.photos?.length || 0 }}/5 </Badge>
                    </div>

                    <div v-if="amenity?.photos?.length" class="flex w-full flex-wrap gap-2.5">
                        <div
                            v-for="(photo, index) in amenity.photos"
                            :key="photo.id"
                            @click="
                                () =>
                                    IMAGE_CAROUSEL_MODAL.open({
                                        photos: amenity?.photos ?? null,
                                        initialIndex: index,
                                    })
                            "
                            class="group relative cursor-pointer"
                        >
                            <img
                                :src="`${photo.file_path}`"
                                class="ring-page-border aspect-square h-28 w-28 rounded-md object-cover ring-1 transition-all group-hover:opacity-80"
                            />
                            <div class="absolute inset-0 flex items-center justify-center opacity-0 transition-opacity group-hover:opacity-100">
                                <Icon icon="lucide:zoom-in" class="size-6 text-white" />
                            </div>
                        </div>
                    </div>

                    <template v-else>
                        <p class="text-secondary-muted-text text-xs italic">
                            {{ $t('page.amenities.state.empty_photo') }}
                        </p>
                    </template>
                </div>
            </div>
        </SheetContent>
    </Sheet>

    <AmenityDetailsImageCarousel
        v-if="IMAGE_CAROUSEL_MODAL.visible.value"
        :photos="IMAGE_CAROUSEL_MODAL.data.value?.photos ?? []"
        :initialIndex="IMAGE_CAROUSEL_MODAL.data.value?.initialIndex ?? 0"
        :open="IMAGE_CAROUSEL_MODAL.visible.value"
        @close="IMAGE_CAROUSEL_MODAL.close"
    />
</template>
