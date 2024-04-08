<?php
declare(strict_types=1);

namespace App\Http\Controllers\API\v1\Dashboard\Seller;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\InviteResource;
use App\Repositories\InviteRepository\InviteRepository;
use App\Services\InviteService\InviteService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InviteController extends SellerBaseController
{

    public function __construct(private InviteRepository $repository, private InviteService $service)
    {
        parent::__construct();
    }

    public function paginate(FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $invites = $this->repository->paginate($request->merge(['shop_id' => $this->shop->id])->all());

        return InviteResource::collection($invites);
    }

    public function changeStatus(int $id, FilterParamsRequest $request): InviteResource|JsonResponse
    {
        $result = $this->service->changeStatus($id, $request->merge(['shop_id' => $this->shop->id])->all());

        if (!data_get($result, 'status')) {
            return $this->onErrorResponse($result);
        }

        return $this->successResponse(
            __('errors.' . ResponseError::NO_ERROR, locale: $this->language),
            InviteResource::make(data_get($result, 'data'))
        );
    }

}
