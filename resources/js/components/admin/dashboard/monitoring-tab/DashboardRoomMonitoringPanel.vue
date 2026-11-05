<script setup lang="ts">
import { DropdownAction } from '@/components/shared/dropdowns';
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Badge } from '@/components/ui/badge';
import { Card } from '@/components/ui/card';
import { DropdownMenuItem } from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Table, TableBody, TableCell, TableEmpty, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { useRooms } from '@/composables/useRooms';
import { devices } from '@/routes/admin/dashboard';
import { index as routeUserAccount } from '@/routes/admin/management/accounts/users';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted } from 'vue';
import { RoomMonitoringSearch } from '..';

const { roomData, loadRooms, isLoading, listenRoomEvents, stopRoomEvents } = useRooms();

onMounted(() => {
    loadRooms();
    listenRoomEvents();
});

onUnmounted(() => {
    stopRoomEvents();
});
</script>
<template>
    <Card class="h-full p-4">
        <div class="flex w-full flex-col items-center justify-between gap-2 sm:flex-row">
            <span class="fluid-text-sm-base text-primary-text w-full font-bold">{{ $t('page.admin_dashboard.header.room_monitoring') }}</span>
            <div class="flex w-full justify-end">
                <RoomMonitoringSearch />
            </div>
        </div>
        <div class="h-full overflow-hidden">
            <template v-if="isLoading">
                <div class="flex h-full items-center justify-center">
                    <SpinnerIcon />
                </div>
            </template>
            <template v-else>
                <ScrollArea class="h-full">
                    <Table class="flex-no-wrap flex w-full flex-row justify-center lg:inline-table">
                        <TableHeader class="table-header fluid-text-xs-sm lg:sticky lg:top-0">
                            <template v-for="(acc, i) in roomData" :key="i">
                                <TableRow class="bg-table-header-background table-header-row">
                                    <TableHead class="h-12 w-[12%] py-3"> {{ $t('table.header.room_name') }} </TableHead>
                                    <TableHead class="h-12 w-[12%] py-3"> {{ $t('table.header.floor_name') }} </TableHead>
                                    <TableHead class="h-12 w-[12%] py-3"> {{ $t('table.header.active_device') }} </TableHead>
                                    <TableHead class="h-12 w-[15%] py-3">{{ $t('table.header.status') }}</TableHead>
                                    <TableHead class="h-12 w-[12%] py-3">{{ $t('table.header.people_count') }}</TableHead>
                                    <TableHead class="h-12 w-[12%]" />
                                </TableRow>
                            </template>
                        </TableHeader>

                        <TableBody
                            v-if="roomData?.length > 0"
                            class="table-body fluid-text-xs-sm [&_tr:last-child]:border lg:[&_tr:last-child]:border-0"
                        >
                            <template v-for="room in roomData" :key="room.id">
                                <TableRow class="table-body-row rounded-l-none">
                                    <TableCell class="h-12 py-3">{{ room.name }}</TableCell>
                                    <TableCell class="h-12 py-3">{{ room.floor }}</TableCell>
                                    <TableCell class="h-12 py-3">
                                        {{ room.active }}
                                    </TableCell>
                                    <TableCell class="h-12 py-3">
                                        <div class="relative inline-block">
                                            <Badge
                                                :variant="room.isOccupied ? 'destructive' : 'success'"
                                                class="font-semibold"
                                            >
                                                {{ room.status }}
                                            </Badge>

                                            <span
                                                v-if="room.isOccupied"
                                                class="absolute -top-px -right-px flex size-2"
                                            >
                                                <span class="bg-destructive absolute h-full w-full animate-ping rounded-full opacity-75"></span>
                                                <span class="bg-destructive relative size-2 rounded-full"></span>
                                            </span>
                                        </div>
                                    </TableCell>

                                    <TableCell class="h-12 py-3">
                                        <div class="flex items-center gap-2">
                                            {{ room.count }}
                                            <Icon icon="ci:users" class="size-4"></Icon>
                                        </div>
                                    </TableCell>
                                    <TableCell class="h-12">
                                        <DropdownAction>
                                            <template #extension>
                                                <DropdownMenuItem>
                                                    <Link :href="routeUserAccount({ query: { room: room.slug } }).url">
                                                        {{ $t('filter.view_user') }}
                                                    </Link>
                                                </DropdownMenuItem>

                                                <DropdownMenuItem>
                                                    <Link :href="devices({ query: { room: room.id } }).url">
                                                        {{ $t('filter.view_device') }}
                                                    </Link>
                                                </DropdownMenuItem>
                                            </template>
                                        </DropdownAction>
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
            </template>
        </div>
    </Card>
</template>
