<script setup lang="ts">
import { MonthPicker } from '@/components/shared/date-picker';
import { InputSelectWithLabel, InputTextAreaWithLabel, InputWithLabel } from '@/components/shared/inputs';
import ModalForm from '@/components/shared/modals/ModalForm.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { store } from '@/routes/admin/management/settings/rates/water';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import { reactive } from 'vue';

defineProps<{
    consumer_types: Array<any>;
    show: boolean;
}>();

const emit = defineEmits(['close']);

const defaultTier = () => ({
    min_cubic_meters: '',
    max_cubic_meters: '',
    rate_per_cubic_meter: '',
});

const form = useForm({
    consumer_type: '',
    meter_size: '',
    tiers: [defaultTier()],
    effective_date: '',
    notes: '',
});

const addRate = reactive({
    onSubmit: () => {
        form.post(store.url(), {
            onSuccess: () => {
                emit('close');
                form.reset();
            },
        });
    },
});

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
        :title="'modal.action.add_rate'"
        icon="mdi:water-outline"
        :confirm-label="'action.create'"
        variant="default"
        @submit="addRate.onSubmit"
        @close="resetForm"
    >
        <form @submit.prevent="addRate.onSubmit" class="space-y-6">
            <!-- Consumer Type -->
            <InputSelectWithLabel
                v-model="form.consumer_type"
                label="form.label.consumer_type"
                placeholder="form.placeholder.consumer_type"
                valueKey="name"
                :items="consumer_types"
                :error="form.errors.consumer_type"
                required
            />

            <!-- Meter Size -->
            <InputWithLabel
                v-model="form.meter_size"
                type="text"
                label="form.label.meter_size"
                placeholder="form.placeholder.meter_size"
                min="0"
                :error="form.errors.meter_size"
            />

            <!-- Effective Date -->
            <div class="space-y-2">
                <Label class="text-sm font-medium">
                    {{ $t('form.label.effective_date') }}
                </Label>
                <MonthPicker v-model="form.effective_date" />
                <p v-if="form.errors.effective_date" class="text-destructive fluid-text-xs-sm">
                    {{ form.errors.effective_date }}
                </p>
            </div>

            <!-- Consumption & Rate Table -->
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <Label class="text-sm font-medium">
                        {{ $t('form.label.consumption_rates') }}
                    </Label>
                    <Button type="button" variant="secondary" size="xs" @click="addTier">
                        <Icon icon="lucide:plus" class="size-4" />
                    </Button>
                </div>

                <Table class="table-fixed border">
                    <TableHeader class="fluid-text-xs-sm">
                        <TableRow class="bg-table-header-background">
                            <TableHead class="h-12">{{ $t('table.header.min') }}</TableHead>
                            <TableHead class="h-12">{{ $t('table.header.max') }}</TableHead>
                            <TableHead class="h-12">{{ $t('table.header.charges') }}</TableHead>
                            <TableHead class="h-12 w-12"></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody class="fluid-text-xs-sm">
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
                                        variant="ghost"
                                        size="icon"
                                        @click="removeTier(index)"
                                        class="text-secondary-text hover:text-destructive size-8"
                                    >
                                        <Icon icon="lucide:trash-2" />
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
