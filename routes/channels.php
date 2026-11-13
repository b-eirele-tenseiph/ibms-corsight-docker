<?php

use App\Models\Room;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

/**
 * User (Guest/Employee/Room)
 */

// Private channel for room (user)
Broadcast::channel('room.{room}', function ($user, Room $room) {
    return $user->room->id === $room->id;
}, ['guards' => ['web']]);

// Channel for fetching user notification
Broadcast::channel('user.notification.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
}, ['guards' => ['web']]);


/**
 * Admin
 */
Broadcast::channel('admin.notification.{id}', function ($user, $id) {
    return (string) $user->id === (string) $id;
}, ['guards' => ['web', 'admin']]);

// Private notification channel for electric report
Broadcast::channel('electric_report_{job}', function () {
    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for lorawan device data
Broadcast::channel('admin.lorawan_device', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for climate data analysis
Broadcast::channel('admin.climate_data', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for switch bot device data
Broadcast::channel('admin.switch_bot_device', function ($user) {
    return true;
}, ['guards' => ['admin']]);


// Private channel for admin for switch bot lock data
Broadcast::channel('admin.switch_bot_lock_access', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for switch bot remote data
Broadcast::channel('admin.switch_bot_remote', function ($user) {

    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for electric meter data
Broadcast::channel('admin.electric_meter', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Private channel for fetching energy_consumption data
Broadcast::channel('energy_consumption', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
    ];
}, ['guards' => ['admin']]);

// Private channel for admin for updating room people count
Broadcast::channel('admin.room_people_count', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Private channel for admin for room monitoring
Broadcast::channel('admin.room.active', function ($user) {
    return true;
}, ['guards' => ['admin']]);

// Everyone logged in via 'web' (and optionally 'admin') can listen
Broadcast::channel('amenities', fn($user) => true, ['guards' => ['web']]);

// Private notification channel for users (guard: web)
Broadcast::channel('user.{id}', function ($user, $id) {
    return (string) $user->getKey() === (string) $id;
}, ['guards' => ['web']]);

// Channel for ordering function
Broadcast::channel('food-orders', function ($user) {
    // Check if user's role is a crew.
    return $user->hasRole('crew');
}, ['guards' => ['admin']]);

// Channel for updating order status for user
Broadcast::channel('order-status-updated', function ($user) {
    return true;
}, ['guards' => ['web']]);

// Private channel for admin for visitor counter
Broadcast::channel('admin.booth_visitor', function ($user) {
    return true;
}, ['guards' => ['admin']]);
