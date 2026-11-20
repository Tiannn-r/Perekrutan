<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reschedule_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->text('alasan');
            $table->text('saran_waktu')->nullable();
            $table->string('status_persetujuan')->default('Pending'); // Pending, Approved, Rejected
            $table->string('ditangani_oleh')->nullable();
            $table->dateTime('diajukan_at')->useCurrent();
            $table->dateTime('ditangani_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reschedule_requests');
    }
};
