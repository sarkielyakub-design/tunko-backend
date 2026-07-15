<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\AdminController;
use App\Http\Requests\Admin\User\IndexUserRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Requests\Admin\User\CreditWalletRequest;
use App\Http\Requests\Admin\User\DebitWalletRequest;
use App\Http\Requests\Admin\User\FreezeUserRequest;
use App\Http\Requests\Admin\User\ResetPasswordRequest;
use App\Http\Requests\Admin\User\ResetPinRequest;

use App\Http\Resources\Admin\UserCollection;
use App\Http\Resources\Admin\UserResource;

use App\Models\User;

use App\Services\Admin\UserService;

class UserController extends AdminController
{
    public function __construct(
        private UserService $service
    ) {
    }

    /**
     * Users List
     */
    public function index(Request $request)
{
    $query = User::query()
        ->with([
            "wallet",
            "country",
        ]);

    if ($request->filled("search")) {

        $search = $request->search;

        $query->where(function ($q) use ($search) {

            $q->where("first_name", "like", "%{$search}%")
              ->orWhere("last_name", "like", "%{$search}%")
              ->orWhere("email", "like", "%{$search}%")
              ->orWhere("phone", "like", "%{$search}%");

        });

    }

    if ($request->filled("status")) {

        $query->where(
            "is_active",
            $request->status === "active"
        );

    }

    if ($request->filled("verified")) {

        $query->where(
            "is_verified",
            $request->verified === "true"
        );

    }

    return $query
        ->latest()
        ->paginate(
            $request->per_page ?? 20
        );
}

    /**
     * Create User
     */
    public function store(
        StoreUserRequest $request
    )
    {
        $user = $this->service->store(
            $request->validated()
        );

        return $this->success(

            new UserResource($user),

            "User created successfully.",

            201

        );
    }

    /**
     * Show User
     */
    public function show(
        User $user
    )
    {
        return $this->success(

            new UserResource(

                $this->service->show($user)

            )

        );
    }

    /**
     * Update User
     */
    public function update(
        UpdateUserRequest $request,
        User $user
    )
    {
        $user = $this->service->update(

            $user,

            $request->validated()

        );

        return $this->success(

            new UserResource($user),

            "User updated successfully."

        );
    }

    /**
     * Delete User
     */
    public function destroy(
        User $user
    )
    {
        $this->service->destroy($user);

        return $this->success(

            null,

            "User deleted successfully."

        );
    }

    /**
     * Credit Wallet
     */
    public function creditWallet(
        CreditWalletRequest $request,
        User $user
    )
    {
        $user = $this->service->creditWallet(

            $user,

            $request->amount,

            $request->description,

            auth('admin')->user()

        );

        return $this->success(

            new UserResource($user),

            "Wallet credited successfully."

        );
    }

    /**
     * Debit Wallet
     */
    public function debitWallet(
        DebitWalletRequest $request,
        User $user
    )
    {
        $user = $this->service->debitWallet(

            $user,

            $request->amount,

            $request->description,

            auth('admin')->user()

        );

        return $this->success(

            new UserResource($user),

            "Wallet debited successfully."

        );
    }

    /**
     * Freeze User
     */
    public function freeze(
        FreezeUserRequest $request,
        User $user
    )
    {
        $user = $this->service->freeze(

            $user,

            $request->reason,

            auth('admin')->user()

        );

        return $this->success(

            new UserResource($user),

            "User frozen successfully."

        );
    }

    /**
     * Unfreeze User
     */
    public function unfreeze(
        FreezeUserRequest $request,
        User $user
    )
    {
        $user = $this->service->unfreeze(

            $user,

            $request->reason,

            auth('admin')->user()

        );

        return $this->success(

            new UserResource($user),

            "User activated successfully."

        );
    }

    /**
     * Reset Password
     */
    public function resetPassword(
        ResetPasswordRequest $request,
        User $user
    )
    {
        $this->service->resetPassword(

            $user,

            $request->password,

            auth('admin')->user()

        );

        return $this->success(

            null,

            "Password reset successfully."

        );
    }

    /**
     * Reset PIN
     */
    public function resetPin(
        ResetPinRequest $request,
        User $user
    )
    {
        $this->service->resetPin(

            $user,

            $request->pin,

            auth('admin')->user()

        );

        return $this->success(

            null,

            "Transaction PIN reset successfully."

        );
    }
    /**
 * User Details
 */
public function details(User $user)
{
    return $this->success(

        new UserResource(

            $this->service->details($user)

        )

    );
}
}