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
        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enquiry_id')->constrained()->onDelete('cascade');
            $table->date('follow_up_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['new', 'in_progress', 'resolved', 'closed'])->default('in_progress');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Manager who added the follow-up
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('follow_ups');
    }
};
