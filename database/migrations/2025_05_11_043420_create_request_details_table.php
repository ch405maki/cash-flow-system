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
        Schema::create('request_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_id')->constrained('requests');
            $table->decimal('quantity', 10, 2);
            $table->decimal('released_quantity', 10, 2)->default(0);
            $table->string('unit');
            $table->text('item_description');
            $table->string('tagging')->nullable();
            $table->dateTime('released_at')->nullable();
            $table->string('tracking_status')->nullable()->after('tagging');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_details');
    }
};
