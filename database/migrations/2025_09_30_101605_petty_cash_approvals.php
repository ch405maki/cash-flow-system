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
        Schema::create('petty_cash_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_id')->constrained('petty_cash')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_approvals');
    }
};
