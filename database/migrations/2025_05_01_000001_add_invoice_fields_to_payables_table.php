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
        Schema::table('payables', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('amount');
            $table->date('invoice_date')->nullable()->after('invoice_number');
            $table->index(['invoice_number']);
            $table->index(['invoice_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payables', function (Blueprint $table) {
            $table->dropIndex(['invoice_number']);
            $table->dropIndex(['invoice_date']);
            $table->dropColumn(['invoice_number', 'invoice_date']);
        });
    }
};