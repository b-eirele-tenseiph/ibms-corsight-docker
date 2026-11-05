// interface from your original file
export type AppNotificationData = {
    nature: string;
    title: string;
    message: string;
    url?: string;
    room?: string;
    metadata?: {
        download_url?: string;
        [key: string]: any;
    };
    content?: string;
};

export type AppNotification = {
    id: string;
    type: string;
    data: AppNotificationData;
    read_at: string | null;
    created_at: string;
    checked?: boolean;
};

export interface AppNotificationItem {
    notif: AppNotification;
    isBulkSelecting: boolean;
    isPopoverOpen: boolean;
}

export interface AppNotificationToolbar {
    checkAllValue: boolean;
    isIndeterminate: boolean;
    anySelected: boolean;
}

// Your new types
// Payload carried by your AdminNotification
export interface EmergencyData {
    nature: string;
    title: string;
    message: string;
    url: string;
    room?: string;
}

export interface EmergencyBroadcastNotification {
    id: string;
    type: string;
    data: EmergencyData;
    read_at: string | null;
    created_at: string;
}

export type BroadcastNotification<T = unknown> = {
    id: string;
    type: string;
    notifiable_type?: string;
    notifiable_id?: string;
    data: T;
    read_at: string | null;
    created_at: string;
};
