<?php
declare(strict_types=1);

namespace App\Repositories\PropertyRepository;

use App\Models\Language;
use App\Models\PropertyValue;
use App\Repositories\CoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;
use Schema;

class PropertyValueRepository extends CoreRepository
{
    protected function getModelClass(): string
    {
        return PropertyValue::class;
    }

    public function index(array $filter): LengthAwarePaginator
    {
        $column = data_get($filter, 'column', 'id');

        if (!Schema::hasColumn('property_values', $column)) {
            $column = 'id';
        }

        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $this->model()
            ->with([
                'group.translation' => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
            ])
            ->when(isset($filter['active']), fn($q) => $q->where('active', $filter['active']))
            ->when(data_get($filter, 'group_id'), fn($q, $groupId) => $q->where('property_group_id', $groupId))
            ->orderBy($column, data_get($filter, 'sort', 'desc'))
            ->paginate(data_get($filter, 'perPage', 10));
    }

    public function show(int $id): Model|null
    {
        $locale = Language::languagesList()->where('default', 1)->first()?->locale;

        return $this->model()
            ->with([
                'galleries'         => fn($q) => $q->select('id', 'type', 'loadable_id', 'path', 'title', 'preview'),
                'group.translation' => fn($q) => $q
                    ->when($this->language, fn($q) => $q->where(function ($q) use ($locale) {
                    $q->where('locale', $this->language)->orWhere('locale', $locale);
                }))
            ])
            ->find($id);
    }

}
