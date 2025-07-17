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
        Schema::create('receipt_notes', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_number')->unique(); // Unique receipt note number (e.g., RN-XXXXXXXX)
            $table->date('receipt_date'); // Date when material is received
            $table->unsignedBigInteger('party_id'); // Foreign key to parties table
            $table->text('note')->nullable(); // Any additional notes
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('party_id')->references('id')->on('parties')->onDelete('cascade');
        });

        // Create a table for receipt note items (similar to purchase_entry_items)
        Schema::create('receipt_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('receipt_note_id'); // Foreign key to receipt_notes table
            $table->unsignedBigInteger('product_id'); // Foreign key to products table
            $table->integer('quantity'); // Quantity received
            $table->decimal('unit_price', 15, 2); // Unit price (might be estimated if no invoice)
            $table->decimal('total_price', 15, 2); // Total price (quantity * unit_price, no GST for now)
            $table->decimal('gst_rate', 5, 2)->nullable(); // GST rate (optional, might be added later)
            $table->string('gst_type')->nullable(); // GST type (CGST, SGST, IGST)
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('receipt_note_id')->references('id')->on('receipt_notes')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('receipt_note_items');
        Schema::dropIfExists('receipt_notes');
    }
};
