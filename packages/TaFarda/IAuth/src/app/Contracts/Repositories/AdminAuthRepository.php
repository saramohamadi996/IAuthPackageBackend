<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\AdminAuthRepositoryInterface;
use TaFarda\IAuth\Models\Admin;
use Carbon\Carbon;
use Exception;

class AdminAuthRepository implements AdminAuthRepositoryInterface
{
    /**
     * Admin login to the system.
     *
     * @param $value
     * @return mixed
     */
    public function login($value): mixed
    {
        $admin = Admin::where('email', $value['email'])->first();
        if ($admin) {
            $admin->last_successful_login = Carbon::now();
            $admin->save();
        }
        return $admin;
    }

    /**
     *  Admin logout to the system.
     *
     * @return mixed
     * @throws Exception
     */
    public
    function logout(): mixed
    {
        return auth()->user()->currentAccessToken()->delete();

    }
}
