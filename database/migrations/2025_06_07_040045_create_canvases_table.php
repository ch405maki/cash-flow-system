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
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('note')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('request_to_order_id')->nullable()->constrained('request_to_orders');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('canvas_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('canvas_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users'); // The user who approved/audited
            $table->string('role'); // 'auditor' or 'executive_director'
            $table->text('comments')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('canvas_selected_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('canvas_id')->constrained()->onDelete('cascade');
            $table->foreignId('canvas_file_id')->constrained('canvas_files')->onDelete('cascade');
            $table->foreignId('approval_id')->constrained('canvas_approvals')->onDelete('cascade');
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('canvas_selected_files');
        Schema::dropIfExists('canvas_approvals');
        Schema::dropIfExists('canvas_files');
        Schema::dropIfExists('canvases');
    }
};