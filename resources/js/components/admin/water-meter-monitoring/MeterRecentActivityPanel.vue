<script setup lang="ts">
import { SpinnerIcon } from '@/components/shared/icons/spinner-icon';
import { StateEmpty } from '@/components/shared/states';
import { Card } from '@/components/ui/card';
import { ScrollArea } from '@/components/ui/scroll-area';
import { recentActivity as routeRecentActivity } from '@/routes/admin/dashboard';
import { index } from '@/routes/admin/management/logs';
import { RecentActivity } from '@/types';
import { Icon } from '@iconify/vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';
import { onMounted, ref } from 'vue';

dayjs.extend(relativeTime);

const recentActivity = ref<Array<RecentActivity>>([]);
const isLoading = ref<boolean>(true);

// Load device data from backend
const loadData = () => {
    isLoading.value = true;

    axios
        .get(routeRecentActivity.url())
        .then((response) => {
            recentActivity.value = response.data.recent_activity;
        })
        .finally(() => {
            isLoading.value = false;
        });
};

onMounted(() => {
    loadData();
});
</script>

<template>
    <Card class="h-full gap-6 py-4">
        <div class="flex w-full flex-row items-center justify-between px-4">
            <span class="fluid-text-sm-base text-primary-text font-bold">{{ $t('page.admin_dashboard.header.activity') }}</span>
            <Link :href="index.url()" class="text-primary fluid-text-xs-sm font-medium hover:underline hover:underline-offset-2">{{
                $t('filter.view_all')
            }}</Link>
        </div>

        <template v-if="isLoading">
            <div class="flex h-full items-center justify-center">
                <SpinnerIcon />
            </div>
        </template>
        <template v-else>
            <div v-if="recentActivity?.length > 0" class="h-full overflow-hidden pr-2 pl-4">
                <ScrollArea class="h-full pr-4">
                    <div v-for="(log, index) in recentActivity" :key="index" class="flex items-center gap-3 border-b py-2">
                        <Icon
                            :icon="log.icon"
                            class="size-5 shrink-0"
                            :class="{
                                'text-error': log.type.toLowerCase() === 'error',
                                'text-warning': log.type.toLowerCase() === 'warning',
                                'text-info': log.type.toLowerCase() === 'information',
                            }"
                        />

                        <div class="flex flex-col gap-1">
                            <span class="fluid-text-xs-sm font-medium">
                                {{ log.description }}
                            </span>
                            <span class="text-secondary-text text-xs font-medium">
                                {{ dayjs(log.last_seen).format('MMM D, YYYY h:mm A') }}
                            </span>
                        </div>
                    </div>
                </ScrollArea>
            </div>
            <div v-else class="py-10">
                <StateEmpty />
            </div>
        </template>
    </Card>
</template>
