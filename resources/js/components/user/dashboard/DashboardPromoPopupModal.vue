<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogOverlay } from '@/components/ui/dialog';
import { hidePromo } from '@/routes/user/dashboard';
import { Promo } from '@/types';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { PromoCarousel } from '.';

const props = defineProps<{
    open: boolean;
    promos: Promo[];
}>();

const form = useForm({
    hide_promo_popup: true,
});

const submit = () => {
    form.post(hidePromo.url(), {
        onSuccess: () => {
            emit('close');
        },
    });
};

const emit = defineEmits(['close']);
</script>

<template>
    <Dialog :open="open" @update:open="emit('close')">
        <DialogOverlay class="fixed inset-0 z-50 bg-black/40" />
        <DialogContent class="bg-opacity-0 border-0 p-0 shadow-none focus:ring-0 focus:outline-none sm:max-w-[600px] sm:p-6">
            <button
                @click="$emit('close')"
                class="absolute top-4 right-4 z-50 flex h-6 w-6 items-center justify-center rounded-full bg-black/5 text-white hover:cursor-pointer hover:bg-black/30 sm:top-8 sm:right-8 md:h-9.5 md:w-9.5"
            >
                <Icon icon="lucide:x" />
            </button>

            <div class="flex flex-col gap-y-2 sm:gap-y-8">
                <PromoCarousel :promos="props.promos" />

                <form @submit.prevent="submit" class="flex flex-col gap-y-4">
                    <div class="flex justify-center">
                        <Button type="submit" variant="link" class="hover:text-primary-foreground text-white underline hover:bg-transparent">
                            {{ $t('action.never_show_again') }}
                        </Button>
                    </div>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>
