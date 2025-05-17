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
        Schema::create('voucher_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('voucher_id')->constrained('vouchers');
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('charging_tag');
            $table->decimal('hours', 8, 2)->nullable();
            $table->decimal('rate', 10, 2)->nullable();
            $table->foreignId('account_id')->constrained('accounts');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('voucher_details');
    }
};
