<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_to_order_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_to_order_id')->constrained('request_to_orders')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users');
            $table->string('status');
            $table->text('remarks')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_to_order_approvals');
    }
};
