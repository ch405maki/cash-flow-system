<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receivings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')
                  ->constrained('purchase_orders')
                  ->cascadeOnDelete();
            $table->foreignId('po_detail_id')
                  ->constrained('purchase_order_details')
                  ->cascadeOnDelete();
            $table->foreignId('received_by')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->integer('quantity_ordered');
            $table->integer('quantity_received');
            $table->enum('condition', ['good', 'damaged', 'incomplete'])->default('good');
            $table->date('received_date');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receivings');
    }
};