<script setup lang="ts">
import { LoginForm, LoginIllustration } from '@/components/shared/login';
import { FlashMessage } from '@/types';

const props = defineProps<{
    status?: FlashMessage;
}>();
</script>

<template>
    <div class="flex h-screen flex-col lg:flex-row">
        <!-- Left Side -->
        <LoginIllustration />

        <!-- Right Side -->
        <LoginForm helpAndManual="true" :status="props.status" />
    </div>
</template>
