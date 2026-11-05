<?php

namespace App\Services\Corsight;

use App\Http\Requests\CorsightAccount\{StoreRequest, UpdateRequest};
use App\Models\CorsightAccount;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Pagination\LengthAwarePaginator;

class CorsightAccountService
{
    /**************************************************************************/
    /* Processing Hierarchy                                                   */
    /**************************************************************************/
    // getAccounts       (1.0) Retrieves a paginated list of accounts
    //                         with sorting and filtering options
    // storeAccount      (2.0) Stores a new account based on
    //                         validated data from the request
    // updateAccount     (3.0) Updates an existing account based on
    //                         validated data from the request
    // getCredentials    (4.0) Retrieves the decrypted credentials
    //                         for a given account

    /**
     * <Layer number> (1.0)
     *
     * <Processing name> getAccounts
     * <Function> Retrieves a paginated list of accounts with
     *            sorting and filtering options
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAccounts(Request $request): LengthAwarePaginator
    {
        $sortField = $request->query('field', 'created_at');
        $sortDirection = $request->query('direction', 'desc');

        return CorsightAccount::orderBy($sortField, $sortDirection)
            ->when($request->query('search'), function ($query, $search) {
                return $query->where('username', 'LIKE', "%{$search}%");
            })
            ->paginate(15)
            ->through(
                fn($account) => [
                    'id' => $account->id,
                    'username' => $account->username,
                    'endpoint_url' => $account->endpoint_url,
                    'endpoint_secret' => $account->endpoint_secret,
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
     * @param \App\Http\Requests\CorsightAccount\StoreRequest $request
     * @return void
     */
    public function storeAccount(StoreRequest $request): void
    {
        CorsightAccount::create($request->validated());
    }

    /**
     * <Layer number> (3.0)
     *
     * <Processing name> updateAccount
     * <Function> Updates an existing account based on
     *            validated data from the request
     *
     * @param \App\Http\Requests\CorsightAccount\UpdateRequest $request
     * @param \App\Models\CorsightAccount $account
     * @return void
     */
    public function updateAccount(UpdateRequest $request, CorsightAccount $account): void
    {
        $account->update($request->validated());
    }

    /**
     * <Layer number> (4.0)
     *
     * <Processing name> getCredentials
     * <Function> Retrieves the decrypted credentials
     *            for a given account
     *
     * @param \App\Models\CorsightAccount $account
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCredentials(CorsightAccount $account): JsonResponse
    {
        return response()->json([
            'username' => $account->username,
            'password' => $account->password,
            'endpoint_secret'    => $account->endpoint_secret,
            'endpoint_url'       => $account->endpoint_url,
        ]);
    }
}
