<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kunjungan_tamu', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_petugas')->nullable()->constrained('users')->nullOnDelete();
            $table->string('nama');
            $table->string('nomor_telepon', 15);
            $table->string('nik', 16);
            $table->date('tanggal_lahir');
            $table->unsignedTinyInteger('umur');
            $table->string('keperluan', 30)->index();
            $table->text('detail_keperluan')->nullable();
            $table->boolean('status_selesai')->default(false)->index();
            $table->foreignId('id_validator')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->timestamp('waktu_divalidasi')->nullable();
            
            $table->unsignedTinyInteger('nilai_pelayanan')->nullable();
            $table->unsignedTinyInteger('nilai_kecepatan')->nullable();
            $table->unsignedTinyInteger('nilai_fasilitas')->nullable();
            $table->text('saran')->nullable();
            $table->json('jawaban_survei')->nullable();
            $table->timestamp('survey_waktu_dikirim')->nullable();
            
            $table->dateTime('waktu_kunjungan')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kunjungan_tamu');
    }
};
