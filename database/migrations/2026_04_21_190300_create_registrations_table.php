<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('master_class_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'master_class_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('registrations');
    }
};
