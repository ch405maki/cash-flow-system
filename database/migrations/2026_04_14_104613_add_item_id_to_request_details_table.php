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
        Schema::table('request_details', function (Blueprint $table) {
            // Add item_id column after id or after request_id
            $table->unsignedBigInteger('item_id')->nullable()->after('request_id');
            
            // Optional: Add foreign key if you have an items table locally
            // $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();
            
            // Optional: Add index for faster queries
            $table->index('item_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('request_details', function (Blueprint $table) {
            // Drop foreign key first if you added it
            // $table->dropForeign(['item_id']);
            
            // Drop the column
            $table->dropColumn('item_id');
        });
    }
};