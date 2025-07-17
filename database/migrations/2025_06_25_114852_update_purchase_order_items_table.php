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
        Schema::table('purchase_order_items', function (Blueprint $table) {
            // Add CGST, SGST, IGST columns
            $table->decimal('cgst', 5, 2)->default(0.00)->after('discount');
            $table->decimal('sgst', 5, 2)->default(0.00)->after('cgst');
            $table->decimal('igst', 5, 2)->default(0.00)->after('sgst');
            // Drop GST column
            $table->dropColumn('gst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('purchase_order_items', function (Blueprint $table) {
            // Revert changes: drop CGST, SGST, IGST and add GST back
            $table->dropColumn(['cgst', 'sgst', 'igst']);
            $table->decimal('gst', 5, 2)->default(0.00)->after('discount');
        });
    }
};
