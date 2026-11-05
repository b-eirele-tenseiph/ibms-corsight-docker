export { default as AnimatedIcon } from './AnimatedIcon.vue';

import { cva } from 'class-variance-authority';

export const animatedIconVariants = {
    container: cva('hidden xxs:block motion-safe:animate-bounce-slow !animation-delay-[8000ms] absolute top-[0%] left-[0%] z-10 -translate-y-1/2'),
    ping: cva('absolute inline-flex h-full w-16 animate-ping rounded-full bg-primary opacity-50 dark:bg-white'),
    icon: cva('transform rounded-full size-16 bg-primary p-3 text-white transition-transform duration-300 hover:scale-125'),
};
