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
        Schema::create('laporan_harian_jaga', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete(); // ID pengguna yang membuat laporan
            $table->date('tanggal_jaga'); // Tanggal jaga
            $table->string('shift'); // Shift (Pagi, Siang, Malam)
            $table->string('cuaca')->nullable(); // Kondisi cuaca
            $table->longText('kejadian_menonjol')->nullable(); // Kejadian menonjol selama shift
            $table->longText('catatan_serah_terima')->nullable(); // Catatan serah terima
            $table->string('status')->default('draft'); // Status laporan: draft, submitted, approved, rejected
            $table->timestamps();
            $table->softDeletes(); // Untuk mengaktifkan soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan_harian_jaga');
    }
};
