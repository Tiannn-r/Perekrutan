<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('email')->index();
            $table->string('telepon');
            $table->string('posisi');
            $table->text('berkas')->nullable();
            $table->string('status')->default('New Application'); // New Application, Invited, Confirmed, Reschedule Requested, Declined, Expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
