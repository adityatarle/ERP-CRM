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
            $table->string('status')->default('pending')->after('total_price'); // 'pending' or 'received'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
