<?php

namespace TaFarda\IAuth\app\Http\Controllers;

use TaFarda\IAuth\app\Contracts\Interfaces\AdminRepositoryInterface;
use TaFarda\IAuth\app\Requests\AdminCreateRequest;
use TaFarda\IAuth\app\Requests\AdminUpdateRequest;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Resources\AdminResource;
use TaFarda\IAuth\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    use ApiResponse;

    /**
     * The admin repository instance.
     *
     * @var AdminRepositoryInterface
     */
    protected AdminRepositoryInterface $adminRepository;

    /**
     * Instantiate a new admin instance.
     *
     * @param AdminRepositoryInterface $adminRepository
     */
    public function __construct(AdminRepositoryInterface $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    /**
     * Show all admins with pagination and the possibility of filtering items.
     *
     * @return GenericResource
     */
    public function index(): GenericResource
    {
        $admin = $this->adminRepository->adminQuery();
        $query = $this->adminRepository->adminPermissionLimit();
        [$admins, $totalAdmins] = $this->adminRepository->index($admin, $query);
        return new GenericResource([
                'items' => AdminResource::collection($admins),
                'total_items' => $totalAdmins
            ]
        );
    }

    /**
     * Show all available admins with pagination and the possibility of filtering items.
     *
     * @return GenericResource
     */
    public function availables(): GenericResource
    {
        $admin = $this->adminRepository->adminQuery();
        $query = $this->adminRepository->availablesLimit();
        [$admins, $totalAdmins] = $this->adminRepository->index($admin, $query);
        return new GenericResource([
                'items' => AdminResource::collection($admins),
                'total_items' => $totalAdmins
            ]
        );
    }

    /**
     * show the profile for a given admin.
     *
     * @return AdminResource
     */
    public function profile(): AdminResource
    {
        $adminId = auth()->id();
        $admin = $this->adminRepository->getById($adminId);
        return new AdminResource($admin);
    }

    /**
     * save the created admin.
     *
     * @param AdminCreateRequest $request
     * @return JsonResponse
     */
    public function store(AdminCreateRequest $request): JsonResponse
    {
        $admin = $this->adminRepository->create($request->all());
        if ($admin) {
            return $this->createSuccess(new AdminResource($admin),
                trans('admin creation was created successfully.')
            );
        }
        return $this->error(trans('An error occurred creating the admin.'));
    }

    /**
     *  save changed admin.
     *
     * @param $adminId
     * @param AdminUpdateRequest $request
     * @return JsonResponse
     */
    public function update($adminId, AdminUpdateRequest $request): JsonResponse
    {
        $update = $this->adminRepository->update($adminId, $request->all());
        if ($update) {
            return $this->success(new AdminResource($update),
                trans('The admin update was completed successfully.')
            );
        }
        return $this->error(trans('The admin update encountered an error.'));
    }
}
