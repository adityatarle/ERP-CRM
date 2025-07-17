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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('purchase_entry_items', 'purchase_entry_id')) {
                $table->unsignedBigInteger('purchase_entry_id')->after('id');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable()->after('purchase_entry_id');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'quantity')) {
                $table->integer('quantity')->default(0)->after('product_id');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->default(0.00)->after('quantity');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'total_price')) {
                $table->decimal('total_price', 15, 2)->default(0.00)->after('unit_price');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'gst_rate')) {
                $table->decimal('gst_rate', 5, 2)->nullable()->after('total_price');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'gst_type')) {
                $table->string('gst_type')->nullable()->after('gst_rate');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'discount')) {
                $table->decimal('discount', 15, 2)->nullable()->default(0.00)->after('gst_type');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'status')) {
                $table->string('status')->nullable()->after('discount');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'item_code')) {
                $table->string('item_code')->nullable()->after('status');
            }
            if (!Schema::hasColumn('purchase_entry_items', 'hsn')) {
                $table->string('hsn')->nullable()->after('item_code');
            }

            // Check if the foreign key for purchase_entry_id exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entry_items'
                AND COLUMN_NAME = 'purchase_entry_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'purchase_entries'
            ");

            if (empty($foreignKeys) && Schema::hasColumn('purchase_entry_items', 'purchase_entry_id')) {
                $table->foreign('purchase_entry_id', 'purchase_entry_items_entry_id_fk')->references('id')->on('purchase_entries')->onDelete('cascade');
            }

            // Check if the foreign key for product_id exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entry_items'
                AND COLUMN_NAME = 'product_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'products'
            ");

            if (empty($foreignKeys) && Schema::hasColumn('purchase_entry_items', 'product_id')) {
                $table->foreign('product_id', 'purchase_entry_items_product_id_fk')->references('id')->on('products')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('purchase_entry_items', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entry_items'
                AND COLUMN_NAME = 'purchase_entry_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'purchase_entries'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign('purchase_entry_items_entry_id_fk');
            }

            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entry_items'
                AND COLUMN_NAME = 'product_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'products'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign('purchase_entry_items_product_id_fk');
            }
        });
    }
};
