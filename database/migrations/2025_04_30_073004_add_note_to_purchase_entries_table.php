<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoteToPurchaseEntriesTable extends Migration
{
    public function up()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->text('note')->nullable()->after('party_id');
        });
    }

    public function down()
    {
        Schema::table('purchase_entries', function (Blueprint $table) {
            $table->dropColumn('note');
        });
    }
}