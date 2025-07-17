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
        Schema::table('delivery_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->nullable()->after('id'); // Add sale_id column
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('set null'); // Add foreign key constraint
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('delivery_notes', function (Blueprint $table) {
            $table->dropForeign(['sale_id']); // Drop the foreign key constraint
            $table->dropColumn('sale_id'); // Drop the sale_id column
        });
    }
};
