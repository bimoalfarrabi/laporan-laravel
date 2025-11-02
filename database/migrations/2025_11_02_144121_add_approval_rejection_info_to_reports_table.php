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
        Schema::table('reports', function (Blueprint $table) {
            $table->unsignedBigInteger('approved_by_user_id')->nullable()->after('status');
            $table->timestamp('approved_at')->nullable()->after('approved_by_user_id');
            $table->unsignedBigInteger('rejected_by_user_id')->nullable()->after('approved_at');
            $table->timestamp('rejected_at')->nullable()->after('rejected_by_user_id');

            $table->foreign('approved_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('rejected_by_user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropForeign(['rejected_by_user_id']);
            $table->dropColumn(['approved_by_user_id', 'approved_at', 'rejected_by_user_id', 'rejected_at']);
        });
    }
};