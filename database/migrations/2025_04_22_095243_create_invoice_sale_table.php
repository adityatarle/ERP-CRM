<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('invoice_sale', function (Blueprint $table) {
            $table->id(); // Optional: Primary key for the pivot table
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->timestamps(); // Optional: Adds created_at and updated_at columns
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_sale');
    }
};
