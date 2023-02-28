<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;

interface AdminAuthRepositoryInterface
{
    /**
     * Admin login to the system.
     *
     * @param $value
     * @return mixed
     */
    public function login($value): mixed;

    /**
     * Admin logout to the system.
     *
     * @return mixed
     */
    public function logout(): mixed;
}
