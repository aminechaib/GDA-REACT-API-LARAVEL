<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Product; // Make sure Product model is imported
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of the resource for AG Grid.
     * Handles pagination, sorting, and filtering.
     */
    public function index(Request $request)
    {
        $page = $request->input('page', 1);
        $rowsPerPage = $request->input('rowsPerPage', 100);
        $sortBy = $request->input('sortBy', 'id');
        $sortType = $request->input('sortType', 'asc');

        $query = \App\Models\Product::query();

        // --- START: ADD THIS SEARCH LOGIC ---
        if ($request->has('searchValue') && !empty($request->input('searchValue'))) {
            $searchValue = $request->input('searchValue');

            // This will search for the term in any of these columns
            $query->where(function ($q) use ($searchValue) {
                $q->where('ref', 'LIKE', "%{$searchValue}%")
                    ->orWhere('designation', 'LIKE', "%{$searchValue}%")
                    ->orWhere('marque', 'LIKE', "%{$searchValue}%")
                    ->orWhere('fournisseur', 'LIKE', "%{$searchValue}%")
                    ->orWhere('ref_constructeur', 'LIKE', "%{$searchValue}%");
            });
        }
        // --- END: SEARCH LOGIC ---

        // Get total count *after* applying search filters
        $total = $query->count();

        // Apply sorting and pagination
        $products = $query->orderBy($sortBy, $sortType)
            ->skip(($page - 1) * $rowsPerPage)
            ->take($rowsPerPage)
            ->get();

        return response()->json([
            'rows' => $products,
            'total' => $total,
        ]);
    }


    /**
     * Handles Excel import with column mapping from frontend.
     */
    public function importMapping(Request $request)
    {
        $request->validate([
            'data' => 'required|array|min:1',
            'mapping' => 'required|array',
            'fournisseur_id' => 'nullable|integer',
            'new_fournisseur' => 'nullable|string|max:255',
            'date' => 'nullable|date'
        ]);

        $data = $request->data;
        $mapping = $request->mapping;
        $fournisseurId = $request->fournisseur_id;
        $newFournisseur = $request->new_fournisseur;
        $date = $request->date ?: now()->format('Y-m-d');

        // Get fournisseur name from ID (match controller logic)
        $fournisseurName = $newFournisseur;
        if (!$fournisseurName && $fournisseurId) {
            $allFournisseurs = Product::select('fournisseur as nom')
                ->whereNotNull('fournisseur')
                ->where('fournisseur', '!=', '')
                ->distinct()
                ->orderBy('fournisseur')
                ->get()
                ->values();

            $selected = $allFournisseurs->get($fournisseurId - 1); // Index-based ID
            $fournisseurName = $selected?->nom ?? '';
        }

        // Transform data based on mapping
        $transformedData = [];
        foreach ($data as $row) {
            $transformedRow = [
                'ref' => $row[$mapping['ref']] ?? '',
                'designation' => $row[$mapping['designation']] ?? '',
                'marque' => $row[$mapping['marque']] ?? '',
                'prix' => floatval(str_replace(',', '.', $row[$mapping['prix']] ?? 0)),
                'fournisseur' => $fournisseurName,
                'date' => $date,
            ];

            if (!empty($transformedRow['ref'])) {
                $transformedData[] = $transformedRow;
            }
        }

        try {
            foreach ($transformedData as $row) {
                Product::updateOrCreate(
                    ['ref' => $row['ref']],
                    $row
                );
            }

            return response()->json([
                'message' => count($transformedData) . ' products imported/updated successfully!',
                'imported' => count($transformedData)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Import failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function simpleIndex(Request $request)
    {
        $page = $request->input('page', 1);
        $rowsPerPage = $request->input('rowsPerPage', 100);
        $sortBy = $request->input('sortBy', 'id');
        $sortType = $request->input('sortType', 'asc');

        $query = \App\Models\Product::query();

        // Simple global search (we can add a search input later)
        if ($request->has('searchValue') && !empty($request->input('searchValue'))) {
            $searchValue = $request->input('searchValue');
            $query->where(function ($q) use ($searchValue) {
                $q->where('ref', 'LIKE', "%{$searchValue}%")
                    ->orWhere('designation', 'LIKE', "%{$searchValue}%")
                    ->orWhere('marque', 'LIKE', "%{$searchValue}%");
            });
        }

        // Get total count before pagination
        $total = $query->count();

        // Apply sorting and pagination
        $products = $query->orderBy($sortBy, $sortType)
            ->skip(($page - 1) * $rowsPerPage)
            ->take($rowsPerPage)
            ->get();

        return response()->json([
            'rows' => $products,
            'total' => $total,
        ]);
    }
}
