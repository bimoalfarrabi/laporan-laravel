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
        Schema::table('report_types', function (Blueprint $table) {
            $table->integer('retention_months')->nullable()->after('is_active')->comment('Retention period in months. Null means forever.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('report_types', function (Blueprint $table) {
            $table->dropColumn('retention_months');
        });
    }
};
