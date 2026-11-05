<?php

namespace App\Services\RoomAccess;

use App\Models\Room;
use App\Models\SwitchBotLockAccess;
use App\Models\User;
use App\Services\SwitchBotDevice\SwitchBotLock\SwitchBotLockAccessService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RoomAccessService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // createRoomAccess        (1.0) Create a new room access record
    //                               for the user.
    // updateRoomAccess        (2.0) Update the user's existing room
    //                               access record.
    // deleteRoomAccess        (3.0) Delete the user's associated
    //                               room access record.
    // accesses                (4.0) Retrieve collection of  rooms with
    //                               their room accesses and users.
    // loadRoom                (5.0) Load room and attach its status and
    //                               room accesses.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> __construct
     * <Function> Delegate Services across controller
     *
     * @param \App\Services\SwitchBotDevice\SwitchBotLock\SwitchBotLockAccessService $switchbotLockService
     *
     */
    public function __construct(
        private SwitchBotLockAccessService $switchbotLockService,
    ) {}

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> createRoomAccess
     * <Function> Create a new room access record for the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $user
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    public function createRoomAccess(array $roomAccessData, User $user, ?array $switchBotLockAccessData = null)
    {
        $room = Room::where('id', $roomAccessData['room_id'])->first();

        if ($room->hasSwitchBotKeypadTouch && $switchBotLockAccessData) {
            //  create access person with switchBot lock access to switchBot keypad
            $this->switchbotLockService->createSwitchBotLockAccess($user, $room->switchBotKeypadTouch, $switchBotLockAccessData);
        }

        // Create Room Access
        $user->roomAccess()->create($roomAccessData);
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> updateRoomAccess
     * <Function> Update the room access record for the given user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\User         $user
     * @return void
     */
    public function updateRoomAccess(array $roomAccessData, User $user, ?array $switchBotLockAccessData = null)
    {
        $room = Room::where('id', $roomAccessData['room_id'])->first();

        // SwitchBot lock
        if ($room->hasSwitchBotKeypadTouch && $user->hasSwitchBotLockAccess) {
            // If room has switchbot keypad and user has switchBot lock access
            $this->switchbotLockService->updateSwitchBotLockAccess(
                $user,
                $room->switchBotKeypadTouch,
                $switchBotLockAccessData
            );
        } else if ($room->hasSwitchBotKeypadTouch && !$user->hasSwitchBotLockAccess) {
            // If room has switchbot keypad and user does not have switchBot lock access
            $this->switchbotLockService->createSwitchBotLockAccess(
                $user,
                $room->switchBotKeypadTouch,
                $roomAccessData
            );
        } else if (!$room->hasSwitchBotKeypadTouch && $user->hasSwitchBotLockAccess) {
            // If room does not have switchbot keypad and user has switchBot lock access
            $switchbotLockServices = new SwitchBotLockAccessService;
            $switchbotLockServices->deleteSwitchBotLockAccess($user);
        }

        $user->update([
            'password' => Hash::make($user->password_displayable),
            'password_displayable' => $user->password_displayable
        ]);

        // If roomAccessData is present on the request, update room access
        if ($roomAccessData) {
            $user->roomAccess()->update(
                $roomAccessData
            );
        }
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> deleteRoomAccess
     * <Function> Delete the room access record associated with the user.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function deleteRoomAccess(User $user)
    {
        $sbLockAccess = SwitchBotLockAccess::where('user_id', $user->id)->first();
        if (isset($sbLockAccess)) {

            $sbLockAccess->update(['status' => 'for deletion']);
        };

        // Delete Room Access
        $user->roomAccess()->delete();
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> accesses
     * <Function> Retrieve collection of  rooms with their room accesses and
     *            users.
     *
     * @param  \Illuminate\Http\Request                 $request
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function accesses(Request $request): LengthAwarePaginator
    {
        $date = $request->query('date', now(config('timezone.default'))->toDateString());
        $search = $request->query('search');

        $rooms = Room::query()
            ->orderByRaw('CAST(number AS UNSIGNED)')
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('number', 'like', "%{$search}%");
                });
            })
            ->with([
                'roomAccesses' => function ($query) use ($date) {
                    $query
                        ->where(function ($query) use ($date) {
                            $query
                                ->where(function ($query) use ($date) {
                                    $query->whereDate('start_date', '<=', $date)
                                        ->whereDate('end_date', '>=', $date);
                                })
                                ->orWhere('is_permanent', true);
                        })
                        ->whereRelation('user', function ($query) {})
                        ->with('switchBotLockAccess');
                }
            ])
            ->paginate(24);

        $rooms->map(function (Room $room) use ($date) {
            $room['status'] = $room->getStatus($date, $room->roomAccesses);
            return $room;
        });

        $request->query->set('date', $date);

        return $rooms;
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> loadRoom
     * <Function> Load room and attach its status and room accesses.
     *
     * @param  \App\Models\Room $room
     * @param  array            $filters
     * @return \App\Models\Room
     */
    public function loadRoom(Room $room, array $filters)
    {
        $date = $filters['date'] ?? now();

        $room['status'] = $room->getStatus($date, $room->roomAccesses);

        $room->load('roomAccesses');

        return $room;
    }
}
