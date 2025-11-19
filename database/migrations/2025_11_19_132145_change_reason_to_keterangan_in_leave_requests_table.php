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
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('reason', 'keterangan');
        });

        // It's better to do this in a separate step to ensure compatibility with all database systems.
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('keterangan')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('keterangan')->nullable(false)->change();
        });
        
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->renameColumn('keterangan', 'reason');
        });
    }
};