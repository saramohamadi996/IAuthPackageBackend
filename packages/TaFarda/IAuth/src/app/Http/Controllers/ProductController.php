<?php

namespace TaFarda\IAuth\app\Http\Controllers;

use TaFarda\IAuth\app\Contracts\Interfaces\ProductRepositoryInterface;
use TaFarda\IAuth\app\Requests\ProductStoreRequest;
use TaFarda\IAuth\app\Requests\ProductUpdateRequest;
use TaFarda\IAuth\app\Resources\GenericResource;
use TaFarda\IAuth\app\Resources\ProductResource;
use TaFarda\IAuth\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use ApiResponse;

    /**
     * The product repository instance.
     *
     * @var ProductRepositoryInterface
     */
    protected ProductRepositoryInterface $productRepository;

    /**
     * Instantiate a new product instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */

    /**
     *  Instantiate a new product instance.
     *
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    /**
     *Show all products with pagination and the possibility of filtering items.
     *
     * @return GenericResource
     */
    public function index(): GenericResource
    {
        $product = $this->productRepository->productQuery();
        $query = $this->productRepository->productPermissionLimit();
        [$products, $totalProducts] = $this->productRepository->index($product, $query);
        return new GenericResource([
                'items' => ProductResource::collection($products),
                'total_items' => $totalProducts
            ]
        );
    }

    /**
     *  save the created product.
     *
     * @param ProductStoreRequest $request
     * @return JsonResponse
     */
    public function store(ProductStoreRequest $request): JsonResponse
    {
        $store = $this->productRepository->create($request->all());
        if ($store) {
            return $this->createSuccess(new ProductResource($store),
                trans('Product creation was created successfully.')
            );
        }
        return $this->error(trans('An error occurred creating the product.'));
    }

    /**
     *  save changed product.
     *
     * @param $productId
     * @param ProductUpdateRequest $request
     * @return JsonResponse
     */
    public function update($productId, ProductUpdateRequest $request): JsonResponse
    {
        $update = $this->productRepository->update($productId, $request->all());
        if ($update) {
            return $this->success(new ProductResource($update),
                trans('The product update was completed successfully.')
            );
        }
        return $this->error(trans('The product update encountered an error.'));
    }

}

