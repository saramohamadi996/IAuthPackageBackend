<?php

namespace TaFarda\IAuth\app\Http\Controllers;

use TaFarda\IAuth\app\Contracts\Interfaces\AdminAuthRepositoryInterface;
use TaFarda\IAuth\app\Requests\AdminLoginRequest;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Resources\AdminResource;
use TaFarda\IAuth\Traits\ApiResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminAuthController extends Controller
{
    use ApiResponse;

    /**
     * The admin auth repository instance.
     *
     * @var AdminAuthRepositoryInterface
     */
    protected AdminAuthRepositoryInterface $adminAuthRepository;

    /**
     * Instantiate a new admin auth instance.
     *
     * @param AdminAuthRepositoryInterface $adminAuthRepository
     */
    public function __construct(AdminAuthRepositoryInterface $adminAuthRepository)
    {
        $this->adminAuthRepository = $adminAuthRepository;
    }

    /**
     * Admin login to the system.
     *
     * @param AdminLoginRequest $request
     * @return JsonResponse
     */
    public function login(AdminLoginRequest $request): JsonResponse
    {
        $data = $request->only(['email', 'password']);
        $admin = $this->adminAuthRepository->login($data);
        if (!$admin)
            return $this->badRequestError(trans('incorrect email or password.'));
        elseif ($admin->status == 0)
            return $this->forbiddenError(trans('Your account status is inactive.'));

        return $this->success(new GenericResource([
            'admin' => new AdminResource($admin),
            'token' => $admin->createToken('apiToken')->plainTextToken
        ]),
            trans('admin Logged In Successfully.'),
        );
    }


    /**
     * Admin logout to the system.
     *
     * @return JsonResponse
     */
    public function logout(): JsonResponse
    {
        $this->adminAuthRepository->logout();
        return $this->success(trans('Successful exit.'));
    }

}
