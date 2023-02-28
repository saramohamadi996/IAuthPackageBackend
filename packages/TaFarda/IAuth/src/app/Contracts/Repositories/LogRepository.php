<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\LogRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Activitylog\Models\Activity;

class LogRepository extends BaseRepository implements LogRepositoryInterface
{
    /**
     * it is the query builder of logs.
     *
     * @return Builder
     */
    private function fetchQueryBuilder(): Builder
    {
        return Activity::query();
    }

    /**
     * Get the value from the database.
     *
     * @return Builder
     */
    public function logQuery(): Builder
    {
        return $this->fetchQueryBuilder();
    }
}
