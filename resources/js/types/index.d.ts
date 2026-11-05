import type { PageProps } from '@inertiajs/core';

export interface Auth {
    user: User;
}

export interface PageTitle {
    title: string;
}

export interface FlashMessage {
    type: 'success' | 'error' | 'warning' | 'info';
    body: string;
}

export interface ImportSummary {
    processed: number;
    successful: number;
    failed: number;
    errors: string[];
}

export interface NavItem {
    title?: string;
    href?: string;
    icon?: string;
    isActive?: boolean;
    menu?: MenuItem[];
    params?: Record<string, any>;
    target?: string;
    fallback?: string;
    routeMatch?: string;
    application?: string;
}

export interface SharedData extends PageProps {
    name: string;
    auth: Auth;
    impersonation: {
        active: boolean;
        role: string | null;
    };
    flash: Record<any>;
    importSummary: Record<any>;
}

export interface User {
    id: number;
    name: string;
    role: string;
    email: string;
    guard_name?: string;
    room_number: number;
    permissions?: Array<string>;
    roles?: Array<string>;
    room_id?: string;
    room_name?: string;
    username?: string;
    role?: {
        name: string;
    };
    external_apps: Array<string>;
}

// Exporting types related to component
export * from './shared';
export * from './t-notification';
export * from './t-paginated-response';
export * from './t-room-access';
export * from './t-room-area';

// Exporting types related to admin pages
export * from './t-admin';
export * from './t-admin-dashboard';
export * from './t-amenity';
export * from './t-amenity-category';
export * from './t-amenity-photo';
export * from './t-climate-analytics';
export * from './t-climate-monitoring';
export * from './t-electric-monitoring';
export * from './t-electric-monitoring-report';
export * from './t-food';
export * from './t-food-category';
export * from './t-lorawan-devices';
export * from './t-lorawan-gateway';
export * from './t-promo';
export * from './t-restaurant-food-order-history';
export * from './t-role';
export * from './t-room';
export * from './t-room-monitoring';
export * from './t-scene';
export * from './t-corsight-account';
export * from './t-corsight-device';
export * from './t-corsight-person';
export * from './t-switch-bot-account';
export * from './t-switch-bot-device';
export * from './t-switch-bot-hub';
export * from './t-switch-bot-remote';
export * from './t-water-meter-monitoring';

// Exporting types related to monitoring
export * from './t-user';

// Exporting types related to user pages
export * from './t-user-dashboard';
