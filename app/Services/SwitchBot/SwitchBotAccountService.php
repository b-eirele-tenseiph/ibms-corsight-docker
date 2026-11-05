<?php

namespace App\Services\SwitchBot;

use App\Exports\BaseExport as Export;
use App\Exports\BaseExport;
use App\Http\Requests\SwitchBotAccount\{StoreRequest, UpdateRequest};
use App\Imports\SwitchBotAccountsImport;
use App\Models\SwitchBotAccount;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Validation\Rules\File;

class SwitchBotAccountService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getAccounts                  (1.0) Retrieves a paginated list of accounts
    //                                    with sorting and filtering options
    // storeAccount                 (2.0) Stores a new account based on
    //                                    validated data from the request
    // updateAccount                (3.0) Updates an existing account based on
    //                                    validated data from the request
    // getAuthCredentials           (4.0) Retrieves the authentication
    //                                    credentials for a given account
    // getAccountWebhook            (5.0) Retrieves or updates the webhook URL
    //                                    and associated tokens for an account
    // importAccount                (6.0) Import SwitchBot accounts from the
    //                                    uploaded Excel/CSV file and validate
    //                                    each row.
    // exportAccount                (7.0) Export all existing SwitchBot accounts
    //                                    into an Excel file.
    // generateTemplateAccount      (8.0) Generate and download an Excel
    //                                    template for importing SwitchBot
    //                                    accounts.

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getAccounts
     * <Function> Retrieves a paginated list of accounts with
     *            sorting and filtering options
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAccounts(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('field', 'created_at');
        $sortDirection = $request->query('direction', 'asc');

        return SwitchBotAccount::when($sortField, function ($query) use ($sortField, $sortDirection) {
            if ($sortField == 'room_name') {
                $query = $query->withAggregate('room', 'name');
            }
            return $query->orderBy($sortField, $sortDirection);
        })
            ->latest()
            ->inRoom($request->query('room'))
            ->when($request->query('search'), function ($query, $search) {
                return $query->where(function ($query) use ($search) {
                    $query->whereHas('room', function ($query) use ($search) {
                        $query->where('name', 'LIKE', "%{$search}%");
                    })
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            })
            ->with('room')
            ->paginate(15)
            ->through(
                fn($device) => [
                    'id' => $device->id,
                    'email' => $device->email,
                    'last_synced_at' => $device->last_synced_at,
                    'room' => [
                        'id' => $device->room?->id,
                        'name' => $device->room?->name
                    ],
                ]
            );
    }

    /**
     * <Layer number> (2.0)
     *
     * <Processing name> storeAccount
     * <Function> Stores a new account based on validated
     *            data from the request
     *
     * @param \App\Http\Requests\SwitchBotAccount\StoreRequest $request
     * @return void
     */
    public function storeAccount(StoreRequest $request): void
    {
        SwitchBotAccount::create($request->validatedData())
            ->setAttribute('webhook', $request->validatedWebhook());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateAccount
     * <Function> Updates an existing account based on
     *            validated data from the request
     *
     * @param \App\Http\Requests\SwitchBotAccount\UpdateRequest $request
     * @return void
     */
    public function updateAccount(UpdateRequest $request, SwitchBotAccount $account): void
    {
        $account->update($request->validatedData());

        $account->setAttribute('webhook', $request->validatedWebhook());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getAuthCredentials
     * <Function> Retrieves the authentication credentials
     *            for a given account
     *
     * @param \App\Models\SwitchBotAccount $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAuthCredentials(SwitchBotAccount $account): JsonResponse
    {
        return response()->json([
            'password' => $account->password,
            'token' => $account->access_token,
            'secret' => $account->secret_key
        ]);
    }

    /**
     * <Layer number> (5.0)
     *
     * <Processing name> getAccountWebhook
     * <Function> Retrieves or updates the webhook URL
     *            and associated tokens for an account
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\SwitchBotAccount $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountWebhook(Request $request, SwitchBotAccount $account): JsonResponse
    {
        $account->access_token = $request->input('token') ?? '';
        $account->secret_key = $request->input('secret') ?? '';

        return response()->json([
            'action' => $account->webhook ? SwitchBotAccount::WEBHOOK_UPDATE : SwitchBotAccount::WEBHOOK_SETUP,
            'url'    => $account->webhook ?? '',
        ]);
    }

    /**
     * <Layer number> (6.0)
     *
     * <Processing name> importAccount
     * <Function> Import SwitchBot accounts from the uploaded Excel/CSV
     *            file and validate each row.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importAccount(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', File::types(['xlsx', 'xls', 'csv'])->max(5 * 1024)]
        ]);

        $import = new SwitchBotAccountsImport();
        $import->startExecutionTime();

        Excel::import($import, $data['file']);

        $import->logImportActivity(
            'SwitchBot Accounts',
            auth()->user(),
            [
                'File Name' => $request->file('file')->getClientOriginalName(),
            ]
        );

        return back()
            ->with(['import' => ['summary' => $import->summary()]]);
    }

    /**
     * <Layer number> (7.0)
     *
     * <Processing name> exportAccount
     * <Function> Export all existing SwitchBot accounts into an Excel file.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function exportAccount()
    {
        $rows = SwitchBotAccount::with('room')->get()->map(fn($g) => [
            'room_id'       => $g->room_id,
            'email'         => $g->email,
            'password'      => $g->password,
            'access_token'  => $g->access_token,
            'secret_key'    => $g->secret_key,
            'created_at'    => $g->created_at,
        ]);

        $headings = ['Room ID', 'Email', 'Password', 'Access Token', 'Secret Key', 'Created At'];

        $export = new BaseExport($rows, $headings);

        return $export->download('switchbot_account', auth()->user());
    }

    /**
     * <Layer number> (8.0)
     *
     * <Processing name> generateTemplateAccount
     * <Function> Generate and download an Excel template for
     *            importing SwitchBot accounts.
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function generateTemplateAccount()
    {

        $dummyRow = [
            'room_name' => 'Room 1',
            'email' => 'user@example.com',
            'password' => 'password',
            'access_token' => '0123456789abcdef0123456789abcdef',
            'secret_key' => '0123456789abcdef0123456789abcdef',
        ];

        $headings = [
            'Room Name',
            'Email',
            'Password',
            'Access Token',
            'Secret Key'
        ];

        return Excel::download(
            new Export(collect([$dummyRow]), $headings),
            Export::getFileName('switchbot_account_template')
        );
    }
}
