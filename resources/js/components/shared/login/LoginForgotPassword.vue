<script setup lang="ts">
import {
    AlertDialog,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Icon } from '@iconify/vue';
import { ref } from 'vue';

const open = ref(false);
</script>

<template>
    <AlertDialog v-model:open="open">
        <AlertDialogTrigger as-child>
            <button class="text-primary-link-text text-shadow-primary-link-text fluid-text-xs-sm cursor-pointer font-semibold hover:underline">
                {{ $t('layout.login.forgot_password') }}
            </button>
        </AlertDialogTrigger>

        <AlertDialogContent @interact-outside="open = false">
            <AlertDialogHeader>
                <div class="flex justify-center">
                    <Icon icon="heroicons:information-circle-20-solid" class="text-primary-link-text h-8 w-8 lg:h-13 lg:w-13" />
                </div>
                <div class="mb-2 flex justify-center">
                    <AlertDialogTitle class="fluid-text-xs-base"> {{ $t('layout.login.forgot_password') }}</AlertDialogTitle>
                </div>
                <AlertDialogDescription class="fluid-text-xs-sm text-center">
                    {{ $t('layout.login.contact_administrator') }}
                </AlertDialogDescription>
            </AlertDialogHeader>
        </AlertDialogContent>
    </AlertDialog>
</template>
