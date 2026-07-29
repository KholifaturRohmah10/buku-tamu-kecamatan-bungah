<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survei_tamu', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('id_pengguna')->nullable()->unique()->constrained('users')->nullOnDelete();
            $table->foreignId('id_kunjungan_tamu')->nullable()->constrained('kunjungan_tamu')->nullOnDelete();
            $table->unsignedTinyInteger('nilai_pelayanan')->nullable();
            $table->unsignedTinyInteger('nilai_kecepatan')->nullable();
            $table->unsignedTinyInteger('nilai_fasilitas')->nullable();
            $table->text('saran')->nullable();
            $table->text('kritik')->nullable();
            $table->json('jawaban_survei')->nullable();
            $table->timestamp('waktu_dikirim')->nullable()->index();
            $table->timestamps();
        });

        Schema::table('kunjungan_tamu', function (Blueprint $table): void {
            $table->foreignId('id_survei_tamu')
                ->nullable()
                ->after('id_petugas')
                ->constrained('survei_tamu')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('kunjungan_tamu', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_survei_tamu');
        });

        Schema::dropIfExists('survei_tamu');
    }
};
