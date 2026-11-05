<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { getCategoryBadgeClass } from '@/lib/badge-colors';
import { index } from '@/routes/user/amenities';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    amenity: { type: Object, required: true },
});

const categoryName = computed(() => props.amenity.amenity_category?.name ?? '—');
</script>

<template>
    <Card class="gap-2 overflow-hidden py-0 transition">
        <CardHeader class="p-3 pb-0">
            <Badge :class="[getCategoryBadgeClass(categoryName), 'font-semibold']">
                {{ categoryName }}
            </Badge>
        </CardHeader>
        <CardContent class="space-y-3 p-3">
            <img
                :src="props.amenity.photos?.[0]?.file_path"
                :alt="props.amenity.title || 'Amenity image'"
                class="h-40 w-full rounded-md object-cover"
                loading="lazy"
                decoding="async"
            />
            <CardTitle class="text-primary-text line-clamp-1">
                {{ props.amenity.title }}
            </CardTitle>

            <CardDescription class="fluid-text-xs-sm text-secondary-text line-clamp-2">
                {{ props.amenity.description }}
            </CardDescription>
        </CardContent>
        <CardFooter class="flex justify-end p-3 pt-0">
            <Link
                :href="index.url()"
                :data="{ show: props.amenity.id }"
                class="group text-primary-link-text fluid-text-xs-sm inline-flex items-center gap-2 font-medium"
            >
                <span>{{ $t('action.discover_more') }}</span>
                <Icon icon="gg:arrow-right-o" class="h-4 w-4 shrink-0 transition-transform group-hover:translate-x-0.5" />
            </Link>
        </CardFooter>
    </Card>
</template>
