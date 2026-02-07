<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->enum('status', ['pending', 'approved'])->default('pending')->after('user_id');
            $table->foreignId('approved_by')->nullable()->constrained('users')->after('status')->nullOnDelete();
            $table->timestamp('approved_at')->nullable()->after('approved_by');
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropColumn(['status', 'approved_by', 'approved_at']);
        });
    }
};
