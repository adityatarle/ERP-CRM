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
        Schema::table('purchase_orders', function (Blueprint $table) {
            // Add the new column. It's nullable because it's optional.
            // Place it after the 'order_date' column for logical grouping.
            $table->string('customer_name')->nullable()->after('order_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn('customer_name');
        });
    }
};
