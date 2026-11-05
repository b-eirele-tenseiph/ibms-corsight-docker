<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { destroy } from '@/routes/admin/management/impersonate';
import type { SharedData } from '@/types';
import { Icon } from '@iconify/vue';
import { router, usePage } from '@inertiajs/vue3';

const impersonation = usePage<SharedData>().props.impersonation;

const exit = () => {
    router.post(destroy.url(), {});
};
</script>

<template>
    <div
        v-if="impersonation.active"
        class="relative flex h-12 items-center justify-between overflow-hidden border-b bg-gradient-to-r from-amber-50 via-orange-50 to-amber-50 px-4 dark:from-amber-950/40 dark:via-orange-950/40 dark:to-amber-950/40"
    >
        <div class="flex items-center gap-3">
            <div class="flex h-6 w-6 items-center justify-center rounded-full bg-amber-500/10 dark:bg-amber-500/20">
                <Icon icon="lucide:eye" class="size-3.5 text-amber-600 dark:text-amber-400" />
            </div>

            <div class="flex items-center gap-2">
                <p class="fluid-text-xs-sm font-semibold tracking-wide text-amber-900 dark:text-amber-100">
                    {{ $t('layout.impersonation.viewing_as') }}
                </p>
                <Badge
                    class="border-none bg-amber-500/15 font-bold text-amber-700 uppercase ring-1 ring-amber-500/30 dark:bg-amber-400/10 dark:text-amber-300"
                >
                    {{ impersonation.role }}
                </Badge>
            </div>
        </div>

        <Button
            @click="exit"
            variant="ghost"
            class="fluid-text-xs-sm hover:text-destructive dark:hover:text-destructive flex items-center gap-2 font-semibold text-amber-800 hover:bg-transparent dark:text-amber-200 dark:hover:bg-transparent"
        >
            <Icon icon="lucide:log-out" class="size-3.5" />
            {{ $t('layout.impersonation.exit_session') }}
        </Button>
    </div>
</template>
