<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\EmailSubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmailSubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var EmailSubscription|JsonResource $this */
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            'active'        => (boolean) $this->active,
            'user'          => UserResource::make($this->whenLoaded('user')),
            'created_at'    => $this->when($this->created_at, $this->created_at?->format('Y-m-d H:i:s') . 'Z'),
            'updated_at'    => $this->when($this->updated_at, $this->updated_at?->format('Y-m-d H:i:s') . 'Z'),
        ];
    }
}
