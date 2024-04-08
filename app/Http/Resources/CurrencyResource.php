<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Currency|JsonResource $this */
        return [
            'id'            => $this->id,
            'symbol'        => $this->symbol,
            'title'         => $this->title,
            'rate'          => $this->when($this->rate, (double) $this->rate),
            'default'       => $this->when($this->default, (bool) $this->default),
            'position'      => $this->when($this->position, $this->position),
            'active'        => (bool) $this->active,
            'created_at'    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

        ];
    }
}
