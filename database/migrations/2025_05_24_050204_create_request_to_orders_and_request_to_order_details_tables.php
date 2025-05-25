<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('request_to_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->string('order_no');
            $table->date('order_date');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('request_to_order_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_to_order_id')->constrained('request_to_orders');
            $table->foreignId('request_id')->constrained('requests');
            $table->foreignId('department_id')->constrained('departments');
            $table->decimal('quantity', 10, 2);
            $table->string('unit');
            $table->text('item_description');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_to_order_details');
        Schema::dropIfExists('request_to_orders');
    }
};
