<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\UserRepositoryInterface;
use TaFarda\IAuth\app\Notifications\UserVerification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use TaFarda\IAuth\Models\User;
use Carbon\Carbon;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    /**
     * it is the query builder of Users.
     *
     * @return Builder
     */
    private function fetchQueryBuilder(): Builder
    {
        return User::query();
    }

    /**
     * Get the value from the database.
     *
     * @return Builder
     */
    public function userQuery(): Builder
    {
        return $this->fetchQueryBuilder();
    }

    /**
     * user access level control.
     *
     * @return Builder
     */
    public function userPermissionLimited(): Builder
    {
        $auth = auth()->user();
        $query = $this->fetchQueryBuilder();
        if (!$auth->hasRole('super-admin')) {
            $query = $query->join('product_user', 'users.id', 'product_user.user_id')
                ->where('product_user.product_id', $auth->product->id);
        }
        return $query;
    }

    /**
     *  user update access level control.
     *
     * @param $user
     * @return true|void
     */
    public function updatePermissionLimited($user)
    {
        $auth = auth()->user();
        if (!$auth->hasRole('super-admin') && $auth->product && !in_array($auth->product->id,
                $user->products()->pluck('id')->toArray())) {
            return true;
        }
    }

    /**
     * find by id the record with the given id.
     *
     * @param $user
     * @return Builder|Collection|Model|null
     */
    public
    function getById($user): Builder|Collection|Model|null
    {
        return $this->fetchQueryBuilder()->find($user);
    }

    /**
     * create new user and profile.
     *
     * @param $request
     * @param $profile
     * @return mixed
     */
    public
    function createUser($request, $profile): mixed
    {
        $user = User::create($request);
        if ($user) {
            $user->profile()->create($request);

            if (isset($request['product_ids'])) {
                $user->products()->attach($request['product_ids']);
            }
        }
        return $user;
    }

    /**
     * update existing user and profile.
     *
     * @param $request
     * @param $profile
     * @param  $user
     * @return Model|Collection|Builder|null
     */
    public
    function updateUser($request, $profile, $user): Model|Collection|null|Builder
    {
        $this->updateUserInformation($user, $request);
        if ($request->has('product_ids')) {
            $user->products()->sync($request->product_ids);
        }
        $user->profile()->update($request->only($user->profile->getFillable()));
        return $this->getById($user->id);
    }

    /**
     * update User Information.
     *
     * @param User $user
     * @param $request
     * @return void
     */
    public function updateUserInformation(User $user, $request): void
    {
        if (isset($request['status']))
            $user->status = $request['status'];
        if (empty($user->email)) {
            $user->email = $request['email'];
        }
        if (empty($user->mobile)) {
            $user->mobile = $request['mobile'];
        }
        $user->save();
    }

    /**
     * Get mobile or mobile and match database.
     *
     * @param $request
     * @return mixed
     */
    public function getUser($request): mixed
    {
        return User::where('email', $request['value'])
            ->orWhere('mobile', $request['value'])->first();
    }

    /**
     *  Create a new mobile or email and otp code.
     *
     * @param $request
     * @return mixed
     */
    public function verifyRequest($request): mixed
    {
        $user = $this->getUser($request);
        if (!$user) {
            $otp_code = 1111;
            if (config('app.env') == 'production')
                $otp_code = rand(config('tafarda_iauth.otp_code_min'), config('tafarda_iauth.otp_code_max'));
            $user = User::create([
                $request['type'] => $request['value'],
                'otp_code' => $otp_code,
                'otp_sent' => Carbon::now()->addMinutes(config('tafarda_iauth.otp_sent_resend')),
            ]);
            $user = User::find($user->id);
            $user->profile()->create();
        }
        if ($request['admin']->product) {
            $otp_code = 1111;
            if (config('app.env') == 'production')
                $otp_code = rand(config('tafarda_iauth.otp_code_min'), config('tafarda_iauth.otp_code_max'));
            $user->update([
                'otp_code' => $otp_code,
                'otp_sent' => Carbon::now()->addMinutes(config('tafarda_iauth.otp_sent_update'))
            ]);
            $user->notify(new UserVerification($request['admin']->product));
        }
        return $user;
    }

    /**
     * Get mobile or email and otp code and match database and verify account.
     *
     * @param $request
     * @return mixed
     */
    public function verify($request): mixed
    {
        $user = $this->getUser($request);
        $user->verified_at = Carbon::now();
        if ($request['admin']->product && !in_array($request['admin']->product->id,
                $user->products->pluck('id')->toArray())) {
            $user->products()->attach($request['admin']->product);
        }
        $user->save();
        return $user;
    }
}
