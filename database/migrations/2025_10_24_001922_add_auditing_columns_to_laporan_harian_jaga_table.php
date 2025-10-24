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
        Schema::table('laporan_harian_jaga', function (Blueprint $table) {
            $table->foreignId('last_edited_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('status');
            $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->onDelete('set null')->after('last_edited_by_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laporan_harian_jaga', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_edited_by_user_id');
            $table->dropColumn('last_edited_by_user_id');
            $table->dropConstrainedForeignId('deleted_by_user_id');
            $table->dropColumn('deleted_by_user_id');
        });
    }
};
