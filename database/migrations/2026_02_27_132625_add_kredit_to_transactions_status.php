<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah 'kredit' ke enum status
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending','paid','kredit') NOT NULL DEFAULT 'pending'");
        
        // Jika payment_method juga enum, tambahkan 'kredit' di sana juga
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash','transfer','qris','kredit') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE transactions MODIFY COLUMN status ENUM('pending','paid') NOT NULL DEFAULT 'pending'");
        DB::statement("ALTER TABLE transactions MODIFY COLUMN payment_method ENUM('cash','transfer','qris') NULL");
    }
};