<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGstTypeToPurchaseEntryItemsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->enum('gst_type', ['CGST', 'SGST', 'IGST'])->nullable()->after('gst_rate');
        });
    }

    public function down()
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropColumn('gst_type');
        });
    }
}