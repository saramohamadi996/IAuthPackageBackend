<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;

use TaFarda\IAuth\Models\User;

interface UserRepositoryInterface
{
    /**
     * Get the value from the database.
     *
     * @return mixed
     */
    public function userQuery(): mixed;

    /**
     * user access level control.
     *
     * @return mixed
     */
    public function userPermissionLimited(): mixed;

    /**
     *  user update access level control.
     *
     * @param $user
     */
    public function updatePermissionLimited($user);

    /**
     * find by id the record with the given id.
     *
     * @param $user
     * @return mixed
     */
    public function getById($user): mixed;

    /**
     * create new user and profile.
     *
     * @param $request
     * @param $profile
     * @return mixed
     */
    public function createUser($request, $profile): mixed;

    /**
     * update existing user and profile.
     *
     * @param $request
     * @param $profile
     * @param $user
     * @return mixed
     */
    public function updateUser($request, $profile, $user): mixed;

    /**
     * update User Information.
     *
     * @param User $user
     * @param $request
     * @return void
     */
    public function updateUserInformation(User $user, $request): void;

    /**
     * update an existing otp code.
     *
     * @param $request
     * @return mixed
     */
    public function verifyRequest($request): mixed;

    /**
     * Get mobile or email and otp code and match database and verify account.
     *
     * @param $request
     * @return mixed
     */
    public function verify($request): mixed;

}
