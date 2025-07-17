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
        Schema::table('sale_items', function (Blueprint $table) {
            // Re-add discount column if it doesn't exist
            if (!Schema::hasColumn('sale_items', 'discount')) {
                $table->decimal('discount', 5, 2)->nullable()->default(0)->after('unit_price');
            }
            // Add barcode column
            $table->string('barcode', 255)->nullable()->after('total_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            // Drop columns in reverse order of creation
            $table->dropColumn('barcode');
            if (Schema::hasColumn('sale_items', 'discount')) {
                $table->dropColumn('discount');
            }
        });
    }
};
