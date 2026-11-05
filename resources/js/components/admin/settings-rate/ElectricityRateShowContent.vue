<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { destroy, update } from '@/routes/admin/management/settings/rates/electricity';
import { RateHistory } from '@/types/t-general-settings';
import { Icon } from '@iconify/vue';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';
import { useI18n } from 'vue-i18n';

defineProps<{
    rateHistory: Array<RateHistory>;
}>();

const { t } = useI18n();

const editForm = useForm({
    id: '',
    rate_per_kwh: '',
});

const startEdit = (rate: { id: string; rate_per_kwh: number }) => {
    editForm.id = rate.id;
    editForm.rate_per_kwh = rate.rate_per_kwh.toString();
};

const clearErrors = () => editForm.resetAndClearErrors();

const updateRate = () => {
    editForm.put(update({ rate: Number(editForm.id) }).url, {
        preserveState: true,
        onSuccess: clearErrors,
    });
};

const deleteRate = (id: any) => {
    editForm.delete(destroy({ rate: id }).url, {
        preserveState: true,
        onSuccess: clearErrors,
    });
};
</script>

<template>
    <div class="space-y-2">
        <div v-for="rate in rateHistory" :key="rate.id" class="hover:bg-accent/30 rounded-md border p-3 transition-colors">
            <div class="flex items-start justify-between gap-4">
                <div class="justify flex-1">
                    <div class="mb-1 flex items-center gap-2">
                        <Label class="fluid-text-xs-sm text-primary-text font-semibold">
                            {{ dayjs(rate.effective_date).format('MMMM YYYY') }}
                        </Label>
                        <Badge v-if="rate.latest" variant="success">{{ $t('label.latest') }}</Badge>
                    </div>

                    <template v-if="editForm.id !== rate.id">
                        <p class="fluid-text-xs-sm text-secondary-text">₱{{ Number(rate.rate_per_kwh).toFixed(4) }} {{ $t('unit.per_kwh') }}</p>
                    </template>

                    <template v-else>
                        <div class="mt-2">
                            <Input
                                class="fluid-text-xs-sm focus-visible:border-ring focus-visible:ring-ring/20 placeholder:text-secondary-muted-text shadow-none focus-visible:ring"
                                v-model="editForm.rate_per_kwh"
                                type="number"
                                step="0.01"
                                :placeholder="t('form.placeholder.rate_per_kwh')"
                                :error="editForm.errors.rate_per_kwh"
                            />
                        </div>
                    </template>
                </div>

                <div class="flex shrink-0 items-center">
                    <template v-if="editForm.id !== rate.id">
                        <Button variant="ghost" class="text-secondary-text size-8" @click="startEdit(rate)">
                            <Icon icon="lucide:edit-2" />
                        </Button>
                        <Button variant="ghost" class="text-secondary-text hover:text-destructive size-8" @click="deleteRate(rate.id)">
                            <Icon icon="lucide:trash-2" />
                        </Button>
                    </template>

                    <template v-else>
                        <Button variant="ghost" size="icon" class="text-success size-8" :disabled="editForm.processing" @click="updateRate">
                            <Icon icon="lucide:check" />
                        </Button>
                        <Button variant="ghost" size="icon" class="size-8" :disabled="editForm.processing" @click="clearErrors">
                            <Icon icon="lucide:x" />
                        </Button>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
