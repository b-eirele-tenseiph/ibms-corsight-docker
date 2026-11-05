import type Echo from 'laravel-echo';

declare global {
    interface Window {
        Echo: Echo;
    }
}

declare module '@vue/runtime-core' {
    interface ComponentCustomProperties {
        route: typeof route;
        $t: Composer['t'];
        $te: Composer['te'];
        is: (value: string) => boolean;
        can: (value: string) => boolean;
    }
}

// Extend ImportMeta interface for Vite...
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

// Allow importing YAML files
declare module '*.yaml' {
    const content: { [key: string]: string };
    export default content;
}
