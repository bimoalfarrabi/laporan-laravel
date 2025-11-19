<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("attendances", function (Blueprint $table) {
            $table->string("photo_in_path")->nullable()->change();
            $table->decimal('latitude_in', 10, 7)->nullable()->change();
            $table->decimal('longitude_in', 10, 7)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("attendances", function (Blueprint $table) {
            $table->string("photo_in_path")->change(); // Revert to non-nullable
            $table->decimal('latitude_in', 10, 7)->change(); // Revert to non-nullable
            $table->decimal('longitude_in', 10, 7)->change(); // Revert to non-nullable
        });
    }
};
