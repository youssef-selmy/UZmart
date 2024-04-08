<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TranslationTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Translation|JsonResource $this */
        return [
            'id'    => $this->id,
            'group' => $this->group,
            'key'   => $this->key,
            'value' => [
                'locale' => $this->locale,
                'value'  => $this->value,
            ],
            'created_at'    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),
        ];
    }
}
