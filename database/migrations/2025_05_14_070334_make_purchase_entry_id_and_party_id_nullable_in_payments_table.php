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
            // Drop foreign key constraints temporarily to modify columns
            $table->dropForeign(['purchase_entry_id']);
            $table->dropForeign(['party_id']);

            // Make purchase_entry_id and party_id nullable
            $table->unsignedBigInteger('purchase_entry_id')->nullable()->change();
            $table->unsignedBigInteger('party_id')->nullable()->change();

            // Re-add foreign key constraints
            $table->foreign('purchase_entry_id')->references('id')->on('purchase_entries')->onDelete('cascade');
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop foreign key constraints
            $table->dropForeign(['purchase_entry_id']);
            $table->dropForeign(['party_id']);

            // Revert columns to NOT NULL (be cautious with existing data)
            $table->unsignedBigInteger('purchase_entry_id')->nullable(false)->change();
            $table->unsignedBigInteger('party_id')->nullable(false)->change();

            // Re-add foreign key constraints
            $table->foreign('purchase_entry_id')->references('id')->on('purchase_entries')->onDelete('cascade');
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
        });
    }
};
