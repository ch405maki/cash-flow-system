<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('request_to_order_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('request_to_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('request_to_order_detail_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_released');
            $table->date('release_date');
            $table->text('notes')->nullable();
            $table->foreignId('released_by')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('request_to_order_releases');
    }
};
