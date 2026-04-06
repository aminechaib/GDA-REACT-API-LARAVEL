<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * This method defines the table schema. We add indexes to all columns
     * that will be frequently used in search, filter (WHERE), and sort (ORDER BY)
     * operations to ensure fast query execution.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Data Columns
            $table->string('ref');
            $table->text('designation');
            $table->string('marque');
            $table->decimal('prix', 10, 2); // Suitable for monetary values
            $table->string('fournisseur');
            $table->string('ref_constructeur')->nullable();
            $table->string('telephone')->nullable();
            $table->date('date');
            $table->timestamps(); // Adds created_at and updated_at

            // === Performance Indexes ===
            // Standard index for exact matches and range queries.
            $table->index('ref');
            $table->index('marque');
            $table->index('prix');
            $table->index('fournisseur');
            $table->index('ref_constructeur');
            $table->index('date');

            // For the 'designation' column, a standard index is not effective for
            // "LIKE %term%" searches. A FULLTEXT index is specifically designed
            // for fast searching within text content.
            // Note: This requires MySQL (with MyISAM or InnoDB) or PostgreSQL.
            $table->fullText('designation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * This method is executed when rolling back the migration. It simply
     * drops the table, removing all its data and structure.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
