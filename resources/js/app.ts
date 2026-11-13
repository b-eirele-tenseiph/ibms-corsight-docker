import '../css/app.css';

import { i18n } from '@/i18n';
import { createInertiaApp } from '@inertiajs/vue3';
import { configureEcho } from '@laravel/echo-vue';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { Toaster, toast } from 'vue-sonner';
import { initializeTheme } from './composables/useAppearance';
import EChartsPlugin from './lib/echarts';

const isHttps = window.location.protocol === 'https:';
const reverbHost = import.meta.env.VITE_REVERB_HOST || window.location.hostname;
const reverbPort = Number(import.meta.env.VITE_REVERB_PORT) || Number(window.location.port) || (isHttps ? 443 : 80);
const reverbTls = (import.meta.env.VITE_REVERB_SCHEME || window.location.protocol.replace(':', '')) === 'https';

configureEcho({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbTls,
    enabledTransports: ['ws', 'wss'],
});

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) => resolvePageComponent(`./pages/${name}.vue`, import.meta.glob<DefineComponent>('./pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .use(EChartsPlugin)
            .component('Toaster', Toaster)
            .provide('toast', toast)
            .mount(el);
    },
    defaults: {
        visitOptions: () => {
            return { viewTransition: true };
        },
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();

// Set user's timezone as a cookie if not already set
const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

if (!document.cookie.includes('timezone')) {
    document.cookie = `timezone=${timezone}; path=/`;
}
