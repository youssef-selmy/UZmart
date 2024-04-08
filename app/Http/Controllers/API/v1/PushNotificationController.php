<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\PushNotificationResource;
use App\Repositories\PushNotificationRepository\PushNotificationRepository;
use App\Services\PushNotificationService\PushNotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PushNotificationController extends Controller
{
    use ApiResponse;

    public function __construct(
        private PushNotificationRepository $repository,
        private PushNotificationService $service
    )
    {
        parent::__construct();

        $this->middleware(['sanctum.check'])->except('store');
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function index(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $filter = $request->merge(['user_id' => auth('sanctum')->id()])->all();
        $model  = $this->repository->paginate($filter);

        return PushNotificationResource::collection($model);
    }

    /**
     * Display a listing of the resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $model = $this->repository->show($id, auth('sanctum')->id());

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
            $model ? PushNotificationResource::make($model) : $model
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param array $data
     * @return JsonResponse
     */
    public function store(array $data): JsonResponse
    {
        $data['user_id'] = auth('sanctum')->id();

        $model = $this->service->store($data);

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            $model ? PushNotificationResource::make($model) : $model
        );

    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function readAt(int $id): JsonResponse
    {
        $model = $this->service->readAt($id, auth('sanctum')->id());

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
            $model ? PushNotificationResource::make($model) : $model
        );
    }

    /**
     * @return JsonResponse
     */
    public function readAll(): JsonResponse
    {
        $this->service->readAll(auth('sanctum')->id());

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
        );
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $this->service->delete($id, auth('sanctum')->id());

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
        );
    }

    /**
     * @return JsonResponse
     */
    public function deleteAll(): JsonResponse
    {
        $this->service->deleteAll(auth('sanctum')->id());

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
        );
    }
}
