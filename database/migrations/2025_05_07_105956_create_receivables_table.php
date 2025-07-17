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
        Schema::create('receivables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sale_id')->index(); // Foreign key to sales table
            $table->unsignedBigInteger('customer_id')->index(); // Foreign key to customers table
            $table->decimal('amount', 15, 2)->default(0.00); // Receivable amount
            $table->integer('credit_days')->nullable(); // Credit days for this receivable
            $table->boolean('is_paid')->default(false); // Payment status
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receivables');
    }
};
