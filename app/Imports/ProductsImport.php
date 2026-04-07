<?php

namespace App\Imports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
// We have removed SkipsOnError and SkipsErrors
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;

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

        if (!empty($dateValue)) {
            $trimmedValue = trim((string) $dateValue);
            // Handle M/D/YYYY format like "9/2/2026"
            if (preg_match('/^(\\d{1,2})\\/(\\d{1,2})\\/(\\d{4})$/', $trimmedValue, $matches)) {
                $month = $matches[1];
                $day = $matches[2];
                $year = $matches[3];
                $parsedDate = Carbon::createFromFormat('n/j/Y', $trimmedValue);
                if ($parsedDate) {
                    $formattedDate = $parsedDate->format('Y-m-d');
                } else {
                    $formattedDate = now()->format('Y-m-d');
                }
            } else {
                try {
                    $parsedDate = Carbon::parse($trimmedValue);
                    $formattedDate = $parsedDate->format('Y-m-d');
                } catch (\Exception $e) {
                    if (is_numeric($trimmedValue)) {
                        $excelDateTime = Date::excelToDateTimeObject($trimmedValue);
                        $formattedDate = $excelDateTime->format('Y-m-d');
                    } else {
                        $formattedDate = now()->format('Y-m-d');
                    }
                }
            }
        } else {
            $formattedDate = null; // Allow nullable
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
