<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('canvases', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('draft');
            $table->text('note')->nullable();
            $table->text('remarks')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('canvases');
    }
};