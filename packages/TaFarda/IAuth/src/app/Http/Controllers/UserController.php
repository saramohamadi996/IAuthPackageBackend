<?php

namespace TaFarda\IAuth\app\Http\Controllers;

use TaFarda\IAuth\app\Contracts\Interfaces\UserRepositoryInterface;
use TaFarda\IAuth\app\Requests\UserWebserviceUpdateRequestRequest;
use TaFarda\IAuth\app\Requests\UserWebserviceUpdateRequest;
use TaFarda\IAuth\app\Requests\UserVerifyRequestRequest;
use TaFarda\IAuth\app\Requests\UserVerifyWithOtpRequest;
use TaFarda\IAuth\app\Resources\WebserviceUserResource;
use TaFarda\IAuth\app\Requests\UserProfileRequest;
use TaFarda\IAuth\app\Requests\UserUpdateRequest;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Requests\UserStoreRequest;
use TaFarda\IAuth\app\Requests\UserShowRequest;
use TaFarda\IAuth\app\Resources\UserResource;
use TaFarda\IAuth\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use TaFarda\IAuth\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    use ApiResponse;

    /**
     * The user repository instance.
     *
     * @var UserRepositoryInterface
     */
    protected UserRepositoryInterface $userRepository;

    /**
     *  Instantiate a new user instance.
     *
     * @param UserRepositoryInterface $userRepository
     */
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }


    /**
     * Show all users with pagination and the possibility of filtering items.
     *
     * @return GenericResource
     */
    public function index(): GenericResource
    {
        $user = $this->userRepository->userQuery();
        $query = $this->userRepository->userPermissionLimited();
        [$users, $totalUsers] = $this->userRepository->index($user, $query);
        return new GenericResource([
                'items' => UserResource::collection($users),
                'total_items' => $totalUsers
            ]
        );
    }

    /**
     * save the created user and profile.
     *
     * @param UserStoreRequest $request
     * @param UserProfileRequest $profile
     * @return JsonResponse
     */
    public function store(UserStoreRequest $request, UserProfileRequest $profile): JsonResponse
    {
        $store = $this->userRepository->createUser($request->all(), $profile);
        if ($store) {
            return $this->createSuccess(new UserResource($store),
                trans('User created successfully.')
            );
        }
        return $this->error(trans('An error occurred creating the user.'));
    }

    /**
     *  save changed user and profile.
     *
     * @param UserUpdateRequest $request
     * @param UserProfileRequest $profile
     * @param $user
     * @return JsonResponse
     */
    public function update(UserUpdateRequest $request, UserProfileRequest $profile, $user): JsonResponse
    {
        $user = $this->userRepository->getById($user);
        if (!$user)
            return $this->error(trans('We do not have users with these characteristics.'));

        $limited = $this->userRepository->updatePermissionLimited($user);
        if ($limited)
            return $this->forbiddenError(trans('You do not have permission to edit this user.'));

        $update = $this->userRepository->updateUser($request, $profile, $user);
        if ($update) {
            return $this->success(new UserResource($update),
                trans('The user update was successful.')
            );
        }
        return $this->error(trans('The user update encountered an error.'));
    }

    /**
     *  Create a new mobile or email and otp code or update an existing otp code.
     *
     * @param UserVerifyRequestRequest $request
     * @return JsonResponse
     */
    public function verifyRequest(UserVerifyRequestRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userRepository->getUser($data);

        if ($user && $user->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        $now = Carbon::now();
        if ($user && $user->otp_sent && $now->isBefore($user->otp_sent))
            return $this->forbiddenError(trans('Your code is valid. Please wait some minutes.'));

        $user = $this->userRepository->verifyRequest($data);

        return $this->createSuccess(new WebserviceUserResource($user), trans(' successfully.'));
    }

    /**
     * Get mobile or email and otp code and match database and verify account.
     *
     * @param UserVerifyWithOtpRequest $request
     * @return JsonResponse
     */
    public function verify(UserVerifyWithOtpRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $this->userRepository->getUser($data);

        if (!$user)
            return $this->error(trans('We do not have users with these characteristics.'));

        if ($user->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        if ($user->otp_code != $data['otp_code'])
            return $this->error(trans('Your activation code is incorrect.'));

        $now = Carbon::now();
        if ($now->isAfter($user->otp_sent))
            return $this->forbiddenError(trans('Your activation code has expired.'));

        $user = $this->userRepository->verify($data);
        return $this->success(new WebserviceUserResource($user),
            trans('User verification was successful.'),
        );
    }

    /**
     *  show the profile for a given user.
     *
     * @param UserShowRequest $request
     * @return WebserviceUserResource|JsonResponse
     */
    public function showByValue(UserShowRequest $request): WebserviceUserResource|JsonResponse
    {
        $data = $request->validated();
        $user = $this->userRepository->getUser($data);

        if (!$user)
            return $this->error(trans('We do not have users with these characteristics.'));

        if ($user->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        return new WebserviceUserResource($user);
    }

    public function webserviceUpdateRequest(UserWebserviceUpdateRequestRequest $request): JsonResponse|WebserviceUserResource
    {
        $data = $request->validated();
        $user = $this->userRepository->getUser($data);
        $now = Carbon::now();

        if (!$user)
            return $this->error(trans('We do not have users with these characteristics.'));

        if ($user->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        if (isset($data['otp_code'])) {
            if ($user->otp_code != $data['otp_code'])
                return $this->forbiddenError(trans('Your activation code is incorrect.'));

            if ($now->isAfter($user->otp_sent))
                return $this->forbiddenError(trans('Your activation code has expired.'));

            $user = $this->userRepository->verify($data);
        }

        if ($now->subHour()->isBefore($user->verified_at))
            return new WebserviceUserResource($user);

        $now = Carbon::now();
        if ($user->otp_sent && $now->isBefore($user->otp_sent))
            return $this->forbiddenError(trans('Your code is valid. Please wait some minutes.'));

        $user = $this->userRepository->verifyRequest($data);

        return $this->success(new WebserviceUserResource($user),
            trans('Considering that more than an hour has passed since your last authentication,you need to authenticate again. Please request update once more.'));
    }

    public function webserviceUpdate(UserWebserviceUpdateRequest $request): JsonResponse|WebserviceUserResource
    {
        $data = $request->validated();
        $user = $this->userRepository->getUser($data);
        $now = Carbon::now();

        if (!$user)
            return $this->error(trans('We do not have users with these characteristics.'));

        if ($user->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        if ($now->subHour()->isAfter($user->verified_at))
            return $this->forbiddenError(
                trans('Considering that more than an hour has passed since your last authentication,you need to authenticate again. Please request update once more.'));

        $this->userRepository->updateUserInformation($user, $request);
        $user->profile()->update($request->only($user->profile->getFillable()));

        return new WebserviceUserResource(User::find($user->id));
    }
}
