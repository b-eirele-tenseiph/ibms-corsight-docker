<script setup lang="ts">
import { ForbiddenIcon, InternalServerErrorIcon, NotFoundIcon } from '@/components/icons/errors';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const props = defineProps({ status: Number, message: String });
const { t } = useI18n();

const icon = computed(() => {
    const icons = {
        403: {
            component: ForbiddenIcon,
            class: 'h-[156px] w-auto tp-sm:h-[250px] tp-md:h-[400px] tp-lg-xl:h-[450px] tp-2xl:h-[540px] tp-3xl:h-[648px]',
        },
        404: {
            component: NotFoundIcon,
            class: 'h-[156px] w-auto tp-sm:h-[250px] tp-md:h-[400px] tp-lg-xl:h-[450px] tp-2xl:h-[540px] tp-3xl:h-[648px]',
        },
        500: {
            component: InternalServerErrorIcon,
            class: 'h-[150px] w-auto tp-sm-md:h-[200px] tp-md:h-[250px] tp-md-lg:h-[300px] tp-lg:h-[250px] tp-lg-xl:h-[300px] tp-2xl:h-[450px]',
        },
        502: {
            component: InternalServerErrorIcon,
            class: 'h-[150px] w-auto tp-sm-md:h-[200px] tp-md:h-[250px] tp-md-lg:h-[300px] tp-lg:h-[250px] tp-lg-xl:h-[300px] tp-2xl:h-[450px]',
        },
        503: {
            component: InternalServerErrorIcon,
            class: 'h-[150px] w-auto tp-sm-md:h-[200px] tp-md:h-[250px] tp-md-lg:h-[300px] tp-lg:h-[250px] tp-lg-xl:h-[300px] tp-2xl:h-[450px]',
        },
    };
    return icons[props.status] || icons[403];
});

const description = computed(() => {
    return (
        {
            403: t('general.error_page.error_403'),
            404: t('general.error_page.error_404'),
            500: t('general.error_page.error_500'),
            502: t('general.error_page.error_502'),
            503: t('general.error_page.error_503'),
        }[props.status] || props.message
    );
});
</script>

<template>
    <main class="tp-lg:flex-row flex h-screen flex-col items-center justify-center gap-6 p-6">
        <component :is="icon.component" :class="icon.class" />

        <div
            :class="[
                'flex flex-col items-center gap-6 text-center text-sm',
                'tp-lg:items-start tp-lg:pt-6 lg:text-left',
                'tp-md:text-base',
                icon.class,
            ]"
        >
            <label class="tp-md-lg:gap-4 flex max-w-md flex-col gap-2">
                <h1 class="tp-md:text-[64px] tp-lg:text-[72px] tp-xl:text-[80px] text-center text-5xl font-semibold text-[#434343]">
                    {{ props.status }}
                </h1>
                <p class="text-[#818181]">{{ description }}</p>
            </label>

            <a href="/" class="bg-tp-office-yellow mt-6 rounded px-4 py-2 text-white">{{ $t('general.error_page.action_button') }}</a>
        </div>
    </main>
</template>
