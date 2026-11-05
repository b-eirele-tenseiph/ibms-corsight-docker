<script setup lang="ts">
import { StateEmpty } from '@/components/shared/states';
import { PromoCarousel } from '@/components/user/promo';
import { UserLayout, UserPageLayout } from '@/layouts/user';
import { UserPromoProps } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const user = computed(() => (usePage().props.auth as any).user);

defineProps<{
    promos: Array<UserPromoProps>;
}>();
</script>

<template>
    <Head :title="$t('head.promo')" />
    <UserLayout :title="user?.room_name">
        <UserPageLayout>
            <PromoCarousel v-if="promos.length" :promo="promos" />
            <StateEmpty v-else class="mt-20" />
        </UserPageLayout>
    </UserLayout>
</template>
