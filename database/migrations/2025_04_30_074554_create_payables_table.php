<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayablesTable extends Migration
{
    public function up()
    {
        Schema::create('payables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->boolean('is_paid')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payables');
    }
}