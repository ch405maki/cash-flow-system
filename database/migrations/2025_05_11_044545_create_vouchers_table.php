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
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->nullable()->constrained('purchase_orders');
            $table->string('voucher_no');
            $table->date('voucher_date');
            $table->date('issue_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('type');
            $table->string('payee');
            $table->string('check_no')->nullable();
            $table->date('check_date')->nullable();
            $table->decimal('check_amount', 12, 2);
            $table->string('check_payable_to');
            $table->date('delivery_date')->nullable();
            $table->text('purpose');
            $table->string('status');
            $table->foreignId('user_id')->constrained('users');
            $table->text('remarks')->nullable();
            $table->string('receipt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vouchers');
    }
};
