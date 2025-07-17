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
       Schema::table('receipt_notes', function (Blueprint $table) {
            // Check if unique index exists on receipt_number
            $indexes = DB::select('SHOW INDEXES FROM receipt_notes WHERE Key_name = ?', ['receipt_notes_receipt_number_unique']);
            $hasUniqueIndex = !empty($indexes);

            // Add columns only if they don't exist
            if (!Schema::hasColumn('receipt_notes', 'gst_amount')) {
                $table->decimal('gst_amount', 15, 2)->nullable()->default(0.00)->after('note');
            }
            if (!Schema::hasColumn('receipt_notes', 'discount')) {
                $table->decimal('discount', 15, 2)->nullable()->default(0.00)->after('gst_amount');
            }
            if (!Schema::hasColumn('receipt_notes', 'invoice_number')) {
                $table->string('invoice_number', 255)->nullable()->unique()->after('purchase_order_number');
            }
            if (!Schema::hasColumn('receipt_notes', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('invoice_number');
            }

            // Ensure existing columns match purchase_entries structure
            if (Schema::hasColumn('receipt_notes', 'receipt_number') && !$hasUniqueIndex) {
                $table->string('receipt_number', 255)->unique()->change();
            } elseif (Schema::hasColumn('receipt_notes', 'receipt_number')) {
                $table->string('receipt_number', 255)->change();
            }
            if (Schema::hasColumn('receipt_notes', 'receipt_date')) {
                $table->date('receipt_date')->change();
            }
            if (Schema::hasColumn('receipt_notes', 'party_id')) {
                $table->unsignedBigInteger('party_id')->change();
            }
            if (Schema::hasColumn('receipt_notes', 'note')) {
                $table->text('note')->nullable()->change();
            }
            if (Schema::hasColumn('receipt_notes', 'purchase_order_number')) {
                $table->string('purchase_order_number', 255)->nullable()->change();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
          Schema::table('receipt_notes', function (Blueprint $table) {
            if (Schema::hasColumn('receipt_notes', 'gst_amount')) {
                $table->dropColumn('gst_amount');
            }
            if (Schema::hasColumn('receipt_notes', 'discount')) {
                $table->dropColumn('discount');
            }
            if (Schema::hasColumn('receipt_notes', 'invoice_number')) {
                $table->dropColumn('invoice_number');
            }
            if (Schema::hasColumn('receipt_notes', 'invoice_date')) {
                $table->dropColumn('invoice_date');
            }
        });
    }
};
