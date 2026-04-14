<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product; // Assuming you have a Fournisseur model, or use Product for now
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function seed()
    {
        $dummies = [
            ['ref' => 'SEED-A', 'designation' => 'Seed Product A', 'marque' => 'Test', 'prix' => 10.00, 'fournisseur' => 'Fournisseur A'],
            ['ref' => 'SEED-B', 'designation' => 'Seed Product B', 'marque' => 'Test', 'prix' => 20.00, 'fournisseur' => 'Fournisseur B'],
            ['ref' => 'SEED-C', 'designation' => 'Seed Product C', 'marque' => 'Test', 'prix' => 30.00, 'fournisseur' => 'Fournisseur C']
        ];

        foreach ($dummies as $dummy) {
            Product::updateOrCreate(['ref' => $dummy['ref']], $dummy);
        }

        return response()->json(['message' => '✅ Seeded 3 dummy products + fournisseurs. Check /api/fournisseurs']);
    }
    public function index()
    {
        // Get unique fournisseurs from products + dummy id
        $fournisseurs = Product::select('fournisseur as nom')
            ->whereNotNull('fournisseur')
            ->where('fournisseur', '!=', '')
            ->distinct()
            ->orderBy('fournisseur')
            ->get();

        $fournisseurs = $fournisseurs->values()->map(function ($item, $index) {
            $item->id = $index + 1; // Stable numeric ID
            return $item;
        });

        return response()->json($fournisseurs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:255'
        ]);

        // Add to products table for persistence
        Product::updateOrCreate(
            ['fournisseur' => $request->nom],
            ['fournisseur' => $request->nom]
        );

        return response()->json([
            'id' => count($this->getFournisseursBase()) + 1, // Next ID
            'nom' => $request->nom
        ]);
    }

    private function getFournisseursBase()
    {
        return Product::select('fournisseur as nom')
            ->whereNotNull('fournisseur')
            ->where('fournisseur', '!=', '')
            ->distinct()
            ->orderBy('fournisseur')
            ->get();
    }
}

