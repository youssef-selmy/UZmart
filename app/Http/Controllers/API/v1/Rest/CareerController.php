<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\CareerResource;
use App\Repositories\CareerRepository\CareerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CareerController extends RestBaseController
{

    public function __construct(private CareerRepository $repository)
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
        $model = $this->repository->paginate($request->all());

        return CareerResource::collection($model);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result = $this->repository->showById($id);

        if (empty($result)) {
            return $this->onErrorResponse([
                'code'      => ResponseError::ERROR_404,
                'message'   => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            CareerResource::make($result)
        );
    }
}
