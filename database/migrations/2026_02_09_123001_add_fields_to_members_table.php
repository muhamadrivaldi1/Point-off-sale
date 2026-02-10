<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->text('address')->nullable()->after('phone');
            $table->enum('level', ['Basic','Silver','Gold'])->default('Basic')->after('address');
            $table->decimal('discount', 5, 2)->default(0)->after('level');
            $table->decimal('total_spent', 15, 2)->default(0)->after('discount');
            $table->enum('status', ['aktif','nonaktif'])->default('aktif')->after('total_spent');
        });
    }

    public function down(): void
    {
        Schema::table('members', function (Blueprint $table) {
            $table->dropColumn(['address', 'level', 'discount', 'total_spent', 'status']);
        });
    }
};
