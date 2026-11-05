<script setup lang="ts">
import { Progress } from '@/components/ui/progress';
import gsap from 'gsap';
import { ref } from 'vue';

const props = withDefaults(
    defineProps<{
        showPercentage?: boolean;
    }>(),
    {
        showPercentage: false,
    },
);

// Animated progress value (reactive)
const progressValue = ref(0);
let progressTimeline: gsap.core.Timeline | null = null;

// --- Animate progress through stages ---
function startFakeProgress() {
    if (progressTimeline) progressTimeline.kill();

    progressTimeline = gsap.timeline();
    progressTimeline
        .to(progressValue, {
            value: 30,
            duration: 0.8,
            ease: 'power2.out',
        })
        .to(progressValue, {
            value: 50,
            duration: 0.8,
            ease: 'power2.out',
        })
        .to(progressValue, {
            value: 70,
            duration: 0.8,
            ease: 'power2.out',
        })
        .to(progressValue, {
            value: 90,
            duration: 0.8,
            ease: 'power2.out',
            onComplete: () => {
                progressTimeline?.pause();
            },
        });

    progressTimeline.play();
}

// --- Animate to 100% then reset ---
function finishProgress() {
    if (progressTimeline) {
        progressTimeline.kill();
        progressTimeline = null;
    }

    gsap.to(progressValue, {
        value: 100,
        duration: 0.6,
        ease: 'power2.out',
        onComplete: () => {
            setTimeout(() => {
                progressValue.value = 0;
            }, 800);
        },
    });
}

// Expose functions to parent
defineExpose({
    startFakeProgress,
    finishProgress,
});
</script>

<template>
    <div v-show="progressValue > 0" class="flex items-center gap-2">
        <Progress :modelValue="progressValue" class="h-1.5 p-0" />
        <span v-show="props.showPercentage" class="text-primary text-sm font-semibold"> {{ Math.round(progressValue) }}% </span>
    </div>
</template>
