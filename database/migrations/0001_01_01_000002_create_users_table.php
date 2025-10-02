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
        Schema::create('profile_pictures', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->string('file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('email')->unique()->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->enum('role', [
                'admin', 
                'executive_director', 
                'department_head',
                'accounting', 
                'bursar',
                'audit',
                'property_custodian',
                'purchasing', 
                'staff'])->default('staff');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedBigInteger('profile_picture_id')->nullable();
            $table->foreignId('department_id')
                    ->nullable()
                    ->constrained('departments')
                    ->onDelete('set null');
            $table->foreignId('access_id')
                    ->nullable()
                    ->constrained('accesses')
                    ->onDelete('set null');
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('profile_picture_id')
                ->references('id')
                ->on('profile_pictures')
                ->nullOnDelete(); 
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_pictures');
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
