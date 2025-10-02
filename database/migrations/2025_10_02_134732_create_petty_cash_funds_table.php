<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petty_cash_funds', function (Blueprint $table) {
            $table->id();
            
            // Link to user (custodian of the fund)
            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Link to department
            $table->foreignId('department_id')
                  ->nullable()
                  ->constrained('departments')
                  ->onDelete('set null');

            // The available fund balance
            $table->decimal('fund_amount', 15, 2)->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petty_cash_funds');
    }
};
