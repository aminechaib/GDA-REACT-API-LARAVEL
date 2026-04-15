<?php

namespace App\Exports;

use App\Models\Product;
// 1. Change FromQuery to FromCollection
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

// 2. Change FromQuery to FromCollection in the implements list
class ProductsExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithTitle
{
    protected $query;

    public function __construct($query)
    {
        $this->query = $query;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    // 3. Rename query() to collection() and use a cursor
    public function collection()
    {
        // The cursor() method executes the query and retrieves one result at a time
        // as a generator. This keeps memory usage extremely low.
        return $this->query->cursor();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Reference',
            'Designation',
            'Marque',
            'Prix',
            'Date',
            'Fournisseur'
        ];
    }

    public function map($product): array
    {
        // This mapping logic remains the same
        $date = $product->date ? date('m/d/Y', strtotime($product->date)) : '';
        return [
            $product->id,
            $product->ref,
            $product->designation,
            $product->marque,
            $product->prix,
            $date,
            $product->fournisseur,
        ];
    }

    public function title(): string
    {
        return 'Products';
    }
}
