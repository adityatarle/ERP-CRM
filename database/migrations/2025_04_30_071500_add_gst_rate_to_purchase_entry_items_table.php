<?php
 
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGstRateToPurchaseEntryItemsTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->decimal('gst_rate', 5, 2)->nullable()->default(0.00)->after('total_price');
        });
    }

    public function down()
    {
        Schema::table('purchase_entry_items', function (Blueprint $table) {
            $table->dropColumn('gst_rate');
        });
    }
}