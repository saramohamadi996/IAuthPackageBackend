<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\AdminRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use TaFarda\IAuth\Models\Admin;
use Illuminate\Support\Str;

class AdminRepository extends BaseRepository implements AdminRepositoryInterface
{
    /**
     * it is the query builder of Admins.
     *
     * @return Builder
     */
    private function fetchQueryBuilder(): Builder
    {
        return Admin::query();
    }

    /**
     * Get the value from the database.
     *
     * @return Builder
     */
    public function adminQuery(): Builder
    {
        return $this->fetchQueryBuilder();
    }

    /**
     * Admin access level control.
     *
     * @return Builder
     */
    public function adminPermissionLimit(): Builder
    {
        $auth = auth()->user();
        $query = $this->fetchQueryBuilder();
        if (!$auth->hasRole('super-admin'))
            $query = $query->where('id', $auth->id);
        return $query;
    }

    /**
     * Getting a list of admins who don't have products.
     *
     * @return Builder
     */
    public function availablesLimit(): Builder
    {
        return $this->fetchQueryBuilder()->whereDoesntHave('product');
    }

    /**
     * find by id the record with the given id.
     *
     * @param int $adminId
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getById(int $adminId): Model|Collection|Builder|array|null
    {
        return $this->fetchQueryBuilder()->find($adminId);
    }

    /**
     * create new admin.
     *
     * @param $request
     * @return mixed
     */
    public function create($request): mixed
    {
        return Admin::create([
            'email' => $request['email'],
            'status' => $request['status'],
            'password' => Hash::make($request['password']),
            'webservice_call_token' => Str::random(12),
            $permissions = $request['permissions'] ?? null
        ])->syncPermissions($permissions);
    }

    /**
     * update existing admins.
     *
     * @param int $adminId
     * @param array $request
     * @return mixed
     */
    public function update(int $adminId, array $request): mixed
    {
        $permissions = $request['permissions'] ?? null;
        unset($request['permissions']);
        unset($request['email']);
        if (isset($request['password']))
            $request['password'] = Hash::make($request['password']);
        else
            unset($request['password']);

        $admin = $this->getById($adminId);
        if (!$admin->hasRole('super-admin')) {
            $admin->update($request);
            $admin->syncPermissions([$permissions]);
            $admin->save();
        }
        return $admin;
    }

}
