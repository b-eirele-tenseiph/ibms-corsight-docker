<script setup lang="ts">
import { UserSidebarLayout } from '@/layouts/user';
import { PageTitle } from '@/types';

defineProps<PageTitle>();
</script>

<template>
    <div class="h-screen overflow-x-hidden overflow-y-hidden">
        <UserSidebarLayout :title="title">
            <slot />
        </UserSidebarLayout>
    </div>
</template>
