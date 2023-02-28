<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;
interface BaseRepositoryInterface
{
    /**
     * paginate and search items.
     *
     * @param $modelName
     * @param $query
     * @return mixed
     */
    public function index($modelName, $query): mixed;
}
