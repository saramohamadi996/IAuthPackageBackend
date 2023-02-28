<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface AdminRepositoryInterface
{
    /**
     * Get the value from the database.
     *
     * @return mixed
     */
    public function adminQuery(): mixed;

    /**
     * Admin access level control.
     *
     * @return mixed
     */
    public function adminPermissionLimit(): mixed;

    /**
     * Getting a list of admins who don't have products.
     *
     * @return mixed
     */
    public function availablesLimit(): mixed;

    /**
     * find by id the record with the given id.
     *
     * @param int $adminId
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getById(int $adminId): Model|Collection|Builder|array|null;

    /**
     * create new admin.
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed;

    /**
     * update existing admins.
     *
     * @param int $adminId
     * @param array $request
     * @return mixed
     */
    public function update(int $adminId, array $request): mixed;
}
