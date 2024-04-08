<?php
declare(strict_types=1);

namespace App\Services\GalleryService;

use App\Helpers\ResponseError;
use App\Models\Gallery;
use App\Services\CoreService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Throwable;

class FileStorageService extends CoreService
{
    protected function getModelClass(): string
    {
        return Gallery::class;
    }

    public function getStorageFiles(array $filter, int $perPage = 10): LengthAwarePaginator
    {
        return Gallery::filter($filter)->paginate($perPage);
    }

    public function deleteFileFromStorage(array $filter = []): array
    {
        try {
            $ids = data_get($filter, 'ids', []);

            foreach (Gallery::filter($filter)->find(is_array($ids) ? $ids : []) as $gallery) {
                $gallery->delete();
            }

            return ['status' => true, 'code' => ResponseError::NO_ERROR, 'data' => []];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_404];
        }
    }
}
