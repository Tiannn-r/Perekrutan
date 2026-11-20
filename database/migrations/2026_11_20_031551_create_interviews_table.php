<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->string('tahap')->default('Tahap 1');
            $table->string('kategori')->default('online'); // online, offline
            $table->dateTime('tanggal_waktu');
            $table->integer('durasi_menit')->default(45);
            $table->string('interviewer');
            $table->text('link_meeting')->nullable(); // for online (Google Meet)
            $table->text('lokasi')->nullable(); // for offline (ruangan/alamat)
            $table->string('status')->default('Invited'); // Invited, Confirmed, Reschedule Requested, Declined, Expired
            $table->integer('jumlah_reschedule')->default(0);
            $table->dateTime('batas_respon')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interviews');
    }
};
