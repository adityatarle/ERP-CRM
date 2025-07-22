<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // Add a nullable string column for the contact person's name
            // Placing it after 'customer_id' for good organization
            $table->string('contact_person')->nullable()->after('customer_id');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            // This allows the migration to be reversed
            $table->dropColumn('contact_person');
        });
    }
};