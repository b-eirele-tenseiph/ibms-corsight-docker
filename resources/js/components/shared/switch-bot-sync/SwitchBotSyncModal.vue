<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { DialogTrigger } from '@/components/ui/dialog';
import { Icon } from '@iconify/vue';
import { DialogRoot, type DialogRootEmits, type DialogRootProps, useForwardPropsEmits } from 'reka-ui';
import SwitchBotScanContent from './SwitchBotSyncContent.vue';
const props = defineProps<
    DialogRootProps & {
        loading: boolean;
        applying?: boolean;
        header: string;
        title: string;
        description: string;
        isError?: boolean;
        failedAccounts: any[];
    }
>();
const emits = defineEmits<DialogRootEmits & { scanDevices: []; applyChanges: [] }>();
const forwarded = useForwardPropsEmits(props, emits);
</script>

<template>
    <DialogRoot data-slot="dialog" v-bind="forwarded">
        <DialogTrigger class="flex">
            <Button @click="$emit('scanDevices')" :disabled="props.loading" class="cursor-pointer">
                <Icon icon="heroicons-outline:refresh" class="size-4" />
                <span class="hidden md:block">{{ $t('action.sync') }}</span>
            </Button>
        </DialogTrigger>
        <SwitchBotScanContent :header :title :description @applyChanges="$emit('applyChanges')" :loading :applying :isError :failedAccounts>
            <slot />
        </SwitchBotScanContent>
    </DialogRoot>
</template>
