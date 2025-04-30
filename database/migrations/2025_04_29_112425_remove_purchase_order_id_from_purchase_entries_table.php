<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemovePurchaseOrderIdFromPurchaseEntriesTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->dropForeign(['purchase_order_id']);
            $table->dropColumn('purchase_order_id');
        });
    }

    public function down()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
        });
    }
}