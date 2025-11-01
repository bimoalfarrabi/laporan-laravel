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
        Schema::create('report_type_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_type_id')->constrained()->onDelete('cascade');
            $table->string('label');
            $table->string('name');
            $table->string('type');
            $table->boolean('required')->default(false);
            $table->integer('order');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_type_fields');
    }
};
