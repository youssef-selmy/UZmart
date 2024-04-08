<?php
declare(strict_types=1);

namespace App\Http\Requests\Order;

use App\Http\Requests\BaseRequest;
use App\Models\Order;
use Illuminate\Validation\Rule;

class StatusUpdateRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'status' => [
                'string',
                'required',
                Rule::in(Order::STATUSES)
            ],
            'notes' => [
                'array',
            ],
            'notes.*' => [
                'array',
                'required',
            ],
            'notes.*.title' => [
                'array',
                'required',
            ],
            'notes.*.title.*' => [
                'string',
                'required',
            ],
            'notes.*.created_at' => [
                'string',
            ],
        ];
    }
}
