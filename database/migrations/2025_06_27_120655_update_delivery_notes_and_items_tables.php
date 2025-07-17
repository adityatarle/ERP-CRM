<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Update delivery_notes table
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_notes', 'ref_no')) {
                $table->string('ref_no', 255)->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('delivery_notes', 'purchase_number')) {
                $table->string('purchase_number', 255)->nullable()->after('ref_no');
            }
            if (!Schema::hasColumn('delivery_notes', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('purchase_number');
            }
            if (!Schema::hasColumn('delivery_notes', 'description')) {
                $table->text('description')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('delivery_notes', 'gst_type')) {
                $table->enum('gst_type', ['CGST', 'SGST', 'IGST'])->nullable()->after('description');
            }
            if (!Schema::hasColumn('delivery_notes', 'cgst')) {
                $table->decimal('cgst', 5, 2)->nullable()->after('gst_type');
            }
            if (!Schema::hasColumn('delivery_notes', 'sgst')) {
                $table->decimal('sgst', 5, 2)->nullable()->after('cgst');
            }
            if (!Schema::hasColumn('delivery_notes', 'igst')) {
                $table->decimal('igst', 5, 2)->nullable()->after('sgst');
            }
        });

        // Populate data for existing records
        DB::table('delivery_notes')
            ->whereNull('purchase_number')
            ->orWhere('purchase_number', '')
            ->update([
                'purchase_number' => DB::raw('CONCAT("PO-", id)'),
            ]);

        DB::table('delivery_notes')
            ->whereNull('purchase_date')
            ->update([
                'purchase_date' => DB::raw('COALESCE(created_at, NOW())'),
            ]);

        DB::table('delivery_notes')
            ->whereNull('gst_type')
            ->update([
                'gst_type' => 'CGST',
            ]);

        // Alter columns to NOT NULL
        Schema::table('delivery_notes', function (Blueprint $table) {
            if (Schema::hasColumn('delivery_notes', 'purchase_number')) {
                $table->string('purchase_number', 255)->nullable(false)->change();
            }
            if (Schema::hasColumn('delivery_notes', 'purchase_date')) {
                $table->date('purchase_date')->nullable(false)->change();
            }
            if (Schema::hasColumn('delivery_notes', 'gst_type')) {
                $table->enum('gst_type', ['CGST', 'SGST', 'IGST'])->nullable(false)->change();
            }
        });

        // Update delivery_note_items table
        Schema::table('delivery_note_items', function (Blueprint $table) {
            if (!Schema::hasColumn('delivery_note_items', 'discount')) {
                $table->decimal('discount', 5, 2)->default(0)->after('price');
            }
            if (!Schema::hasColumn('delivery_note_items', 'itemcode')) {
                $table->string('itemcode', 255)->nullable()->after('discount');
            }
            if (!Schema::hasColumn('delivery_note_items', 'secondary_itemcode')) {
                $table->string('secondary_itemcode', 255)->nullable()->after('itemcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Rollback delivery_notes table changes
        Schema::table('delivery_notes', function (Blueprint $table) {
            $columns = ['ref_no', 'purchase_number', 'purchase_date', 'description', 'gst_type', 'cgst', 'sgst', 'igst'];
            $existingColumns = array_filter($columns, fn($column) => Schema::hasColumn('delivery_notes', $column));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });

        // Rollback delivery_note_items table changes
        Schema::table('delivery_note_items', function (Blueprint $table) {
            $columns = ['discount', 'itemcode', 'secondary_itemcode'];
            $existingColumns = array_filter($columns, fn($column) => Schema::hasColumn('delivery_note_items', $column));
            if (!empty($existingColumns)) {
                $table->dropColumn($existingColumns);
            }
        });
    }
};