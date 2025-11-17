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
        Schema::table('attendances', function (Blueprint $table) {
            // Rename existing columns for "in"
            $table->renameColumn('photo_path', 'photo_in_path');
            $table->renameColumn('latitude', 'latitude_in');
            $table->renameColumn('longitude', 'longitude_in');

            // Modify timestamps
            $table->dateTime('time_in')->nullable()->after('user_id');
            $table->dateTime('time_out')->nullable()->after('time_in');

            // Add new columns for "out"
            $table->string('photo_out_path')->nullable()->after('photo_in_path');
            $table->decimal('latitude_out', 10, 7)->nullable()->after('latitude_in');
            $table->decimal('longitude_out', 10, 7)->nullable()->after('longitude_in');

            // Add shift column
            $table->string('shift')->nullable()->after('user_id');
        });

        // Data migration: Move data from created_at to time_in and drop old timestamp columns
        \Illuminate\Support\Facades\DB::statement('UPDATE attendances SET time_in = created_at');

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            // Add timestamps back
            $table->timestamps();
        });

        // Data migration: Move data from time_in to created_at
        \Illuminate\Support\Facades\DB::statement('UPDATE attendances SET created_at = time_in');

        Schema::table('attendances', function (Blueprint $table) {
            // Rename columns back
            $table->renameColumn('photo_in_path', 'photo_path');
            $table->renameColumn('latitude_in', 'latitude');
            $table->renameColumn('longitude_in', 'longitude');

            // Drop added columns
            $table->dropColumn('time_in');
            $table->dropColumn('time_out');
            $table->dropColumn('photo_out_path');
            $table->dropColumn('latitude_out');
            $table->dropColumn('longitude_out');
            $table->dropColumn('shift');
        });
    }
};
