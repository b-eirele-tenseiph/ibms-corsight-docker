<script setup lang="ts">
import { SelectArea } from '@/components/shared/selects';
import { index } from '@/routes/admin/electric-monitoring';
import { ElectricMeterProps } from '@/types';
import { router } from '@inertiajs/vue3';
import { useDebounce } from '@vueuse/core';
import { ref, watch } from 'vue';

const props = defineProps<ElectricMeterProps>();

const device = ref((props.filters as any)?.device ?? props.electric_meter[0]?.id);

const debounceddevice = useDebounce(device, 500);
watch(debounceddevice, (val) => {
    router.get(
        index.url(),
        {
            device: val === 'all' ? null : val,
        },
        {
            preserveState: false,
            replace: true,
        },
    );
});
</script>
<template>
    <div>
        <div class="flex h-full items-center">
            <div class="xs:flex-row xs:gap-y-2 xs:gap-x-2 flex h-auto w-full flex-col gap-y-2 md:gap-x-3">
                <SelectArea
                    v-model="device"
                    placeholder="filter.select_devices"
                    :items="electric_meter"
                    class="dark:bg-input/0 dark:hover:bg-input/0 border-none px-0 shadow-none focus-visible:ring-0"
                />
            </div>
        </div>
    </div>
</template>
