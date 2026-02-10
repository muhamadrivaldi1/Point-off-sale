<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->integer('used_points')
                ->default(0)
                ->after('discount');

            $table->decimal('point_value', 12, 2)
                ->default(0)
                ->after('used_points');
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['used_points', 'point_value']);
        });
    }
};
