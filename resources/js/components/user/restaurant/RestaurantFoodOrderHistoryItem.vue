<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Stepper, StepperDescription, StepperItem, StepperSeparator, StepperTitle, StepperTrigger } from '@/components/ui/stepper';
import { cancel } from '@/routes/user/restaurant';
import { RestaurantFoodOrderHistory } from '@/types';
import { Icon } from '@iconify/vue';
import { router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { RestaurantOrderCancelModal } from '.';

const props = defineProps<{
    order: RestaurantFoodOrderHistory;
}>();

// Create a reactive variable for the order status
const orderStatus = ref(props.order.status);
const remarks = ref(props.order.remarks ?? '');

// Get active step based on order status
const getActiveStep = () => {
    if (orderStatus.value === 'new') return 1;
    if (orderStatus.value === 'on cook') return 2;
    if (orderStatus.value === 'completed') return 3;
    return 0; // Default to no active step if status is unknown
};
// Define the steps
const steps = computed(() => {
    const isCancelled = orderStatus.value === 'cancelled';

    return [
        {
            step: 1,
            title: isCancelled ? 'Cancelled' : 'Placed',
            active: getActiveStep() >= 1 || isCancelled,
            isCancelled: isCancelled,
        },
        {
            step: 2,
            title: 'Preparing',
            active: getActiveStep() >= 2 && !isCancelled,
        },
        {
            step: 3,
            title: 'Ready',
            active: getActiveStep() >= 3 && !isCancelled,
        },
    ];
});

const isNextStepActive = (step: number) => {
    const activeStep = getActiveStep();
    return activeStep === step + 1;
};

// watch prop change from the sheet parent

watch(
    () => props.order,
    (newOrder) => {
        orderStatus.value = newOrder.status;
        remarks.value = newOrder.remarks ?? '';
    },
    { deep: true, immediate: true },
);

const showCancelModal = ref(false);
const isProcessing = ref(false);
const handleCancelConfirm = (reason: string) => {
    isProcessing.value = true;

    router.post(
        cancel({ restaurant: props.order.id }),
        {
            _method: 'put',
            status: 'cancelled',
            remarks: reason,
        },
        {
            onSuccess: () => {
                showCancelModal.value = false;
                orderStatus.value = 'cancelled';
                remarks.value = reason;
            },
            onFinish: () => {
                isProcessing.value = false;
            },
        },
    );
};
</script>

<template>
    <div class="bg-background flex flex-col gap-y-4 rounded-xl p-4 dark:border">
        <div class="flex flex-col gap-3">
            <div class="flex items-start justify-between">
                <h1 class="fluid-text-xs-base font-semibold sm:font-bold">{{ $t('page.restaurant.label.order_number') }}{{ props.order.id }}</h1>
                <p class="fluid-text-xxs-sm text-primary-text">
                    {{ new Date(props.order.createdAt || props.order.created_at).toLocaleString() }}
                </p>
            </div>
            <Stepper class="flex w-full items-start gap-2 pt-2">
                <StepperItem
                    v-for="step in steps"
                    :key="step.step"
                    class="relative flex w-full flex-col items-center justify-center"
                    :step="step.step"
                >
                    <StepperSeparator
                        v-if="step.step !== steps[steps.length - 1].step"
                        :class="[isNextStepActive(step.step) || getActiveStep() >= step.step + 1 ? 'bg-primary' : 'bg-button-border']"
                        class="group-data-[state=completed]:bg-primary absolute top-5 right-[calc(-50%+10px)] left-[calc(50%+20px)] mx-3 block h-0.75 shrink-0 rounded-full"
                    />

                    <StepperTrigger as-child>
                        <Button
                            :variant="
                                step.isCancelled
                                    ? 'destructive'
                                    : getActiveStep() === step.step || isNextStepActive(step.step) || step.active
                                      ? 'default'
                                      : 'outline'
                            "
                            size="icon"
                            class="z-10 shrink-0 rounded-full"
                            :style="{ pointerEvents: getActiveStep() === step.step || isNextStepActive(step.step) || step.active ? 'auto' : 'none' }"
                        >
                            <template v-if="step.step === 1">
                                <Icon v-if="step.isCancelled" icon="lucide:circle-x" class="w-full" />
                                <Icon v-else icon="lucide:pointer" class="w-full" />
                            </template>

                            <Icon v-if="step.step === 2" icon="lucide:cooking-pot" class="w-full" />
                            <Icon v-if="step.step === 3" icon="lucide:concierge-bell" class="w-full" />
                        </Button>
                    </StepperTrigger>

                    <div class="mt-2 flex flex-col items-center text-center">
                        <StepperTitle
                            :class="[step.isCancelled ? 'text-error-text' : '', getActiveStep() === step.step && !step.isCancelled && 'text-primary']"
                            class="fluid-text-xxs-sm font-medium transition"
                        >
                            {{ step.title }}
                        </StepperTitle>
                        <StepperDescription
                            :class="[getActiveStep() === step.step && 'text-primary']"
                            class="text-secondary-text fluid-text-xxs-xs xs:not-sr-only sr-only font-normal transition"
                        >
                        </StepperDescription>
                    </div>
                </StepperItem>
            </Stepper>
        </div>

        <div class="flex flex-col gap-y-3">
            <div class="flex flex-col gap-y-2">
                <p class="fluid-text-xxs-sm font-semibold">{{ $t('page.restaurant.label.details') }}</p>
                <div v-for="item in props.order.details" :key="item.food.id" class="flex justify-between">
                    <p class="fluid-text-xxs-sm font-medium">
                        <span class="capitalize">{{ item.food.name }}</span> × {{ item.quantity }}
                    </p>
                    <p class="fluid-text-xxs-sm font-medium">
                        ₱
                        {{
                            (Number(item.food.price) * item.quantity).toLocaleString(undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2,
                            })
                        }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-y-2 border-t border-dashed pt-2">
                <div class="fluid-text-xxs-sm flex justify-between gap-x-1 font-bold">
                    <p>{{ $t('page.restaurant.label.total') }}</p>
                    <p>
                        ₱
                        {{
                            props.order.details
                                .reduce((sum, i) => sum + Number(i.food.price) * i.quantity, 0)
                                .toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                        }}
                    </p>
                </div>

                <div v-if="orderStatus === 'cancelled'" class="mt-1 flex flex-col gap-y-1">
                    <p class="text-error-text fluid-text-xxs-sm font-semibold">{{ $t('page.restaurant.label.reason_for_cancelling') }}:</p>
                    <p class="text-secondary-text fluid-text-xxs-sm italic">
                        {{ remarks || 'No reason provided' }}
                    </p>
                </div>

                <Button
                    v-if="orderStatus === 'new'"
                    class="text-destructive border-destructive hover:bg-destructive/10 fluid-text-xxs-sm h-7 w-fit px-2 font-medium transition-all hover:cursor-pointer"
                    variant="outline"
                    type="button"
                    @click="showCancelModal = true"
                >
                    {{ $t('action.cancel_order') }}
                </Button>
            </div>

            <RestaurantOrderCancelModal v-model:open="showCancelModal" :order-id="props.order.id" @confirm="handleCancelConfirm" />
        </div>
    </div>
</template>
