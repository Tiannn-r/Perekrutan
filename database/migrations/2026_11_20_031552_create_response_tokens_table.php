<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('response_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('interview_id')->constrained('interviews')->cascadeOnDelete();
            $table->string('token', 80)->unique();
            $table->boolean('sudah_dipakai')->default(false);
            $table->dateTime('kedaluwarsa_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('response_tokens');
    }
};
