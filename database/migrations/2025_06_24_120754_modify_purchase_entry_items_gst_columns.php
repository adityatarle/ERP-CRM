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
       Schema::table('purchase_entry_items', function (Blueprint $table) {
            // Ensure cgst_rate, sgst_rate, igst_rate exist
            if (!Schema::hasColumn('purchase_entry_items', 'cgst_rate')) {
                $table->decimal('cgst_rate', 8, 2)->nullable()->after('discount');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'sgst_rate')) {
                $table->decimal('sgst_rate', 8, 2)->nullable()->after('cgst_rate');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'igst_rate')) {
                $table->decimal('igst_rate', 8, 2)->nullable()->after('sgst_rate');
            }
            // Drop cgst, sgst, igst if they exist
            if (Schema::hasColumn('purchase_entry_items', 'cgst')) {
                $table->dropColumn('cgst');
            }
            if (Schema::hasColumn('purchase_entry_items', 'sgst')) {
                $table->dropColumn('sgst');
            }
            if (Schema::hasColumn('purchase_entry_items', 'igst')) {
                $table->dropColumn('igst');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->decimal('cgst', 15, 2)->nullable()->after('igst_rate');
            $table->decimal('sgst', 15, 2)->nullable()->after('cgst');
            $table->decimal('igst', 15, 2)->nullable()->after('sgst');
            $table->dropColumn(['cgst_rate', 'sgst_rate', 'igst_rate']);
        });
    }
};
