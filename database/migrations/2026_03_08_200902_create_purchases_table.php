<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('purchase_number')->unique(); // Contoh: PO-001
            $table->foreignId('supplier_id')->constrained();
            $table->date('purchase_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['lunas', 'hutang'])->default('hutang');
            $table->date('due_date')->nullable(); // Jatuh tempo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
