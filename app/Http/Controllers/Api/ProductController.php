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
        // This is the logic from Step 3
        $request->validate([
            'startRow' => 'required|integer',
            'endRow' => 'required|integer',
            'sortModel' => 'nullable|array',
            'filterModel' => 'nullable|array',
        ]);

        $startRow = $request->input('startRow');
        $endRow = $request->input('endRow');
        $limit = $endRow - $startRow;

        $query = Product::query();

        // Handle Column Filters (filterModel)
        if ($request->has('filterModel')) {
            foreach ($request->input('filterModel') as $field => $filter) {
                if ($filter['filterType'] == 'text') {
                    $query->where($field, 'LIKE', '%' . $filter['filter'] . '%');
                }
            }
        }

        // Handle Sorting (sortModel)
        if ($request->has('sortModel') && !empty($request->input('sortModel'))) {
            foreach ($request->input('sortModel') as $sort) {
                $query->orderBy($sort['colId'], $sort['sort']);
            }
        } else {
            $query->orderBy('id', 'asc');
        }

        $totalRows = $query->count();
        $products = $query->offset($startRow)->limit($limit)->get();

        return response()->json([
            'rows' => $products,
            'lastRow' => $totalRows,
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
}
