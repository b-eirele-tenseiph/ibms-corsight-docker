<script setup lang="ts">
import { ForgotPasswordDialog } from '@/components/shared/login';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { useAppearance } from '@/composables/useAppearance';
import { login as adminLogin } from '@/routes/admin';
import { login as userLogin } from '@/routes/index';
import { FlashMessage } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { AlertBox } from '../alert-box';
import { InputWithLabel } from '../inputs';

const props = defineProps<{
    status?: FlashMessage;
    forgotPassword?: string;
    helpAndManual?: string;
    admin?: string;
}>();

const form = useForm({
    username: '',
    password: '',
    remember: false,
    status: '',
});

const submit = () => {
    form.post(props.admin ? adminLogin.url() : userLogin.url(), { onFinish: () => form.reset('password') });
};

const { appearance } = useAppearance();
</script>
<template>
    <div class="xxs:items-start flex h-screen items-center justify-center lg:w-12/24 lg:items-center">
        <div class="m-8 w-full sm:mx-16 sm:mt-18 xl:p-18">
            <form @submit.prevent="submit" class="xs:mx-10 3xl:mx-18 flex flex-col gap-4 sm:mx-0 md:mx-10 lg:mx-0 lg:gap-8">
                <div class="flex lg:justify-center">
                    <img
                        :src="appearance === 'dark' ? '/images/Logo/iBMS-White.png' : '/images/Logo/iBMS-Black.png'"
                        alt="App Logo"
                        class="h-10 object-contain sm:h-14 lg:h-20"
                    />
                </div>

                <div class="space-y-2 lg:text-center">
                    <h1 class="text-primary-text fluid-text-lg-3xl font-bold">{{ $t('layout.login.title') }}</h1>
                </div>

                <div class="flex flex-col">
                    <!-- Error Message => No Room Access  -->
                    <AlertBox v-if="props.status" :type="props.status.type" :message="props.status.body" class="fluid-text-xs-sm mb-4" />

                    <!-- Error Message => Authentication Error -->
                    <AlertBox v-if="form.errors.status" type="error" :message="form.errors.status" class="fluid-text-xs-sm mb-4" />

                    <div class="mb-4">
                        <InputWithLabel
                            v-model="form.username"
                            placeholder="form.placeholder.username"
                            :error="form.errors.username"
                            :tabindex="1"
                            autofocus
                            :class="[
                                'e-10 fluid-text-xs-base placeholder:fluid-text-xs-base h-8 p-3.5 placeholder:font-semibold focus-visible:ring sm:h-10',
                                form.errors.username
                                    ? 'border-red-500 placeholder-red-400 focus-visible:border-red-500 focus-visible:ring-red-500/50'
                                    : 'placeholder:text-secondary-muted-text placeholder:fluid-text-xs-base focus-visible:border-primary focus-visible:ring-primary/50',
                            ]"
                        />
                    </div>

                    <div class="mb-2 w-full">
                        <InputWithLabel
                            type="password"
                            v-model="form.password"
                            placeholder="form.placeholder.password"
                            :error="form.errors.password"
                            :tabindex="2"
                            autocomplete="current-password"
                            autofocus
                            :class="[
                                'e-10 fluid-text-xs-base placeholder:fluid-text-xs-base h-8 p-3.5 placeholder:font-semibold focus-visible:ring sm:h-10',
                                form.errors.password
                                    ? 'border-red-500 placeholder-red-400 focus-visible:border-red-500 focus-visible:ring-red-500/50'
                                    : 'placeholder:text-secondary-muted-text placeholder:fluid-text-xs-base focus-visible:border-primary focus-visible:ring-primary/50',
                            ]"
                        />
                    </div>

                    <div class="flex justify-between">
                        <div class="flex items-center">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="remember" v-model="form.remember" :tabindex="3" />
                                <span class="fluid-text-xs-sm font-semibold">{{ $t('layout.login.remember_me') }}</span>
                            </div>
                        </div>

                        <div v-if="forgotPassword" class="flex items-center">
                            <ForgotPasswordDialog />
                        </div>
                    </div>
                </div>

                <div>
                    <Button type="submit" variant="default" class="w-full" :tabindex="4" :disabled="form.processing">
                        <Icon icon="lucide:loader-circle" v-if="form.processing" class="size-4 animate-spin" />
                        <span class="fluid-text-sm-base font-bold"> {{ $t('action.sign_in') }}</span>
                    </Button>

                    <div v-if="helpAndManual" class="mt-4 flex cursor-pointer justify-center gap-1">
                        <a
                            href="https://1drv.ms/b/c/563693e45b3d01c9/EckBPVvkkzYggFbSFgAAAAABoon4rW1p70-g0LtVLZyCBQ?e=Ar3FsO"
                            class="text-secondary-text fluid-text-xs-sm font-semibold"
                            :tabindex="5"
                        >
                            {{ $t('layout.login.help_manual') }}
                        </a>

                        <span class="flex items-center">
                            <Icon icon="ri:question-line" class="text-secondary-text size-3 lg:size-4.5" />
                        </span>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
