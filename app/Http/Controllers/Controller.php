<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Language;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param string|null $language
     * @param string|int|null $currency
     */
    public function __construct(
        protected ?string $language = null,
        protected string|int|null $currency = null
    )
    {
        $this->language = $this->setLanguage();
        $this->currency = $this->setCurrency();
    }

    /**
     * Set default Currency
     */
    protected function setCurrency(): string|int|null
    {
        return request(
            'currency_id',
            data_get(Currency::currenciesList()->where('default', 1)->first(), 'id')
        );
    }

    /**
     * Set default Language
     */
    protected function setLanguage(): ?string
    {
        return request(
            'lang',
            Language::languagesList()->where('default', 1)->first()?->locale
        );
    }
}
