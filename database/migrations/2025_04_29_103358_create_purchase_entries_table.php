<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_entries', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique();
            $table->date('purchase_date');
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->foreignId('party_id')->constrained()->onDelete('cascade');
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('purchase_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->decimal('total_price', 8, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_entry_items');
        Schema::dropIfExists('purchase_entries');
    }
}