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
            // Add a simple, nullable text column to store the name.
            // 'after('party_id')' is optional, for organization.
            $table->string('contact_person')->nullable()->after('party_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('receipt_notes', function (Blueprint $table) {
            // This ensures you can undo the migration.
            $table->dropColumn('contact_person');
        });
    }
};