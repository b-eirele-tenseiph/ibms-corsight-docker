<script setup lang="ts">
import { MonthPicker } from '@/components/shared/date-picker';
import { InputWithLabel } from '@/components/shared/inputs';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { store } from '@/routes/admin/management/settings/rates/electricity';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { reactive, watch } from 'vue';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    'update:show': [value: boolean];
}>();

const addRate = reactive({
    form: useForm({
        effective_date: '',
        rate_per_kwh: '',
    }),
    onSubmit: () => {
        addRate.form.post(store.url(), {
            onSuccess: () => {
                emit('update:show', false);
                addRate.form.resetAndClearErrors();
            },
        });
    },
});

watch(
    () => props.show,
    (val) => !val && addRate.form.clearErrors(),
);
</script>

<template>
    <Popover :open="show" @update:open="emit('update:show', $event)">
        <PopoverTrigger as-child>
            <Button size="sm">
                <Icon icon="lucide:plus" class="size-4" />
                <span class="hidden md:flex">{{ $t('action.add_rate') }}</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="z-50 w-80" align="end" :side-offset="5">
            <div class="space-y-4">
                <div class="space-y-2">
                    <Label class="fluid-text-xs-sm text-primary-text font-semibold">{{ $t('modal.action.add_rate') }}</Label>
                    <p class="text-secondary-text text-xs">{{ $t('modal.description.rate_description') }}</p>
                </div>

                <div class="space-y-3">
                    <div class="grid w-full space-y-2">
                        <Label class="fluid-text-xs-sm">{{ $t('form.label.month') }}</Label>
                        <MonthPicker v-model="addRate.form.effective_date" />
                        <div v-show="addRate.form.errors.effective_date">
                            <p class="text-destructive fluid-text-xs-sm">{{ addRate.form.errors.effective_date }}</p>
                        </div>
                    </div>

                    <InputWithLabel
                        label="form.label.rate_per_kwh"
                        v-model="addRate.form.rate_per_kwh"
                        type="number"
                        step="0.01"
                        min="0"
                        placeholder="0.12"
                        :error="addRate.form.errors.rate_per_kwh"
                    />

                    <div class="flex items-center gap-2 pt-2">
                        <Button size="sm" class="flex-1" @click="addRate.onSubmit()"> {{ $t('action.create') }} </Button>
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
