<?php

namespace TaFarda\IAuth\app\Http\Controllers;

use TaFarda\IAuth\app\Contracts\Interfaces\LogRepositoryInterface;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Resources\LogResource;
use Spatie\Activitylog\Models\Activity;
use TaFarda\IAuth\Traits\ApiResponse;
use App\Http\Controllers\Controller;

class LogController extends Controller
{
    use ApiResponse;

    /**
     * The log repository instance.
     *
     * @var LogRepositoryInterface
     */
    protected LogRepositoryInterface $logRepository;

    /**
     * Instantiate a new log instance.
     *
     * @param LogRepositoryInterface $logRepository
     */
    public function __construct(LogRepositoryInterface $logRepository)
    {
        $this->logRepository = $logRepository;
    }

    /**
     * Show all logs.
     *
     * @return GenericResource
     */
    public function index(): GenericResource
    {
        $query = $this->logRepository->logQuery();
        [$logs, $totalLogs] = $this->logRepository->index(Activity::class, $query);
        return new GenericResource([
                'items' => LogResource::collection($logs),
                'total_items' => $totalLogs
            ]
        );
    }

}
