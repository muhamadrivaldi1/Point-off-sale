<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {

            $table->string('return_number')->nullable()->after('id');

            $table->unsignedBigInteger('approved_by')->nullable()->after('user_id');

            $table->timestamp('approved_at')->nullable()->after('approved_by');

            $table->string('reference')->nullable()->after('approved_at');

            $table->text('note')->nullable()->after('reference');
        });
    }

    public function down(): void
    {
        Schema::table('purchase_returns', function (Blueprint $table) {

            $table->dropColumn([
                'return_number',
                'approved_by',
                'approved_at',
                'reference',
                'note'
            ]);
        });
    }
};
