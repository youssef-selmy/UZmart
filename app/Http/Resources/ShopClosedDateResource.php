<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\ShopClosedDate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShopClosedDateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var ShopClosedDate|JsonResource $this */
        return [
            'id'            => $this->id,
            'day'           => $this->date,
            'created_at'    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            'shop'          => ShopResource::make($this->whenLoaded('shop')),
        ];
    }
}
