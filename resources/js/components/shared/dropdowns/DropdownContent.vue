<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DropdownMenu, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Icon } from '@iconify/vue';

withDefaults(
    defineProps<{
        buttonVariant?: 'ghost' | 'link' | 'default' | 'destructive' | 'outline' | 'secondary';
        buttonName?: string;
        icon?: string;
        align?: 'start' | 'center' | 'end';
    }>(),
    {
        buttonVariant: 'outline',
        icon: 'lucide:ellipsis-vertical',
    },
);
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button :variant="buttonVariant" class="cursor-pointer">
                <span v-if="buttonName" class="hidden md:flex">{{ $t(buttonName) }}</span>
                <Icon :icon="icon" />
            </Button>
        </DropdownMenuTrigger>

        <slot name="content" />
    </DropdownMenu>
</template>
