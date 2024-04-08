<?php
declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\PaymentPayload;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentPayloadResource extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  Request $request
     * @return array
     */
    public function toArray($request): array
    {
        /** @var PaymentPayload|JsonResource $this */
        return [
            'payment_id'    => $this->when($this->payment_id, $this->payment_id),
            'payload'       => $this->when($this->payload, $this->payload),

            //Relations
            'payment'       => PaymentResource::make($this->whenLoaded('payment')),
        ];
    }
}
