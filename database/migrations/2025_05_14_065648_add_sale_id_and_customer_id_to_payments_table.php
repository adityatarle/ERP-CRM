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
        Schema::table('payments', function (Blueprint $table) {
            $table->unsignedBigInteger('sale_id')->nullable()->after('party_id');
            $table->unsignedBigInteger('customer_id')->nullable()->after('sale_id');

            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['customer_id']);
            $table->dropColumn(['sale_id', 'customer_id']);
        });
    }
};
