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
            $table->string('purchase_number')->nullable()->after('description'); // Add purchase_number column
            $table->date('purchase_date')->nullable()->after('purchase_number'); // Add purchase_date column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('purchase_number');
            $table->dropColumn('purchase_date');
        });
    }
};
