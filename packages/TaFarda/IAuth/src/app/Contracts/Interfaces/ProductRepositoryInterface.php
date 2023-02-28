<?php

namespace TaFarda\IAuth\app\Contracts\Interfaces;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

interface ProductRepositoryInterface
{
    /**
     * Get the value from the database.
     *
     * @return mixed
     */
    public function productQuery(): mixed;

    /**
     * Admin access level control.
     *
     * @return mixed
     */
    public function productPermissionLimit(): mixed;

    /**
     * find by id the record with the given id.
     *
     * @param int $product
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getById(int $product): Model|Collection|Builder|array|null;

    /**
     * create new product.
     *
     * @param array $value
     * @return mixed
     */
    public function create(array $value): mixed;

    /**
     * update existing products.
     *
     * @param int $productId
     * @param array $value
     * @return mixed
     */
    public function update(int $productId, array $value): mixed;
}
