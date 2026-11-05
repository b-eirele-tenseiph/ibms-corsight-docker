<script setup lang="ts">
import { InputDateAndDate, InputSearch } from '@/components/shared/inputs';
import { SelectArea } from '@/components/shared/selects';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuContent, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { CorsightPersonDevice, QueryParams } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps<{
    query: QueryParams;
    devices: CorsightPersonDevice[];
}>();

const { t } = useI18n();

const filters = ref(props.query);

let timeout: any = null;

const search = computed({
    get: () => filters.value.search ?? '',
    set: (newValue: string) => {
        if (newValue) {
            filters.value.search = newValue;
        } else {
            filters.value.search = undefined;
        }
        filters.value.page = 1;
    },
});

const load = () => {
    clearTimeout(timeout);
    timeout = setTimeout(() => router.reload({ data: filters.value }), 500);
};

const reload = () => {
    filters.value.page = 1;
    router.reload({ data: filters.value });
};

const roleOptions = computed(() => [
    { id: 'entry_point', name: t('page.corsight_person.role.entry_point') },
    { id: 'exit_point', name: t('page.corsight_person.role.exit_point') },
    { id: 'general', name: t('page.corsight_person.role.general') },
    { id: 'passage', name: t('page.corsight_person.role.passage') },
]);

const updateDevice = (value: string | null) => {
    filters.value.device_id = value || undefined;
    reload();
};

const updateRole = (value: string | null) => {
    filters.value.role = value || undefined;
    reload();
};

const updateDate = (key: 'date_from' | 'date_to', value: string) => {
    filters.value[key] = value || undefined;
    reload();
};

const toggleFlag = (key: 'security_only' | 'in_now') => {
    filters.value[key] = filters.value[key] ? undefined : '1';
    reload();
};

// Number of active dropdown filters (shown as a badge on the trigger)
const activeFilterCount = computed(() => {
    let count = 0;
    if (filters.value.device_id) count++;
    if (filters.value.role) count++;
    if (filters.value.date_from || filters.value.date_to) count++;
    return count;
});

const resetFilters = () => {
    filters.value.device_id = undefined;
    filters.value.role = undefined;
    filters.value.date_from = undefined;
    filters.value.date_to = undefined;
    reload();
};
</script>

<template>
    <div class="flex w-full gap-2">
        <!-- Search Input -->
        <div class="max-w-50">
            <InputSearch v-model="search" @update:model-value="load" />
        </div>

        <!-- Filter Dropdown -->
        <DropdownMenu>
            <DropdownMenuTrigger>
                <Button variant="outline" class="flex items-center gap-2">
                    <Icon icon="heroicons-outline:filter" width="16" height="16" />
                    <span class="fluid-text-xs-sm">{{ $t('operation.filter') }}</span>
                    <Badge
                        v-if="activeFilterCount > 0"
                        variant="secondary"
                        class="bg-primary text-primary-foreground fluid-text-xxs-xs size-4.5 items-center justify-center rounded-full"
                    >
                        {{ activeFilterCount }}
                    </Badge>
                </Button>
            </DropdownMenuTrigger>

            <DropdownMenuContent align="start" class="w-80 space-y-4 p-4">
                <!-- Camera Filter -->
                <div>
                    <label class="fluid-text-xxs-xs mb-1 block">{{ $t('table.header.camera') }}</label>
                    <SelectArea
                        :items="devices"
                        :model-value="filters.device_id ?? null"
                        valueKey="id"
                        itemKey="name"
                        placeholder="form.placeholder.camera"
                        default-value="page.corsight_person.filter.all_camera"
                        @update:model-value="updateDevice"
                    />
                </div>

                <!-- Camera Role Filter -->
                <div>
                    <label class="fluid-text-xxs-xs mb-1 block">{{ $t('form.label.role') }}</label>
                    <SelectArea
                        :items="roleOptions"
                        :model-value="filters.role ?? null"
                        valueKey="id"
                        itemKey="name"
                        placeholder="form.placeholder.camera_role"
                        default-value="page.corsight_person.filter.all_role"
                        @update:model-value="updateRole"
                    />
                </div>

                <!-- Date Range Filter -->
                <InputDateAndDate
                    label="form.label.date_range"
                    :date_first="filters.date_from ?? ''"
                    :date_second="filters.date_to ?? ''"
                    :allow-past-date="true"
                    @update:date_first="updateDate('date_from', $event)"
                    @update:date_second="updateDate('date_to', $event)"
                />

                <!-- Reset -->
                <div v-if="activeFilterCount > 0" class="flex justify-end border-t pt-2">
                    <Button variant="link" class="fluid-text-xs-sm text-primary-link-text cursor-pointer px-1" @click="resetFilters">
                        {{ $t('action.reset') }}
                    </Button>
                </div>
            </DropdownMenuContent>
        </DropdownMenu>

        <!-- Quick Filter Toggles -->
        <div class="ml-auto flex gap-2">
            <Button
                variant="outline"
                class="fluid-text-xs-sm flex items-center gap-2"
                :class="filters.security_only ? 'bg-primary text-white' : ''"
                @click="toggleFlag('security_only')"
            >
                <Icon icon="heroicons-outline:shield-exclamation" width="16" height="16" />
                {{ $t('page.corsight_person.filter.security_only') }}
            </Button>
            <Button
                variant="outline"
                class="fluid-text-xs-sm flex items-center gap-2"
                :class="filters.in_now ? 'bg-primary text-white' : ''"
                @click="toggleFlag('in_now')"
            >
                <Icon icon="heroicons-outline:user" width="16" height="16" />
                {{ $t('page.corsight_person.filter.in_now') }}
            </Button>
        </div>
    </div>
</template>
