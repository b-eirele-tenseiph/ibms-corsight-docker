<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import type { User } from '@/types';
import { Icon } from '@iconify/vue';

const props = withDefaults(
    defineProps<{
        user: User;
        showEmail?: boolean;
        showRole?: boolean;
    }>(),
    {
        showRole: false,
    },
);
</script>

<template>
    <div class="fluid-text-xs-sm grid flex-1 gap-1 text-left leading-tight">
        <div class="flex flex-row items-center gap-2">
            <span class="truncate font-bold">{{ user.name }}</span>
            <Icon icon="lucide:chevron-down" class="xs:block hidden size-3" />
        </div>
        <span v-if="props.showEmail" class="text-secondary-text truncate text-xs">{{ user.email }}</span>
        <Badge v-if="props.showRole && user.role" class="rounded-full text-(length:--text-xxs) capitalize">
            {{ $te(`layout.role.${user.role}`) ? $t(`layout.role.${user.role}`) : user.role }}
        </Badge>
    </div>
</template>
