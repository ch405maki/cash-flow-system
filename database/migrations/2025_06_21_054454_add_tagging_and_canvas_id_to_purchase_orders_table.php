<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** @noinspection PhpUnused */
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            // 1) simple nullable string
            $table->string('tagging')->nullable()->after('account_id');
            $table->foreignId('canvas_id')
                ->nullable()
                ->after('account_id') 
                ->constrained('canvases')
                ->nullOnDelete();     // SET NULL on delete
        });
    }

    /** @noinspection PhpUnused */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'canvas_id')) {
                // Try dropping the foreign key *only if* it exists
                try {
                    $table->dropForeign(['canvas_id']);
                } catch (\Throwable $e) {
                    // suppress error if foreign key doesn't exist
                }

                $table->dropColumn('canvas_id');
            }

            if (Schema::hasColumn('purchase_orders', 'tagging')) {
                $table->dropColumn('tagging');
            }
        });
    }

};
