import { createI18n } from 'vue-i18n';
import en from '../lang/en.yaml';
import ja from '../lang/ja.yaml';

// Define available translation messages
const messages = { en, ja };

// Load saved locale from localStorage
const saved = (localStorage.getItem('locale') as keyof typeof messages) ?? 'en';

export const i18n = createI18n({
    legacy: false,
    locale: saved,
    fallbackLocale: 'en',
    messages,
    globalInjection: true,
});
