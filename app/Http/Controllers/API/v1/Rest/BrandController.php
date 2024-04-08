<?php

namespace App\Http\Controllers\API\v1\Rest;

use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\BrandResource;
use App\Repositories\BrandRepository\BrandRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BrandController extends RestBaseController
{
    
    public function __construct(private BrandRepository  $repository)
    {
        parent::__construct();
    }

    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $brands = $this->repository->brandsPaginate($request->merge(['active' => 1])->all());

        return BrandResource::collection($brands);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $brand = $this->repository->brandDetails($id);

        if (empty($brand)) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
            BrandResource::make($brand)
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  string $string
     * @return JsonResponse
     */
    public function showSlug(string $string): JsonResponse
    {
        $brand = $this->repository->brandDetailsBySlug($string);

        if (empty($brand)) {
            return $this->onErrorResponse([
                'code'    => ResponseError::ERROR_404,
                'message' => __('errors.' . ResponseError::ERROR_404, locale: $this->language)
            ]);
        }

        return $this->successResponse(
            __('errors.'. ResponseError::NO_ERROR, locale: $this->language),
            BrandResource::make($brand)
        );
    }
}
