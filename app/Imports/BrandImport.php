<?php
declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BrandImport extends BaseImport implements ToCollection, WithHeadingRow, WithBatchInserts
{
    use Importable;

    public function __construct(private ?int $shopId = null) {}

    /**
     * @param Collection $collection
     * @return void
     */
    public function collection(Collection $collection): void
    {
        foreach ($collection as $row) {

            if (!data_get($row, 'title')) {
                continue;
            }

            $brand = Brand::updateOrCreate([
                'title'   => data_get($row, 'title'),
                'shop_id' => $this->shopId,
            ], [
                'title'  => data_get($row, 'title', ''),
                'active' => data_get($row, 'active') == 'active',
            ]);

            $this->downloadImages($brand, data_get($row, 'img_urls', ''));
        }

    }

    public function headingRow(): int
    {
        return 1;
    }

    public function batchSize(): int
    {
        return 200;
    }

    public function chunkSize(): int
    {
        return 200;
    }
}
