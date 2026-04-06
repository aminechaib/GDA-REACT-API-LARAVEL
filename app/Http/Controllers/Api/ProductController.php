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
     * Handles the Excel file upload and initiates the import process.
     */
    public function import(Request $request)
    {
        // This is the logic from Step 2
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new ProductsImport, $request->file('file'));
            return response()->json(['message' => 'Products imported successfully.'], 200);
        } catch (ValidationException $e) {
            $failures = $e->failures();
            return response()->json(['errors' => $failures], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred during import.', 'error' => $e->getMessage()], 500);
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
