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
       Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('cgst', 5, 2)->nullable()->after('gst_type');
            $table->decimal('sgst', 5, 2)->nullable()->after('cgst');
            $table->decimal('igst', 5, 2)->nullable()->after('sgst');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('cgst', 5, 2)->nullable()->after('gst_type');
            $table->decimal('sgst', 5, 2)->nullable()->after('cgst');
            $table->decimal('igst', 5, 2)->nullable()->after('sgst');
        });
    }
};
