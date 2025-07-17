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
         Schema::table('purchase_entries', function (Blueprint $table) {
            $table->dropColumn(['cgst', 'sgst', 'igst']);
        });

        // Add cgst, sgst, igst to purchase_entry_items table
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->decimal('cgst', 15, 2)->nullable()->after('gst_rate');
            $table->decimal('sgst', 15, 2)->nullable()->after('cgst');
            $table->decimal('igst', 15, 2)->nullable()->after('sgst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('purchase_entries', function (Blueprint $table) {
            $table->decimal('cgst', 15, 2)->nullable();
            $table->decimal('sgst', 15, 2)->nullable();
            $table->decimal('igst', 15, 2)->nullable();
        });

        // Revert changes: Remove cgst, sgst, igst from purchase_entry_items
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropColumn(['cgst', 'sgst', 'igst']);
        });
    }
};
