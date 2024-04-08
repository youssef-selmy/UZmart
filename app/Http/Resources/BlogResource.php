<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var Blog|JsonResource $this */
        $locales = $this->relationLoaded('translations') ?
            $this->translations->pluck('locale')->toArray() : null;

        return [
            'id'            => (int) $this->id,
            'uuid'          => (string) $this->uuid,
            'user_id'       => $this->when($this->user_id, $this->user_id),
            'type'          => $this->type,
            'published_at'  => $this->when($this->published_at, $this->published_at . 'Z'),
            'active'        => (bool)$this->active,
            'img'           => $this->when($this->img, $this->img),
            'r_count'       => $this->when($this->r_count, $this->r_count),
            'r_avg'         => $this->when($this->r_avg, $this->r_avg),
            'r_sum'         => $this->when($this->r_sum, $this->r_sum),
            'created_at'    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),

            // Relations
            'translation'   => TranslationResource::make($this->whenLoaded('translation')),
            'translations'  => TranslationResource::collection($this->whenLoaded('translations')),
            'locales'       => $this->when($locales, $locales),
            'author'        => UserResource::make($this->whenLoaded('author')),
        ];
    }
}
