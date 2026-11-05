import { useMediaQuery } from '@vueuse/core';
import { computed } from 'vue';

export function useBreakpoints() {
    const isTinyMobile = useMediaQuery('(max-width: 320px)');
    const isExtraSmallMobile = useMediaQuery('(max-width: 360px)');
    const isSmallMobile = useMediaQuery('(max-width: 479px)');
    const isMobile = useMediaQuery('(min-width: 480px) and (max-width: 639px)');
    const isTablet = useMediaQuery('(min-width: 640px) and (max-width: 1023px)');
    const isLaptop = useMediaQuery('(min-width: 1024px) and (max-width: 1335px)');
    const isDesktop = useMediaQuery('(min-width: 1336px)');

    const isMobileOrTablet = computed(() => isSmallMobile.value || isMobile.value || isTablet.value);
    const isMobileScreen = computed(() => isExtraSmallMobile.value || isSmallMobile.value || isMobile.value);

    return {
        isTinyMobile,
        isExtraSmallMobile,
        isSmallMobile,
        isMobile,
        isTablet,
        isLaptop,
        isDesktop,
        isMobileOrTablet,
        isMobileScreen,
    };
}
