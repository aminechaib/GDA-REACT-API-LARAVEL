<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'designation',
        'marque',
        'prix',
        'fournisseur',
        'ref_constructeur',
        'telephone',
        'date',
    ];

    /**
     * The attributes that should be cast.
     *
     * This ensures data types are consistent, which is important for
     * calculations, validation, and API responses.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'prix' => 'decimal:2',
        'date' => 'date',
    ];
}
