<?php

namespace App\Exports;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TranslationExport extends BaseExport implements FromCollection, WithHeadings
{
    public function __construct(private ?Collection $languages = null)
    {
        $this->languages = Language::pluck('locale');
    }

    /**
    * @return Collection
    */
    public function collection(): Collection
    {
        $translations = Translation::orderBy('id')->get();

        return $translations->map(fn (Translation $translation) => $this->tableBody($translation));

    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        $headers = [
            'key'
        ];

        foreach ($this->languages as $language) {
            $headers[] = $language;
        }

        return $headers;
    }

    /**
     * @param Translation $translation
     * @return array
     */
    private function tableBody(Translation $translation): array
    {
        $data = [
            'key' => $translation->key,
        ];

        foreach ($this->languages as $language) {

            if ($language == $translation->locale) {
                $data[$language] = $translation->value;
            }

        }

        return $data;
    }
}
