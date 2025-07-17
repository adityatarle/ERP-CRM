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
       Schema::create('delivery_notes', function (Blueprint $table) {
        $table->id();
        $table->string('delivery_note_number')->unique();
        $table->foreignId('customer_id')->constrained()->onDelete('restrict');
        $table->date('delivery_date');
        $table->text('notes')->nullable();
        $table->boolean('is_invoiced')->default(false);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('delivery_notes');
    }
};
