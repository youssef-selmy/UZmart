<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class FilterParamsRequest extends BaseRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'sort'          => 'string|in:asc,desc',
            'column'        => 'regex:/^[a-zA-Z-_]+$/',
            'perPage'       => 'integer|min:1|max:100',
            'cPerPage'      => 'integer|min:1|max:100',
            'shop_id'       => [
                'integer',
                Rule::exists('shops', 'id')
            ],
            'user_id'       => 'exists:users,id',
            'currency_id'   => 'exists:currencies,id',
            'lang'          => 'exists:languages,locale',
            'category_id'   => 'exists:categories,id',
            'brand_id'      => 'exists:brands,id',
            'region_id'     => 'exists:regions,id',
            'country_id'    => 'exists:countries,id',
            'city_id'       => 'exists:cities,id',
            'area_id'       => 'exists:areas,id',
            'price'         => 'numeric',
            'note'          => 'string|max:255',
            'date_from'     => 'date_format:Y-m-d',
            'date_to'       => 'date_format:Y-m-d',
            'ids'           => 'array',
            'active'        => 'boolean',
        ];
    }

}
