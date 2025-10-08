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
        // petty_cash table
        Schema::create('petty_cash', function (Blueprint $table) {
            $table->id();
            $table->string('pcv_no')->unique();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('paid_to');
            $table->string('status')->default('draft');
            $table->date('date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        // petty_cash_items table
        Schema::create('petty_cash_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_id')->constrained('petty_cash')->onDelete('cascade');
            $table->string('particulars');
            $table->string('type');
            $table->date('date');
            $table->date('liquidation_for_date')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('receipt')->nullable();
            $table->timestamps();
        });

        // distribution_expenses table
        Schema::create('distribution_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_id')->constrained('petty_cash')->onDelete('cascade');
            $table->string('account_name');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->foreignId('prepared_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('audited_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distribution_expenses');
        Schema::dropIfExists('petty_cash_items');
        Schema::dropIfExists('petty_cash');
    }
};
