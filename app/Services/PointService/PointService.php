<?php
declare(strict_types=1);

namespace App\Services\PointService;

use App\Helpers\ResponseError;
use App\Http\Resources\PointResource;
use App\Models\Point;
use App\Services\CoreService;
use Illuminate\Http\JsonResponse;
use Throwable;

class PointService extends CoreService
{
    protected function getModelClass(): string
    {
        return Point::class;
    }

    public function create(array $data): array
    {
        try {
            $point = $this->model()->create($data);
            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $point,
            ];
        } catch (Throwable $e) {
            return [
              'status' => false,
              'code'   => ResponseError::ERROR_501,
            ];
        }
    }

    public function update(Point $point, array $data): array
    {
        try {
            $point->update($data);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $point,
            ];
        } catch (Throwable $e) {
            return [
              'status' => false,
              'code'   => ResponseError::ERROR_501,
            ];
        }
    }

    /**
     * Change Active Status of Model.
     *
     * @param int $id
     * @return array
     */
    public function setActive(int $id): array
    {
        $point = $this->model()->find($id);

        if (empty($point)) {
            return [
                'status' => false,
                'code'   => ResponseError::ERROR_501,
            ];
        }

        /** @var Point $point */
        $point->update(['active' => !$point->active]);

        return [
            'status' => true,
            'code'   => ResponseError::NO_ERROR,
            'data'   => $point,
        ];
    }
}
