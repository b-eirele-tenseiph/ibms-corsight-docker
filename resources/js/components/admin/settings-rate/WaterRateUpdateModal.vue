<script setup lang="ts">
import { MonthPicker } from '@/components/shared/date-picker';
import { InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import { ModalForm } from '@/components/shared/modals';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { update } from '@/routes/admin/management/settings/rates/water';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

const props = defineProps<{
    show: boolean;
    rate: any;
    consumer_types: Array<any>;
}>();

const emit = defineEmits(['close']);

const defaultTier = () => ({
    min_cubic_meters: '',
    max_cubic_meters: '',
    rate_per_cubic_meter: '',
});

const getFormData = (rate: any) => ({
    consumer_type: rate.consumer_type,
    meter_size: rate.meter_size,
    effective_date: rate.effective_date,
    tiers: rate.tiers?.length > 0 ? rate.tiers.map((t: any) => ({ ...t })) : [defaultTier()],
    notes: rate.notes,
});

const form = useForm(getFormData(props.rate));

watch(
    () => props.rate,
    (newRate) => {
        if (!newRate) return;
        form.defaults(getFormData(newRate)).reset();
    },
    { deep: true },
);

const onSubmit = () => {
    form.put(update(props.rate.id).url, {
        onSuccess: () => emit('close'),
        preserveState: true,
    });
};

const addTier = () => form.tiers.push(defaultTier());
const removeTier = (index: number) => form.tiers.length > 1 && form.tiers.splice(index, 1);

const resetForm = () => {
    emit('close');
    form.clearErrors();
};
</script>

<template>
    <ModalForm
        :open="show"
        title="modal.action.update_rate"
        icon="mdi:water-outline"
        confirm-label="action.update"
        variant="default"
        @submit="onSubmit"
        @close="resetForm"
    >
        <form @submit.prevent="onSubmit" class="space-y-6">
            <InputSelectWithLabel
                v-model="form.consumer_type"
                label="form.label.consumer_type"
                placeholder="form.placeholder.consumer_type"
                valueKey="name"
                :items="consumer_types"
                disabled
            />

            <InputWithLabel v-model="form.meter_size" label="form.label.meter_size" placeholder='e.g., 5/8" (15mm)' disabled />

            <div class="space-y-2">
                <Label class="text-sm font-medium">{{ $t('form.label.effective_date') }}</Label>
                <MonthPicker v-model="form.effective_date" disabled />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label class="text-sm font-medium">{{ $t('form.label.consumption_rates') }}</Label>
                    <Button type="button" variant="secondary" size="xs" @click="addTier">
                        <Icon icon="lucide:plus" class="size-4" />
                    </Button>
                </div>

                <Table class="table-main table-fixed border">
                    <TableHeader class="table-header fluid-text-xs-sm">
                        <TableRow class="bg-table-header-background table-header-row">
                            <TableHead class="h-12">{{ $t('table.header.min') }}</TableHead>
                            <TableHead class="h-12">{{ $t('table.header.max') }}</TableHead>
                            <TableHead class="h-12">{{ $t('table.header.charges') }}</TableHead>
                            <TableHead class="h-12 w-12"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody class="table-body fluid-text-xs-sm">
                        <template v-for="(tier, index) in form.tiers" :key="index">
                            <TableRow class="[&_td]:p-0 [&_td_input]:border-0 [&_td_input]:focus-visible:ring-0 [&_td_input]:dark:bg-transparent">
                                <TableCell>
                                    <Input v-model="tier.min_cubic_meters" type="number" placeholder="0" min="0" step="1" class="h-10 border-0" />
                                </TableCell>
                                <TableCell>
                                    <Input v-model="tier.max_cubic_meters" type="number" placeholder="10" min="0" step="1" class="h-10 border-0" />
                                </TableCell>
                                <TableCell>
                                    <Input
                                        v-model="tier.rate_per_cubic_meter"
                                        type="number"
                                        placeholder="250"
                                        min="0"
                                        step="1"
                                        class="h-10 border-0"
                                    />
                                </TableCell>
                                <TableCell>
                                    <Button
                                        v-if="form.tiers.length > 1"
                                        type="button"
                                        variant="ghost"
                                        size="icon"
                                        @click="removeTier(index)"
                                        class="text-secondary-text hover:text-destructive size-8"
                                    >
                                        <Icon icon="lucide:trash-2" width="24" height="24" />
                                    </Button>
                                </TableCell>
                            </TableRow>
                        </template>
                    </TableBody>
                </Table>

                <template v-for="(error, key) in form.errors" :key="key">
                    <div class="text-destructive fluid-text-xs-sm" v-if="key.includes('tiers.')">{{ error }}</div>
                </template>
            </div>

            <!-- Notes -->
            <InputTextAreaWithLabel v-model="form.notes" label="form.label.notes" placeholder="form.placeholder.notes" :error="form.errors.notes" />
        </form>
    </ModalForm>
</template>
