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
            $table->foreignId('po_id')->unique()->constrained('purchase_orders');
            $table->string('voucher_no');
            $table->date('voucher_date');
            $table->date('issue_date');
            $table->date('payment_date');
            $table->string('type');
            $table->string('payee');
            $table->string('check_no')->nullable();
            $table->date('check_date');
            $table->decimal('check_amount', 12, 2);
            $table->string('check_payable_to');
            $table->date('delivery_date');
            $table->text('purpose');
            $table->string('status');
            $table->foreignId('user_id')->constrained('users');
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
