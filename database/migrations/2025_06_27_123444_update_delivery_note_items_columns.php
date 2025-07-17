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
        Schema::table('delivery_note_items', function (Blueprint $table) {
            // Rename item_code to itemcode
            $table->renameColumn('item_code', 'itemcode');
            // Add secondary_itemcode if not exists
            if (!Schema::hasColumn('delivery_note_items', 'secondary_itemcode')) {
                $table->string('secondary_itemcode', 255)->nullable()->after('itemcode');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delivery_note_items', function (Blueprint $table) {
            // Rename itemcode back to item_code
            $table->renameColumn('itemcode', 'item_code');
            // Drop secondary_itemcode if exists
            if (Schema::hasColumn('delivery_note_items', 'secondary_itemcode')) {
                $table->dropColumn('secondary_itemcode');
            }
        });
    }
};