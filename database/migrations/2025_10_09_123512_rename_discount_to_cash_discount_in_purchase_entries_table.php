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
            $table->renameColumn('discount', 'cash_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
          $table->renameColumn('cash_discount', 'discount');
        });
    }
};
