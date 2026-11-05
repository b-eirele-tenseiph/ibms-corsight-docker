import { switchLocale } from '@/routes/index';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

const STORAGE_KEY = 'locale';

export function useLocalization() {
    const { t, locale } = useI18n();

    // Get saved locale from localStorage
    const saved = localStorage.getItem(STORAGE_KEY) || 'en';
    // Set the initial locale from saved preference
    locale.value = saved;

    const currentLocale = ref(saved);

    // Updates the application locale
    function setLocale(lang: string) {
        currentLocale.value = lang;
        locale.value = lang;

        // Persist locale preference
        localStorage.setItem(STORAGE_KEY, lang);
        document.documentElement.setAttribute('lang', lang);

        router.visit(switchLocale({ locale: lang }).url, {
            preserveState: true,
            preserveScroll: true,
            method: 'get',
        });
    }

    return { t, currentLocale, setLocale };
}
