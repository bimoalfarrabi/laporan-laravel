<?php

use App\Models\User;
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
        Schema::create('report_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Nama jenis laporan (misal: Laporan Patroli)
            $table->string('slug')->unique(); // Slug untuk identifikasi internal/URL
            $table->text('description')->nullable(); // Deskripsi jenis laporan
            $table->json('fields_schema'); // Skema bidang laporan dalam format JSON
            $table->boolean('is_active')->default(true); // Status aktif/non-aktif
            $table->unsignedBigInteger('created_by_user_id')->nullable();
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('updated_by_user_id')->nullable();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_types');
    }
};
