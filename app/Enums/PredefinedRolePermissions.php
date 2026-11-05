<?php

namespace App\Enums;

enum AdminPermissions: string
{
    // manage
    case ManageRoomMonitoring = 'room monitoring';
    case ManageElectricMonitoring = 'electric monitoring';
    case ManageClimateAnalytics = 'climate analytics';

    case ManageMonitoringControls = 'monitoring controls';

    case ManageRooms = 'manage rooms';
    case ManageRemoteLock = 'manage remotelock';
    case ManageLorawan = 'manage lorawan';
    case ManageSwitchBot = 'manage switchbot';
    case ManageCorsight = 'manage corsight';
    case ManageLogs  = 'manage logs';

    case ManageAdminAccounts = 'manage admin accounts';
    case ManageUserAccounts = 'manage user accounts';

    case ManageRoles = "manage roles";
    case ManageFiles = "manage files";
    case ManageRates = "manage rates";
    case ManageScenes  = 'manage scenes';

    case ManageRoomAccess = 'manage room access';
    case ManageAmenities = 'manage amenities';
    case ManagePromos = 'manage promos';

    case ManageOrders = 'manage orders';

    case ControlDevices = 'control devices';
}

enum WebPermissions: string
{
    // control
    case ControlDevices = 'control devices';

        // access
    case AccessPromos = 'access promos';
    case AccessRestaurant = 'access restaurant';
    case AccessAmenities = 'access amenities';
}

class PredefinedRolePermissions
{
    // Super Admin
    public static function superAdmin(): array
    {
        return [
            "name" => "super admin",
            "guard" => "admin",
            "permissions" => array_map(fn($permission) => $permission->value, AdminPermissions::cases()),
        ];
    }

    // Admin
    public static function admin(): array
    {
        return [
            "name" => "admin",
            "guard" => "admin",
            "permissions" => [
                AdminPermissions::ManageElectricMonitoring->value,
                AdminPermissions::ManageRoomMonitoring->value,
                AdminPermissions::ManageClimateAnalytics->value,
                AdminPermissions::ManageMonitoringControls->value,
                AdminPermissions::ManageRooms->value,
                AdminPermissions::ManageRemoteLock->value,
                AdminPermissions::ManageLorawan->value,
                AdminPermissions::ManageSwitchBot->value,
                AdminPermissions::ManageCorsight->value,
                AdminPermissions::ManageLogs->value,
                AdminPermissions::ManageFiles->value,
                AdminPermissions::ManageRates->value,
                AdminPermissions::ManageUserAccounts->value,
                AdminPermissions::ManageScenes->value,
            ],
        ];
    }

    // Staff Permissions
    public static function staff(): array
    {
        return [
            "name" => "staff",
            "guard" => "admin",
            "permissions" => [
                AdminPermissions::ManageRoomAccess->value,
                AdminPermissions::ManageMonitoringControls->value,
                AdminPermissions::ControlDevices->value,
                AdminPermissions::ManageLogs->value,
                AdminPermissions::ManagePromos->value,
                AdminPermissions::ManageAmenities->value
            ],
        ];
    }


    // Crew Permissions
    public static function crew(): array
    {
        return [
            "name" => "crew",
            "guard" => "admin",
            "permissions" => [
                AdminPermissions::ManageOrders->value,
            ],
        ];
    }

    // User Permissions
    public static function user(): array
    {
        return [
            "name" => "user",
            "guard" => "web",
            "permissions" => [
                WebPermissions::ControlDevices->value,
                WebPermissions::AccessAmenities->value,
                WebPermissions::AccessPromos->value,
                WebPermissions::AccessRestaurant->value
            ],
        ];
    }
}
