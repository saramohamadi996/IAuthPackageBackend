<?php

namespace TaFarda\IAuth\app\Contracts\Repositories;

use TaFarda\IAuth\app\Contracts\Interfaces\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use TaFarda\IAuth\Models\Product;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    /**
     * it is the query builder of Products.
     *
     * @return Builder
     */
    private function fetchQueryBuilder(): Builder
    {
        return Product::query();
    }

    /**
     * Get the value from the database.
     *
     * @return Builder
     */
    public function productQuery(): Builder
    {
        return $this->fetchQueryBuilder();
    }

    /**
     * Admin access level control.
     *
     * @return Builder
     */
    public function productPermissionLimit(): Builder
    {
        $auth = auth()->user();
        $query = $this->fetchQueryBuilder();
        if (!$auth->hasRole('super-admin')) {
            $query = $query->where('admin_id', $auth->id);
        }
        return $query;
    }

    /**
     * find by id the record with the given id.
     *
     * @param int $product
     * @return Builder|Builder[]|Collection|Model|null
     */
    public function getById(int $product): Model|Collection|Builder|array|null
    {
        return $this->fetchQueryBuilder()->find($product);
    }

    /**
     * create new product.
     *
     * @param array $value
     * @return mixed
     */
    public function create(array $value): mixed
    {
        return Product::create($value);
    }

    /**
     * update existing products.
     *
     * @param int $productId
     * @param array $value
     * @return mixed
     */
    public function update(int $productId, array $value): mixed
    {
        Product::where('id', $productId)->update($value);
        return $this->getById($productId);

    }

}

