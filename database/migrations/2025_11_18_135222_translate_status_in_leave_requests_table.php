<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('status')->default('menunggu persetujuan')->change();
        });

        DB::table('leave_requests')->where('status', 'pending')->update(['status' => 'menunggu persetujuan']);
        DB::table('leave_requests')->where('status', 'approved')->update(['status' => 'disetujui']);
        DB::table('leave_requests')->where('status', 'rejected')->update(['status' => 'ditolak']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('leave_requests')->where('status', 'menunggu persetujuan')->update(['status' => 'pending']);
        DB::table('leave_requests')->where('status', 'disetujui')->update(['status' => 'approved']);
        DB::table('leave_requests')->where('status', 'ditolak')->update(['status' => 'rejected']);

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });
    }
};
