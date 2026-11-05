<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { ref } from 'vue';

const props = defineProps<{
    open: boolean;
    orderId: number | string;
    loading?: boolean;
}>();

const emit = defineEmits(['update:open', 'confirm']);

const reason = ref('');

const handleClose = () => {
    reason.value = '';
    emit('update:open', false);
};

const handleConfirm = () => {
    if (!reason.value.trim() || props.loading) return;
    emit('confirm', reason.value);
};
</script>

<template>
    <Dialog :open="open" @update:open="handleClose">
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle class="fluid-text-sm-base">{{ $t('page.crew_dashboard.label.order_number') }}{{ orderId }}</DialogTitle>
                <DialogDescription class="fluid-text-xxs-xs">
                    {{ $t('page.crew_dashboard.label.description') }}
                </DialogDescription>
            </DialogHeader>

            <div class="fluid-text-xs-sm grid gap-2 py-4">
                <Label for="reason" class="text-xs font-semibold">{{ $t('page.crew_dashboard.label.reason_for_cancellation') }}</Label>
                <Textarea
                    id="reason"
                    v-model="reason"
                    placeholder="e.g. Changed my mind, ordered the wrong item..."
                    class="fluid-text-xs-sm min-h-[100px] resize-none"
                />
            </div>

            <DialogFooter>
                <Button variant="outline" @click="handleClose" class="fluid-text-xs-sm">
                    {{ $t('action.close') }}
                </Button>
                <Button variant="destructive" @click="handleConfirm" class="fluid-text-xs-sm">
                    {{ $t('action.confirm_cancellation') }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
