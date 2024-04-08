<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\ShopGallery\StoreRequest;
use App\Http\Resources\ShopGalleryResource;
use App\Models\ShopGallery;
use App\Repositories\ShopGalleryRepository\ShopGalleryRepository;
use App\Services\ShopGalleryService\ShopGalleryService;
use Illuminate\Http\JsonResponse;

class ShopGalleriesController extends SellerBaseController
{

    public function __construct(private ShopGalleryService $service, private ShopGalleryRepository $repository)
    {
        parent::__construct();
    }

    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $model = ShopGallery::where('shop_id', $this->shop->id)->first();

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            !empty($model) ? ShopGalleryResource::make($this->repository->show($model)) : null
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated              = $request->validated();
        $validated['shop_id']   = $this->shop->id;

        $result = $this->service->create($validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            ShopGalleryResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $shopId
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(int $shopId, StoreRequest $request): JsonResponse
    {
        $model = ShopGallery::where('shop_id', $shopId)->first();

        if (empty($model) || $model->shop_id !== $this->shop->id) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        $validated              = $request->validated();
        $validated['shop_id']   = $this->shop->id;

        $result = $this->service->update($model, $validated);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ShopGalleryResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result = $this->service->deleteOne($id, $this->shop->id);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse(['code' => ResponseError::ERROR_404]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language),
            []
        );
    }

}
