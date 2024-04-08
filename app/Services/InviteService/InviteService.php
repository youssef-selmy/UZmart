<?php
declare(strict_types=1);

namespace App\Services\InviteService;

use App\Helpers\ResponseError;
use App\Models\Invitation;
use App\Models\Shop;
use App\Services\CoreService;
use Exception;
use Throwable;

final class InviteService extends CoreService
{

    protected function getModelClass(): string
    {
        return Invitation::class;
    }

    public function create(string $uuid): array
    {
        try {
            $shop = Shop::firstWhere('uuid', $uuid);

            $invite = $this->model()
                ->updateOrCreate([
                    'user_id' => auth('sanctum')->id()
                ], [
                    'shop_id' => $shop->id,
                ]);

            return [
                'status' => true,
                'code'   => ResponseError::NO_ERROR,
                'data'   => $invite
            ];
        } catch (Exception $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_501, 'message' => $e->getMessage()];
        }
    }

    public function changeStatus(int $id, array $data): array
    {
        try {
            $shopId = $data['shop_id'] ?? null;

            $invite = $this->model()
                ->with([
                    'user'
                ])
                ->whereHas('user')
                ->firstWhere(['id' => $id, 'shop_id' => $shopId]);

            if (!$invite) {
                return [
                    'status' => false,
                    'code'   => ResponseError::ERROR_404
                ];
            }

            $data = [
                'status' => $data['status'] ?? 4,
                'role'   => $data['role']   ?? 'user'
            ];

            /** @var Invitation $invite */
            $invite->update($data);

            if ($data['status'] === 2) {
                $roles   = $invite->user->roles?->pluck('name')?->toArray() ?? [];
                $roles[] = $invite->role;

                $invite->user->syncRoles($roles);
            }

            return [
                'status' => true,
                'data'   => $invite,
            ];
        } catch (Throwable $e) {
            $this->error($e);
            return ['status' => false, 'code' => ResponseError::ERROR_502, 'message' => $e->getMessage()];
        }
    }

}
