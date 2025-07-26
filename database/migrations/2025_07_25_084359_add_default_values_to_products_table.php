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
        Schema::table('products', function (Blueprint $table) {
            // Set a default value for numeric columns
            $table->decimal('price', 8, 2)->default(0.00)->change();
            $table->string('item_code')->nullable()->change(); // Make item_code optional (nullable)
            $table->integer('stock')->default(0)->change();
            $table->integer('pstock')->default(0)->change();
            $table->integer('qty')->default(0)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is the reverse operation, in case you need to undo it
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(null)->change();
            $table->string('item_code')->nullable(false)->change();
            $table->integer('stock')->default(null)->change();
            $table->integer('pstock')->default(null)->change();
            $table->integer('qty')->default(null)->change();
        });
    }
};