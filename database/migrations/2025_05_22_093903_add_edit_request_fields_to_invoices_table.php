<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('edit_request_status')->nullable()->after('status');
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->onDelete('set null')->after('edit_request_status');
            $table->text('unlock_reason')->nullable()->after('requested_by_id');
            $table->foreignId('unlock_decision_by_id')->nullable()->constrained('users')->onDelete('set null')->after('unlock_reason');
            $table->timestamp('unlock_decision_at')->nullable()->after('unlock_decision_by_id');
            $table->text('unlock_decision_reason')->nullable()->after('unlock_decision_at');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropForeign(['requested_by_id']);
            $table->dropForeign(['unlock_decision_by_id']);
            $table->dropColumn([
                'edit_request_status',
                'requested_by_id',
                'unlock_reason',
                'unlock_decision_by_id',
                'unlock_decision_at',
                'unlock_decision_reason',
            ]);
        });
    }
};