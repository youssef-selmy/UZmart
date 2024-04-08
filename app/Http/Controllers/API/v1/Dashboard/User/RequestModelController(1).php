<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Requests\RequestModel\DeliveryManRequest;
use App\Http\Resources\RequestModelResource;
use App\Models\RequestModel;
use App\Repositories\RequestModelRepository\RequestModelRepository;
use App\Services\RequestModelService\RequestModelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RequestModelController extends UserBaseController
{

    public function __construct(
        private RequestModelRepository $repository,
        private RequestModelService $service,
    )
    {
        parent::__construct();
    }

    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->merge(['created_by' => auth('sanctum')->id(), 'type' => 'user'])->all();

        $models = $this->repository->index($filter);

        return RequestModelResource::collection($models);
    }

    /**
     * Display the specified resource.
     * @param DeliveryManRequest $request
     * @return JsonResponse
     */
    public function store(DeliveryManRequest $request): JsonResponse
    {
        $validated = $request->validated();

        if (auth('sanctum')->user()->hasRole(['admin', 'seller'])) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_400,
                'message' => __('errors.' . ResponseError::ERROR_400, locale: $this->language)
            ]);
        }

        $validated['id'] 			= auth('sanctum')->id();
        $validated['type'] 			= RequestModel::USER;
        $validated['created_by'] 	= auth('sanctum')->id();
        $validated['data']['role']	= 'deliveryman';

        $result = $this->service->create($validated);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            RequestModelResource::make(data_get($result, 'data'))
        );
    }

    /**
     * Display the specified resource.
     * @param RequestModel $requestModel
     * @return JsonResponse
     */
    public function show(RequestModel $requestModel): JsonResponse
    {
        if ($requestModel->created_by !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $model = $this->repository->show($requestModel);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            RequestModelResource::make($this->repository->show($model))
        );
    }

    /**
     * Display the specified resource.
     * @param RequestModel $requestModel
     * @param DeliveryManRequest $request
     * @return JsonResponse
     */
    public function update(RequestModel $requestModel, DeliveryManRequest $request): JsonResponse
    {
        if ($requestModel->created_by !== auth('sanctum')->id()) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        $validated 					= $request->validated();
        $validated['data']['role']	= 'deliveryman';

        $result = $this->service->update($requestModel, $validated);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            RequestModelResource::make(data_get($result, 'data'))
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
        $result = $this->service->delete((array)$id, auth('sanctum')->id());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse([
                'code'    => data_get($result, 'code'),
                'message' => data_get($result, 'message')
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::RECORD_WAS_SUCCESSFULLY_DELETED, locale: $this->language)
        );
    }

}
