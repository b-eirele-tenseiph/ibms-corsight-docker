<script setup lang="ts">
import { StateEmpty } from '@/components/shared/states'; // Import the StateEmpty component
import AmenityCard from '@/components/user/amenity/AmenityCard.vue';
import AmenitySlideOver from '@/components/user/amenity/AmenitySlideOver.vue';
import { UserPageLayout } from '@/layouts/user';
import UserLayout from '@/layouts/UserLayout.vue';
import { index } from '@/routes/user/amenities';
import type { Amenity } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { useEcho } from '@laravel/echo-vue';
import { computed, onMounted, onUnmounted, ref, watch } from 'vue';

const props = withDefaults(
    defineProps<{
        amenities: Amenity[];
        activeAmenity?: Amenity | null;
        roomName?: string | null;
    }>(),
    {
        amenities: () => [],
        activeAmenity: null,
    },
);

const amenities = ref<Amenity[]>([...props.amenities]);
const visibleAmenities = computed(() => amenities.value.filter((a) => a?.is_visible === true));

// Slide-over open state
const open = ref(false);
const activeVisibleAmenity = computed<Amenity | null>(() =>
    props.activeAmenity && props.activeAmenity.is_visible === true ? props.activeAmenity : null,
);

watch(activeVisibleAmenity, (v) => (open.value = !!v), { immediate: true });

function closePanel() {
    router.get(index.url(), {}, { replace: true, preserveScroll: false, preserveState: true });
}

const { stopListening: stopAmenity, listen: listenAmenity } = useEcho(
    'amenities',
    '.AmenityVisibility',
    (e: { amenity_id: string; is_visible: boolean }) => {
        const i = amenities.value.findIndex((a) => a.id === e.amenity_id);
        if (i !== -1) {
            amenities.value[i] = { ...amenities.value[i], is_visible: e.is_visible };
        } else {
        }
        if (props.activeAmenity?.id === e.amenity_id && e.is_visible === false) {
            open.value = false;
        }
    },
);

onMounted(() => {
    listenAmenity();
});

onUnmounted(() => {
    stopAmenity();
});
</script>

<template>
    <Head :title="$t('head.amenity')" />
    <UserLayout :title="roomName">
        <UserPageLayout>
            <div class="px-3">
                <div v-if="visibleAmenities.length > 0" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <AmenityCard v-for="amenity in visibleAmenities" :key="amenity.id" :amenity="amenity" />
                </div>
                <StateEmpty v-else class="mt-20" />
            </div>
            <AmenitySlideOver :amenity="activeVisibleAmenity" :open="open" @close="closePanel" />
        </UserPageLayout>
    </UserLayout>
</template>
