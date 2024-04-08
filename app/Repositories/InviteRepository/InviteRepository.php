<?php
declare(strict_types=1);

namespace App\Repositories\InviteRepository;

use App\Models\Invitation;
use App\Models\Language;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class InviteRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return Invitation::class;
    }

    /**
     * @param array $filter
     * @return LengthAwarePaginator
     */
    public function paginate(array $filter): LengthAwarePaginator
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $this->model()
            ->filter($filter)
            ->with([
                'user.roles',
                'user' => fn($q) => $q->select('id', 'firstname', 'lastname'),
                'shop.translation' => function($q) use($locale) {
                    $q->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }));
                }
            ])
            ->orderBy(data_get($filter, 'column', 'id'), data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    /**
     * @param Invitation $invitation
     * @return Invitation
     */
    public function show(Invitation $invitation): Invitation
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $invitation->loadMissing([
            'user.roles',
            'user' => fn($q) => $q->select('id', 'firstname', 'lastname'),
            'shop.translation' => function($q) use($locale) {
                $q->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }));
            }
        ]);
    }

}
