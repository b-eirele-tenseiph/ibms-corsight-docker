<script setup lang="ts">
import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { index } from '@/routes/user/dashboard';
import { QueryParams, RoomArea, SharedData } from '@/types';
import { Icon } from '@iconify/vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useMediaQuery } from '@vueuse/core';
import { computed, inject, ref } from 'vue';

const props = defineProps<{
    area: Array<RoomArea>;
    filters: QueryParams;
}>();

const currentArea = computed(() => props.filters.area ?? 'all-area');
const isRedirecting = ref(false);

const beforeRedirect = () => {
    isRedirecting.value = true;
};

const generateAreaLink = (area: string) => {
    const room = usePage<SharedData>().props.auth.user.room_number;
    return area === 'all-area' ? index({ room }).url : index({ room }, { query: { area } }).url;
};

const onAreaChange = (value: any) => {
    beforeRedirect();
    router.visit(generateAreaLink(value));
};

const isTablet = useMediaQuery('(min-width: 768px)');
const setting = inject('gridSetting') as { column: any; drag: boolean };
</script>

<template>
    <div class="flex shrink-0 flex-row items-center gap-4 px-2">
        <ScrollArea v-if="isTablet" class="flex w-0 grow">
            <div class="flex w-max gap-4 pb-3">
                <Link
                    @click="beforeRedirect"
                    :href="generateAreaLink('all-area')"
                    :class="['font-medium text-nowrap', currentArea === 'all-area' ? 'text-primary font-semibold' : 'text-primary-text']"
                    >{{ $t('page.user_dashboard.filter.all_area') }}
                </Link>
                <Link
                    v-for="room_area in area"
                    :key="room_area.id"
                    @click="beforeRedirect()"
                    :href="generateAreaLink(room_area.slug ?? '')"
                    :class="['font-medium text-nowrap', currentArea === room_area.slug ? 'text-primary font-semibold' : 'text-primary-text']"
                    >{{ room_area.name }}</Link
                >
                <ScrollBar orientation="horizontal" />
            </div>
        </ScrollArea>
        <div v-else class="flex-1">
            <Select v-model="currentArea" @update:modelValue="onAreaChange">
                <SelectTrigger class="focus-visible:border-ring focus-visible:ring-ring/20 w-48 cursor-pointer focus-visible:ring">
                    <SelectValue :value="currentArea" class="fluid-text-xs-sm" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup class="h-64">
                        <SelectItem value="all-area" :selected="currentArea === 'all-area'" class="fluid-text-xs-sm">
                            {{ $t('page.user_dashboard.filter.all_area') }}
                        </SelectItem>
                        <SelectItem
                            class="fluid-text-xs-sm"
                            v-for="room_area in area"
                            :key="room_area.id"
                            :value="room_area.slug ?? ''"
                            :selected="currentArea === room_area.slug"
                        >
                            {{ room_area.name }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>
        <Icon :icon="setting.drag ? 'lucide:save' : 'lucide:move'" class="size-6 cursor-pointer" @click="$emit('enableEdit')" />
    </div>
</template>
