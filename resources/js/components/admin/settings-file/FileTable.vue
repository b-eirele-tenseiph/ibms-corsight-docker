<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import Badge from '@/components/ui/badge/Badge.vue';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { FileProps } from '@/types/t-general-settings';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { computed, ref } from 'vue';

dayjs.extend(relativeTime);

const props = defineProps<FileProps>();

const filters = ref({ ...props.filters });

const { isExtraSmallMobile } = useBreakpoints();

const load = () => {
    router.reload({ data: filters.value });
};

const fileConfig: Record<string, { icon: string; color: string }> = {
    pdf: { icon: 'bi:filetype-pdf', color: 'bg-error-bg text-error-text' },
    excel: { icon: 'bi:filetype-xlsx', color: 'bg-success-bg text-success-text' },
    csv: { icon: 'bi:filetype-csv', color: 'bg-success-bg text-success-text' },
    word: { icon: 'bi:filetype-docx', color: 'bg-info-bg text-info-text' },
};

const getFileConfig = (type: string) =>
    fileConfig[type] || { icon: 'bi:file-earmark', color: 'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-100' };

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.files.data.length > 0 || isSmallScreen.value ? props.files.data : [{}];
});

const iconVisibility = useMediaQuery('(min-width: 360px)');
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(file, index) in tableData" :key="index">
                        <TableRow class="table-header-row">
                            <SortableTableHead
                                class="h-14 w-full truncate py-3 lg:w-[30%]"
                                column="name"
                                v-model:filters="filters"
                                @update:filters="load()"
                            >
                                {{ $t('table.header.document') }}
                            </SortableTableHead>
                            <SortableTableHead
                                class="h-14 w-full truncate py-3 lg:w-[10%]"
                                column="size"
                                v-model:filters="filters"
                                @update:filters="load()"
                            >
                                {{ $t('table.header.size') }}
                            </SortableTableHead>
                            <SortableTableHead
                                class="h-14 w-full truncate py-3 lg:w-[18%]"
                                column="admin_id"
                                v-model:filters="filters"
                                @update:filters="load()"
                            >
                                {{ $t('table.header.generated_by') }}
                            </SortableTableHead>
                            <SortableTableHead
                                class="h-14 w-full truncate py-3 lg:w-[12%]"
                                column="created_at"
                                v-model:filters="filters"
                                @update:filters="load()"
                            >
                                {{ $t('table.header.last_updated') }}
                            </SortableTableHead>
                            <TableHead v-if="isArchive" class="h-14 w-full py-3 lg:w-[10%]">{{ $t('table.header.days_left') }}</TableHead>
                            <TableHead class="h-14 w-full py-3 lg:w-[10%]"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>
                <TableBody v-if="files.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(file, index) in files.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="h-14 truncate py-3">
                                <div class="flex h-full items-center gap-3">
                                    <div v-if="iconVisibility" :class="['flex-shrink-0 rounded-lg p-2', getFileConfig(file.extension).color]">
                                        <Icon :icon="getFileConfig(file.extension).icon" class="size-4 lg:size-5" />
                                    </div>
                                    <div class="line-clamp-2 font-medium text-wrap">{{ file.name }}</div>
                                </div>
                            </TableCell>
                            <TableCell class="h-14 truncate py-3 font-mono font-semibold">{{ file.size }}</TableCell>
                            <TableCell class="h-14 truncate py-3">{{ file.admin?.name ?? '—' }}</TableCell>
                            <TableCell class="h-14 truncate py-3">{{ dayjs(file.created_at).fromNow() }}</TableCell>
                            <TableCell v-if="isArchive" class="h-14 py-3">
                                <Badge :class="[file.daysLeft <= 10 ? 'bg-error-bg text-error-text' : 'bg-warning-bg text-warning-text']"
                                    >{{ file.days_left }} {{ $t('page.file_settings.label.days') }}</Badge
                                >
                            </TableCell>

                            <TableCell class="h-14 py-3">
                                <DropdownAction
                                    :actions="
                                        isArchive
                                            ? {
                                                  restore: { label: 'modal.action.restore_file' },
                                                  forceDelete: {
                                                      label: 'modal.action.delete_permanently',
                                                      class: 'text-destructive hover:!text-destructive',
                                                  },
                                              }
                                            : {
                                                  download: { label: 'modal.action.download_file' },
                                                  delete: { label: 'modal.action.delete_file' },
                                              }
                                    "
                                    @download="$emit('download', file.path)"
                                    @delete="$emit('delete', file)"
                                    @restore="$emit('restore', file)"
                                    @forceDelete="$emit('forceDelete', file)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="isArchive ? 6 : 5">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
