<?php

use App\Models\ReportType;
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
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_type_id')->constrained('report_types')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->json('data'); // Menyimpan data laporan sesuai skema dari report_types
            $table->string('status')->default('draft'); // Status laporan (draft, submitted, approved, rejected)
            $table->timestamps();
            $table->softDeletes(); // soft delete

            // audit
            $table->unsignedBigInteger('last_edited_by_user_id')->nullable();
            $table->foreign('last_edited_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unsignedBigInteger('deleted_by_user_id')->nullable();
            $table->foreign('deleted_by_user_id')->references('id')->on('users')->onDelete('set null');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
