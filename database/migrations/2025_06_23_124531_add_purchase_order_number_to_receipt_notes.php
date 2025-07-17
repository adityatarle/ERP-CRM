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
            $table->string('purchase_order_number', 255)->nullable()->after('party_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('receipt_notes', function (Blueprint $table) {
            $table->dropColumn('purchase_order_number');
        });
    }
};
