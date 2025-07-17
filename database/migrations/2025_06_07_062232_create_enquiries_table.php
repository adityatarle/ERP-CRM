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
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->text('details');
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('new');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Manager assigned (optional)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};
