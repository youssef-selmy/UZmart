<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\User;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\InviteResource;
use App\Repositories\InviteRepository\InviteRepository;
use App\Services\InviteService\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InviteController extends UserBaseController
{

    public function __construct(private InviteRepository $repository, private InviteService $service)
    {
        parent::__construct();
    }

    /**
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $invites = $this->repository->paginate($request->all());

        return InviteResource::collection($invites);
    }

    /**
     * @param string $uuid
     * @return JsonResponse
     */
    public function create(string $uuid): JsonResponse
    {
        $result = $this->service->create($uuid);

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            InviteResource::make(data_get($result, 'data'))
        );
    }
}
