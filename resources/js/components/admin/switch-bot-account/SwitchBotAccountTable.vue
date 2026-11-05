<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { PaginatedResponse, QueryParams, SwitchBotAccountData } from '@/types';
import { router } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, ref } from 'vue';

const props = defineProps<{
    account: PaginatedResponse<SwitchBotAccountData>;
    filters: QueryParams;
}>();

const filters = ref({ ...props.filters });

const { isExtraSmallMobile } = useBreakpoints();

const load = () => {
    router.reload({ data: filters.value });
};

const tableData = computed(() => {
    const isSmallScreen = useMediaQuery('(max-width: 1024px)');
    return props.account.data.length > 0 || isSmallScreen.value ? props.account.data : [{}];
});
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(acc, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="h-12 py-3 lg:w-[15%]">{{ $t('table.header.num') }}</TableHead>
                            <SortableTableHead class="lg:w-[20%]" column="room_name" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.room_name') }}
                            </SortableTableHead>
                            <SortableTableHead class="lg:w-[20%]" column="email" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.email') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3 lg:w-[15%]">{{ $t('table.header.auth_cred') }}</TableHead>
                            <SortableTableHead class="lg:w-[18%]" column="last_synced_at" v-model:filters="filters" @update:filters="load()">
                                {{ $t('table.header.last_synced_at') }}
                            </SortableTableHead>
                            <TableHead class="h-12 lg:w-[12%]" />
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="account.data.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(acc, index) in account.data" :key="index">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 truncate py-3"> {{ (account.current_page - 1) * account.per_page + index + 1 }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ acc.room?.name || '-' }}</TableCell>
                            <TableCell class="h-12 truncate py-3">{{ acc.email }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Button size="xs" class="fluid-text-xs-sm h-full cursor-pointer rounded-full px-3" @click="$emit('credentials', acc)">
                                    {{ $t('action.show') }}
                                </Button>
                            </TableCell>
                            <TableCell class="h-12 truncate py-3">{{ acc.last_synced_at || '-' }}</TableCell>
                            <TableCell class="h-12">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.update_account' },
                                        delete: { label: 'modal.action.delete_account' },
                                    }"
                                    @update="$emit('update', acc)"
                                    @delete="$emit('delete', acc)"
                                />
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
                <TableBody v-else>
                    <TableEmpty :colspan="6">
                        <StateEmpty />
                    </TableEmpty>
                </TableBody>
            </Table>
        </ScrollArea>
    </div>
</template>
