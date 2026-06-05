<?php
namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelRowsImport implements ToCollection, WithHeadingRow
{
    protected Collection $rows;

    public function collection(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function getRows(): Collection
    {
        return $this->rows;
    }
}

