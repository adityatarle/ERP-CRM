<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentsTable extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('party_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
}