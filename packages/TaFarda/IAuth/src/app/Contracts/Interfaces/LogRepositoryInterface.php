<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;

interface LogRepositoryInterface
{
    /**
     * Get the value from the database.
     *
     * @return mixed
     */
    public function logQuery(): mixed;
}
