<script setup lang="ts">
import gsap from 'gsap';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps<{
    length: number;
    modelValue: number;
}>();

const emit = defineEmits<{
    (e: 'update:modelValue', value: number): void;
}>();

// Refs
const dotsWrapper = ref<HTMLElement | null>(null);
const select = ref<HTMLElement | null>(null);
let lastPos = 0;

function moveSelect(index: number) {
    if (!dotsWrapper.value || !select.value) return;

    const listItems = dotsWrapper.value.querySelectorAll<HTMLElement>('.dot');
    const dot = listItems[index];
    if (!dot) return;

    // Move blob
    const dest = dot.offsetLeft + dot.offsetWidth / 2 - select.value.offsetWidth / 2;
    gsap.to(select.value, {
        x: dest,
        duration: 0.6,
        ease: 'back.out(1.7)',
    });

    // Dynamic positioning logic
    const container = dotsWrapper.value.parentElement;
    if (container) {
        const containerWidth = container.offsetWidth;
        const dotCenter = dot.offsetLeft + dot.offsetWidth / 2;
        const totalDotsWidth = dotsWrapper.value.scrollWidth;

        let scrollTo = 0;

        if (index <= 2) {
            // ✅ Start alignment
            scrollTo = 0;
        } else if (index >= props.length - 2) {
            // ✅ End alignment
            scrollTo = totalDotsWidth - containerWidth;
        } else {
            // ✅ Center alignment
            const containerCenter = containerWidth / 2;
            scrollTo = dotCenter - containerCenter;
        }

        // Animate scroll
        gsap.to(dotsWrapper.value, {
            x: -scrollTo,
            duration: 0.6,
            ease: 'power3.out',
        });
    }
}

function updateScale() {
    if (!select.value) return;
    const pos = gsap.getProperty(select.value, 'x') as number;
    const speed = Math.abs(pos - lastPos);

    gsap.to(select.value, {
        scaleX: 1 + speed * 0.5,
        scaleY: 1,
        duration: 0.1,
        onComplete: () => {
            gsap.to(select.value, {
                scaleX: 1,
                duration: 0.6,
                ease: 'elastic.out(1, 0.5)',
            });
        },
    });

    lastPos = pos;
    requestAnimationFrame(updateScale);
}

// ✅ Only shrink edges if not adjacent
const edgeScale = computed(() => {
    const minScale = 0.5;

    const isFarFromStart = props.modelValue > 1; // not first or adjacent
    const isFarFromEnd = props.modelValue < props.length - 2; // not last or adjacent

    return {
        startScale: isFarFromStart ? minScale : 1,
        endScale: isFarFromEnd ? minScale : 1,
    };
});

watch(
    () => props.modelValue,
    (val) => moveSelect(val),
);

onMounted(() => {
    moveSelect(props.modelValue);
    requestAnimationFrame(updateScale);
});
</script>

<template>
    <div class="dots-container">
        <ul class="dots" ref="dotsWrapper">
            <li class="select" ref="select"></li>
            <li
                v-for="index in props.length"
                :key="index"
                class="dot"
                :class="{ active: index - 1 === props.modelValue }"
                @click="emit('update:modelValue', index - 1)"
                :style="{
                    transform: index === 1 ? `scale(${edgeScale.startScale})` : index === props.length ? `scale(${edgeScale.endScale})` : 'scale(1)',
                }"
            ></li>
        </ul>
    </div>
</template>

<style scoped>
.dots-container {
    width: 85px;
    height: 20px;
    overflow: hidden;
    position: relative;
    display: flex;
    justify-items: center;
    padding: 5px 0px 5px 0px;
}

.dots {
    list-style: none;
    display: flex;
    justify-items: flex-start;
    align-items: center;
    position: relative;
    will-change: transform;
    padding: 0px 8px 0px 8px;
}

.dot {
    display: inline-block;
    border-radius: 50%;
    width: 8px;
    height: 8px;
    margin: 0 5px;
    position: relative;
    z-index: 2;
    cursor: pointer;
    background: #fff;
    transition: transform 0.3s ease;
}
.dot.active {
    opacity: 0.9;
    background: #fff;
}

.select {
    border-radius: 50%;
    width: 12px;
    height: 12px;
    background: white;
    position: absolute;
    top: 50%;
    left: 0;
    transform: translateY(-50%);
    z-index: 5;
    pointer-events: none;
}
</style>
