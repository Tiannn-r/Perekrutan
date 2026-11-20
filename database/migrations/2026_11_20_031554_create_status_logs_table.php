<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('candidate_id')->nullable()->constrained('candidates')->cascadeOnDelete();
            $table->foreignId('interview_id')->nullable()->constrained('interviews')->cascadeOnDelete();
            $table->string('dari_status');
            $table->string('ke_status');
            $table->string('aktor')->default('Sistem'); // HR, Kandidat, Sistem
            $table->text('keterangan')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_logs');
    }
};
