<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InviteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Invitation|JsonResource $this */
        return [
            'id'         => $this->id,
            'shop_id'    => $this->shop_id,
            'user_id'    => $this->user_id,
            'role'       => $this->role,
            'status'     => Invitation::getStatusKey($this->status),
            'created_at' => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at' => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            'user'          => UserResource::make($this->whenLoaded('user')),
            'shop'          => ShopResource::make($this->whenLoaded('shop')),

        ];
    }
}
