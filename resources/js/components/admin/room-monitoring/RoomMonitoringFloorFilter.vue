<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { index } from '@/routes/admin/room-monitoring';
import { Floor, QueryParams } from '@/types';
import { router } from '@inertiajs/vue3';

const props = defineProps<{
    query: QueryParams;
    item: Array<Floor>;
}>();

const updateFloor = (floor: any) => {
    router.get(index.url(), { floor: floor || undefined }, { replace: true });
};
</script>

<template>
    <div class="flex w-full flex-col items-end">
        <div class="w-full max-w-36">
            <SelectArea
                :model-value="props.query.floor ?? null"
                @update:model-value="updateFloor"
                :items="item"
                defaultValue="category.all_floor"
                valueKey="slug"
            />
        </div>
    </div>
</template>
