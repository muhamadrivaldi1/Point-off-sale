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
        Schema::table('transactions', function (Blueprint $table) {
            // Kita letakkan setelah kolom 'status' karena 'notes' tidak terlihat di DB Anda
            $table->date('due_date')->nullable()->after('status')->comment('Jatuh tempo kredit');
            $table->string('debtor_name', 150)->nullable()->after('due_date')->comment('Nama peminjam');
            $table->string('debtor_phone', 30)->nullable()->after('debtor_name')->comment('No. telepon');
            $table->string('payment_plan', 20)->nullable()->after('debtor_phone')->comment('cash/transfer/qris/cicilan');
            $table->unsignedTinyInteger('installment_count')->nullable()->after('payment_plan')->comment('Jumlah cicilan');
            $table->text('kredit_notes')->nullable()->after('installment_count')->comment('Catatan khusus kredit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'due_date', 
                'debtor_name', 
                'debtor_phone', 
                'payment_plan', 
                'installment_count', 
                'kredit_notes'
            ]);
        });
    }
};