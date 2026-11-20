<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->nullable()->constrained('interviews')->cascadeOnDelete();
            $table->string('penerima');
            $table->string('tipe'); // Undangan, Konfirmasi, Reschedule, Penolakan, Jadwal Baru
            $table->string('status_kirim')->default('Sent'); // Sent, Failed
            $table->text('pesan_error')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
