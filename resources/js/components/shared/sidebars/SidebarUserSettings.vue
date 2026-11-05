<script setup lang="ts">
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Label } from '@/components/ui/label';
import { useLocalization } from '@/composables/useLocalization';
import { Icon } from '@iconify/vue';

const { currentLocale, setLocale } = useLocalization();

const languages = [
    { value: 'en', label: 'English', flag: 'circle-flags:us' },
    { value: 'ja', label: '日本語', flag: 'circle-flags:jp' },
];
</script>

<template>
    <div class="mt-auto flex justify-center py-2">
        <DropdownMenu>
            <DropdownMenuTrigger class="hover:bg-accent cursor-pointer rounded-md px-3 py-2">
                <Icon icon="lucide:languages" class="text-secondary-text size-4" />
            </DropdownMenuTrigger>

            <DropdownMenuContent align="start" class="w-36">
                <DropdownMenuLabel class="fluid-text-xs-sm">{{ $t('form.label.language') }}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                <DropdownMenuItem v-for="lang in languages" :key="lang.value" class="flex items-center gap-2" @click="setLocale(lang.value)">
                    <Icon :icon="lang.flag" class="size-4" />
                    <Label class="fluid-text-xs-sm flex-1">{{ lang.label }}</Label>
                    <Icon v-if="currentLocale === lang.value" icon="stash:check-duotone" class="size-5" />
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    </div>
</template>
