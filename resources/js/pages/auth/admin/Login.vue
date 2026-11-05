<script setup lang="ts">
import { LoginForm, LoginIllustration } from '@/components/shared/login';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <div class="flex h-screen flex-col lg:flex-row">
        <!-- Left Side -->
        <LoginIllustration />

        <!-- Right Side -->
        <LoginForm admin="true" forgotPassword="true" />
    </div>
</template>
