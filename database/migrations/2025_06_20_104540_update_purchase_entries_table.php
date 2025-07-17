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
            // Add missing columns if they don't exist
            if (!Schema::hasColumn('purchase_entries', 'purchase_number')) {
                $table->string('purchase_number')->after('id');
            }
            if (!Schema::hasColumn('purchase_entries', 'purchase_date')) {
                $table->date('purchase_date')->nullable()->after('purchase_number');
            }
            if (!Schema::hasColumn('purchase_entries', 'invoice_number')) {
                $table->string('invoice_number')->nullable()->after('purchase_date');
            }
            if (!Schema::hasColumn('purchase_entries', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('invoice_number');
            }
            if (!Schema::hasColumn('purchase_entries', 'party_id')) {
                $table->unsignedBigInteger('party_id')->after('invoice_date');
            }
            if (!Schema::hasColumn('purchase_entries', 'purchase_order_id')) {
                $table->unsignedBigInteger('purchase_order_id')->nullable()->after('party_id');
            }
            if (!Schema::hasColumn('purchase_entries', 'note')) {
                $table->text('note')->nullable()->after('purchase_order_id');
            }
            if (!Schema::hasColumn('purchase_entries', 'gst_amount')) {
                $table->decimal('gst_amount', 15, 2)->nullable()->default(0.00)->after('note');
            }
            if (!Schema::hasColumn('purchase_entries', 'discount')) {
                $table->decimal('discount', 15, 2)->nullable()->default(0.00)->after('gst_amount');
            }
            if (!Schema::hasColumn('purchase_entries', 'cgst')) {
                $table->decimal('cgst', 15, 2)->nullable()->default(0.00)->after('discount');
            }
            if (!Schema::hasColumn('purchase_entries', 'sgst')) {
                $table->decimal('sgst', 15, 2)->nullable()->default(0.00)->after('cgst');
            }
            if (!Schema::hasColumn('purchase_entries', 'igst')) {
                $table->decimal('igst', 15, 2)->nullable()->default(0.00)->after('sgst');
            }

            // Check if the foreign key for party_id exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entries'
                AND COLUMN_NAME = 'party_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'parties'
            ");

            if (empty($foreignKeys) && Schema::hasColumn('purchase_entries', 'party_id')) {
                $table->foreign('party_id', 'purchase_entries_party_id_fk')->references('id')->on('parties')->onDelete('cascade');
            }

            // Check if the foreign key for purchase_order_id exists
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entries'
                AND COLUMN_NAME = 'purchase_order_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'purchase_orders'
            ");

            if (empty($foreignKeys) && Schema::hasColumn('purchase_entries', 'purchase_order_id')) {
                $table->foreign('purchase_order_id', 'purchase_entries_order_id_fk')->references('id')->on('purchase_orders')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            // Drop foreign keys if they exist
            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entries'
                AND COLUMN_NAME = 'party_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'parties'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign('purchase_entries_party_id_fk');
            }

            $foreignKeys = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                WHERE TABLE_NAME = 'purchase_entries'
                AND COLUMN_NAME = 'purchase_order_id'
                AND CONSTRAINT_SCHEMA = DATABASE()
                AND REFERENCED_TABLE_NAME = 'purchase_orders'
            ");

            if (!empty($foreignKeys)) {
                $table->dropForeign('purchase_entries_order_id_fk');
            }
            });
    }
};
