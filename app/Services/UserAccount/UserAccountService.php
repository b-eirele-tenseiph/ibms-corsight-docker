<?php

namespace App\Services\UserAccount;

use App\Models\User;
use App\Models\Room;
use App\Services\RoomAccess\RoomAccessService;
use Illuminate\Support\Facades\DB;
use App\Exports\BaseExport;
use App\Models\Activity;
use App\Traits\Notification\ShouldNotifyAdmin;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class UserAccountService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // $message                     (1.0) The message based on the given message
    //                                    id.
    // $activityLog                 (2.0) Activity log instance.
    // created                      (3.0) Fired after an User is created and 
    //                                    notifies users.
    // __construct                  (4.0) Inject RoomAccessService dependency.
    // getUsers                     (5.0) Retrieve paginated list of
    //                                    user accounts.
    // createUser                   (6.0) Create a new user account.
    // updateUser                   (7.0) Update an existing user account.
    // deleteUser                   (8.0) Delete a user account.
    // getRooms                     (9.0) Retrieve all room records.
    // exportUsers                 (10.0) Export all existing Users into an
    //                                    Excel file.
    // notification                (11.0) Create notification array data.
    // getRoom                     (12.0) Retrieve the user's assigned room.
    // notifyAdmin                 (13.0) Prepare message, save activity log,
    //                                    and send admin notification.
    // prepareMessage              (14.0) Retrieve message from message list
    //                                    using id depending on which type of
    //                                    access was recorded.
    // saveActivityLog             (15.0) Save the activity log to the database

    use ShouldNotifyAdmin;

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> $message
     * <Function> The message based on the given message id.
     *
     * @var string
     */
    private $message;

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> $activityLog
     * <Function> Activity log instance.
     *
     * @var \Spatie\Activitylog\Models\Activity
     */
    private $activityLog;

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> created
     * <Function> Fired after an User is created; notifies users.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    private $user;

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> __construct
     * <Function> Inject RoomAccessService dependency.
     *
     * @param \App\Services\RoomAccess\RoomAccessService $roomAccessService
     */
    public function __construct(
        protected RoomAccessService $roomAccessService
    ) {}

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getUsers
     * <Function> Retrieve paginated list of user accounts.
     *
     * @param array $params
     *
     * @return $users
     */
    public function getUsers(array $params)
    {
        [$search, $sort, $direction, $roomFilter] = [
            @$params['search'],
            @$params['sort'],
            @$params['direction'],
            @$params['room']
        ];

        $users = User::query()
            ->when(
                $search,
                function ($query, $search) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('username', 'like', "%{$search}%")
                            ->orWhereHas('room', function ($query) use ($search) {
                                return $query->where('name', 'like', "%{$search}%");
                            });
                    });
                }
            )
            ->when(
                in_array($sort, ['name', 'username', 'room_name']),
                function ($query) use ($sort, $direction) {
                    if ($sort === 'room_name') {
                        $query = $query->withAggregate('room', 'name');
                    }

                    $query->orderBy($sort, $direction);
                }
            )
            ->when($roomFilter, function ($query, $roomFilter) {
                $query->whereHas('room', function ($q) use ($roomFilter) {
                    $q->whereRaw("LOWER(REPLACE(rooms.name, ' ', '-')) = ?", [strtolower($roomFilter)]);
                });
            })
            ->with(['room', 'roomAccess', 'switchBotLockAccess' => function ($query) {
                $query->select('id', 'status', 'switch_bot_lock_accesses.user_id');
            }])
            ->latest()
            ->paginate(15);

        return $users;
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> createUser
     * <Function> Create a new user account.
     *
     * @param array $userData
     * @param array $roomAccessData
     * @param array $switchBotLockAccessData
     *
     * @return $user
     */

    public function createUser(array $userData, array $roomAccessData, array $switchBotLockAccessData)
    {
        return DB::transaction(function () use ($userData, $roomAccessData, $switchBotLockAccessData) {
            // Create user
            $user = User::create($userData);

            // Create Room Access
            $this->roomAccessService->createRoomAccess($roomAccessData, $user, $switchBotLockAccessData);

            // Create Notification
            $user->load('room');

            $this->user = $user;
            $this->notifyAdmin($user);

            // Assign Role
            $user->assignRole('user');
        });
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> updateUser
     * <Function> Update an existing user account.
     *
     * @param array $userData
     * @param \App\Models\User $user
     *
     * @return $user
     */
    public function updateUser(array $userData, User $user, array $roomAccessData, array $switchBotLockAccessData)
    {
        return DB::transaction(function () use ($userData, $roomAccessData, $user, $switchBotLockAccessData) {
            // Update User
            $user->update($userData);

            // Update Room Access
            $this->roomAccessService->updateRoomAccess($roomAccessData, $user, $switchBotLockAccessData);
        });
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> deleteUser
     * <Function> Delete a user account.
     *
     * @param \App\Models\User $user
     *
     * @return $user
     */
    public function deleteUser(User $user)
    {
        return DB::transaction(function () use ($user) {
            // Delete Room Access
            $this->roomAccessService->deleteRoomAccess($user);

            // Delete User
            $user->delete();
        });
    }

    /**
     * <Layer number> (9.0)
     *
     * <Processing name> getRooms
     * <Function> Retrieve all room records.
     *
     * @return $users
     */
    public function getRooms()
    {
        return Room::all();
    }

    /**
     * <Layer number> (10.0)
     *
     * <Processing name> exportUsers
     * <Function> Export all existing Users into an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportUsers(): BinaryFileResponse
    {
        $rows = User::with(['room', 'roomAccess'])
            ->get()
            ->map(fn($g) => [
                'room_name'                => $g->room?->name,
                'user_name'                => $g->username,
                'name'                     => $g->name,
                'created_at'               => $g->created_at,
                'is_permanent'             => $g->roomAccess?->is_permanent,
                'start_date'               => $g->roomAccess?->start_date,
                'end_date'                 => $g->roomAccess?->end_date,
            ]);

        // Define the column headings (added after Name)
        $headings = ['Room Name', 'User Name', 'Name', 'Created At', 'Is Permanent', 'Start Date', 'End Date'];

        $export = new BaseExport($rows, $headings);

        return $export->download('users', auth()->user());
    }

    /**
     * <Layer number> (11.0)
     *
     * <Processing name> notification
     * <Function> Create notification array data. Implemented from trait.
     *
     * @return array
     */
    public function notification(): array
    {
        return [
            'nature'  => Activity::TYPE_INFO,
            'title'   => 'User Created',
            'message' => $this->message,
            'url' => route('admin.management.logs.index', [
                'search'    => $this->activityLog->id,
                'end_date'  => $this->activityLog->created_at->format('Y-m-d'),
                'expanded'  => true
            ])
        ];
    }

    /**
     * <Layer number> (12.0)
     *
     * <Processing name> getRoom
     * <Function> Retrieve the user's assigned room.
     *
     * @return \App\Models\Room
     */
    private function getRoom(): ?Room
    {
        return $this->user ? $this->user->room : null;
    }

    /**
     * <Layer number> (13.0)
     * <Processing name> notifyAdmin
     * <Function> Prepare message, save activity log, and send admin notification.
     *
     * @param \App\Models\User $user
     * @return void
     */
    private function notifyAdmin(User $user): void
    {
        $this->prepareMessage($user)
            ->saveActivityLog($user)
            ->sendAdminNotification();
    }

    /**
     * <Layer number> (14.0)
     *
     * <Processing name> prepareMessage
     * <Function> Retrieve message from message list using id depending
     *            on which type of access was recorded.
     *
     * @return self
     */
    private function prepareMessage($user): self
    {
        $room = $this->getRoom();
        $roomName = $room ? $room->name : '-not set-';

        $this->message = get_message(
            Activity::TYPE_AUDIT,
            'A004',
            ['user' => $this->user->name, 'room' => $roomName]
        );

        return $this;
    }

    /**
     * <Layer number> (15.0)
     *
     * <Processing name> saveActivityLog
     * <Function> Save the activity log to the database.
     *
     * @return self
     */
    public function saveActivityLog(User $user): self
    {
        $this->activityLog = activity()
            ->inLog(Activity::TYPE_AUDIT)
            ->withProperties([
                'Code'    => 'A004',
                'Floor'   => $this->getRoom($user)->floor->name ?? '-not-set-',
                'Room'    => $this->getRoom($user)->name ?? '-not-set-',
            ])
            ->log($this->message);

        return $this;
    }
}
