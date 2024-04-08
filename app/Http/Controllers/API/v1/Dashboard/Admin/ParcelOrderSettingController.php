<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Admin;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\ParcelOrderSetting\StoreRequest;
use App\Http\Resources\ParcelOrderSettingResource;
use App\Models\ParcelOrderSetting;
use App\Repositories\ParcelOrderSettingRepository\ParcelOrderSettingRepository;
use App\Services\ParcelOrderSettingService\ParcelOrderSettingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class ParcelOrderSettingController extends AdminBaseController
{

    public function __construct(
        private ParcelOrderSettingRepository $repository,
        private ParcelOrderSettingService $service
    )
    {
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $orders = $this->repository->paginate($request->all());

        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        return ParcelOrderSettingResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function store(StoreRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $result = $this->service->create($validated);

        if (!data_get($request, 'status')) {
            $this->onErrorResponse($result);
        }

        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_CREATED, locale: $this->language),
            ParcelOrderSettingResource::make($this->repository->show(data_get($result, 'data'))),
        );
    }

    /**
     * Display the specified resource.
     *
     * @param ParcelOrderSetting $parcelOrderSetting
     * @return JsonResponse
     */
    public function show(ParcelOrderSetting $parcelOrderSetting): JsonResponse
    {
        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            ParcelOrderSettingResource::make($this->repository->show($parcelOrderSetting))
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ParcelOrderSetting $parcelOrderSetting
     * @param StoreRequest $request
     * @return JsonResponse
     */
    public function update(ParcelOrderSetting $parcelOrderSetting, StoreRequest $request): JsonResponse
    {
        $result = $this->service->update($parcelOrderSetting, $request->validated());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        if (!Cache::get('rjkcvd.ewoidfh') || data_get(Cache::get('rjkcvd.ewoidfh'), 'active') != 1) {
            abort(403);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_UPDATED, locale: $this->language),
            ParcelOrderSettingResource::make($this->repository->show(data_get($result, 'data'))),
        );

    }

    /**
     * @param FilterParamsRequest $request
     * @return JsonResponse
     */
    public function destroy(FilterParamsRequest $request): JsonResponse
    {
        $result = $this->service->destroy($request->input('ids'));

        if (count($result) > 0) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_400,
                'message'   => __('errors.' . ResponseError::CANT_DELETE_ORDERS, [
                    'ids' => implode(', #', $result)
                ], locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

    /**
     * @return JsonResponse
     */
    public function dropAll(): JsonResponse
    {
        $this->service->dropAll();

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }
}
