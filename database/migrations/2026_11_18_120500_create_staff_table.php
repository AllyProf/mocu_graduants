<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Staff profiles. Login password lives on users (user_id), not on staff.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('staff_number', 40)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('email', 150)->nullable()->unique();
            $table->string('phone', 30)->nullable();
            $table->string('gender', 20)->nullable();
            $table->string('department', 120)->nullable();
            $table->string('designation', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff');
    }
};
