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
        Schema::table('receipt_note_items', function (Blueprint $table) {
            // Add columns only if they don't exist
            if (!Schema::hasColumn('receipt_note_items', 'cgst_rate')) {
                $table->decimal('cgst_rate', 8, 2)->nullable()->after('unit_price');
            }
            if (!Schema::hasColumn('receipt_note_items', 'sgst_rate')) {
                $table->decimal('sgst_rate', 8, 2)->nullable()->after('cgst_rate');
            }
            if (!Schema::hasColumn('receipt_note_items', 'igst_rate')) {
                $table->decimal('igst_rate', 8, 2)->nullable()->after('sgst_rate');
            }
            if (!Schema::hasColumn('receipt_note_items', 'item_code')) {
                $table->string('item_code', 255)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('receipt_note_items', 'hsn')) {
                $table->string('hsn', 255)->nullable()->after('item_code');
            }
            if (!Schema::hasColumn('receipt_note_items', 'status')) {
                $table->string('status', 255)->default('pending')->after('hsn');
            }

            // Ensure existing columns match purchase_entry_items structure
            if (Schema::hasColumn('receipt_note_items', 'receipt_note_id')) {
                $table->unsignedBigInteger('receipt_note_id')->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'quantity')) {
                $table->integer('quantity')->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'unit_price')) {
                $table->decimal('unit_price', 8, 2)->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'discount')) {
                $table->decimal('discount', 5, 2)->nullable()->default(0.00)->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'total_price')) {
                $table->decimal('total_price', 8, 2)->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->nullable()->default(0.00)->change();
            }
            if (Schema::hasColumn('receipt_note_items', 'gst_type')) {
                $table->enum('gst_type', ['CGST', 'SGST', 'IGST'])->nullable()->change();
            }
        });

        // Populate cgst_rate, sgst_rate, and igst_rate based on existing gst_type and gst_rate
        if (Schema::hasColumn('receipt_note_items', 'cgst_rate')) {
            DB::statement("UPDATE receipt_note_items SET cgst_rate = CASE WHEN gst_type = 'CGST' THEN gst_rate / 2 ELSE NULL END WHERE cgst_rate IS NULL");
        }
        if (Schema::hasColumn('receipt_note_items', 'sgst_rate')) {
            DB::statement("UPDATE receipt_note_items SET sgst_rate = CASE WHEN gst_type = 'CGST' THEN gst_rate / 2 ELSE NULL END WHERE sgst_rate IS NULL");
        }
        if (Schema::hasColumn('receipt_note_items', 'igst_rate')) {
            DB::statement("UPDATE receipt_note_items SET igst_rate = CASE WHEN gst_type = 'IGST' THEN gst_rate ELSE NULL END WHERE igst_rate IS NULL");
        }
    
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
      Schema::table('receipt_note_items', function (Blueprint $table) {
            if (Schema::hasColumn('receipt_note_items', 'cgst_rate')) {
                $table->dropColumn('cgst_rate');
            }
            if (Schema::hasColumn('receipt_note_items', 'sgst_rate')) {
                $table->dropColumn('sgst_rate');
            }
            if (Schema::hasColumn('receipt_note_items', 'igst_rate')) {
                $table->dropColumn('igst_rate');
            }
            if (Schema::hasColumn('receipt_note_items', 'item_code')) {
                $table->dropColumn('item_code');
            }
            if (Schema::hasColumn('receipt_note_items', 'hsn')) {
                $table->dropColumn('hsn');
            }
            if (Schema::hasColumn('receipt_note_items', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
