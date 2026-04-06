<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
// We have removed SkipsOnError and SkipsErrors
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ProductsImport implements
    ToModel,
    WithHeadingRow,
    WithBatchInserts,
    WithChunkReading
{
    // The 'use SkipsErrors;' trait is now gone.

    public function model(array $row)
    {
        // Using the robust data cleaning logic from before
        $dateValue = $row['date'] ?? null;
        $formattedDate = null;

        if (!empty($dateValue) && is_numeric($dateValue)) {
            $formattedDate = Date::excelToDateTimeObject($dateValue);
        } else {
            // If date is invalid or empty, default to the current date.
            $formattedDate = now();
        }

        $priceValue = $row['prix'] ?? '0';
        $priceValue = str_replace(',', '.', $priceValue);
        $cleanedPrice = preg_replace('/[^0-9\.]/', '', $priceValue);
        $formattedPrice = !empty($cleanedPrice) ? (float) $cleanedPrice : 0.00;

        if (empty($row['ref'])) {
            return null;
        }

        return new Product([
            'ref' => $row['ref'],
            'designation' => $row['designation'],
            'marque' => $row['marque'],
            'prix' => $formattedPrice,
            'fournisseur' => $row['fournisseur'],
            'ref_constructeur' => $row['ref_constructeur'],
            'telephone' => $row['telephone'],
            'date' => $formattedDate,
        ]);
    }

    public function batchSize(): int
    {
        return 1000;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
