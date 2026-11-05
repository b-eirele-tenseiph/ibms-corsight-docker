<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SortableTableHead } from '@/components/shared/sortables';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useBreakpoints } from '@/composables/useBreakpoints';
import { getRoleBadgeClass } from '@/lib/badge-colors';
import { index } from '@/routes/admin/management/accounts/admins';
import { Admin } from '@/types/t-admin';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { AdminSwitch } from '.';

const props = defineProps<{
    admins: Admin[];
    queryParams?: object;
}>();

const { isExtraSmallMobile, isMobile, isSmallMobile, isTablet } = useBreakpoints();

const filters = ref({ ...props.queryParams });
const isSmallScreen = computed(() => isTablet.value || isMobile.value || isSmallMobile.value);

const tableData = computed(() => {
    // If data exists or it's a small screen (don't force a dummy)
    return props.admins.length > 0 || isSmallScreen.value ? props.admins : [{}];
});

function reloadTable() {
    router.get(index.url(), filters.value, {
        preserveScroll: true,
        replace: true,
    });
}
</script>

<template>
    <div class="h-full overflow-hidden">
        <ScrollArea class="h-full">
            <Table class="table-main table-fixed">
                <TableHeader v-if="!isExtraSmallMobile" class="table-header fluid-text-xs-sm">
                    <template v-for="(admin, index) in tableData" :key="index">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="h-12 py-3">{{ $t('table.header.num') }}</TableHead>
                            <SortableTableHead column="username" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.username') }}
                            </SortableTableHead>
                            <SortableTableHead column="name" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.name') }}
                            </SortableTableHead>
                            <SortableTableHead column="role" v-model:filters="filters" @update:filters="reloadTable">
                                {{ $t('table.header.role') }}
                            </SortableTableHead>
                            <TableHead class="h-12 py-3 text-center">{{ $t('table.header.activation') }}</TableHead>
                            <TableHead class="h-12 py-3"></TableHead>
                        </TableRow>
                    </template>
                </TableHeader>

                <TableBody v-if="props.admins.length > 0" class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0">
                    <template v-for="(admin, index) in props.admins" :key="admin.id">
                        <TableRow class="table-body-row">
                            <TableCell class="h-12 py-3">{{ index + 1 }}</TableCell>
                            <TableCell class="h-12 py-3">{{ admin.username }}</TableCell>
                            <TableCell class="h-12 py-3">{{ admin.name }}</TableCell>
                            <TableCell class="h-12 py-3">
                                <Badge v-if="admin.role_name" :class="['font-semibold', getRoleBadgeClass(admin.role_name)]">
                                    {{ admin.role_name }}
                                </Badge>
                            </TableCell>
                            <TableCell class="h-12 py-3 text-start lg:text-center"> <AdminSwitch :admin="admin" /></TableCell>
                            <TableCell class="h-12 py-2 lg:text-center">
                                <DropdownAction
                                    :actions="{
                                        update: { label: 'modal.action.update_admin' },
                                        delete: { label: 'modal.action.delete_admin' },
                                    }"
                                    @update="$emit('update', admin)"
                                    @delete="$emit('delete', admin)"
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
