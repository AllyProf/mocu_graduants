<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Missing-name complaints submitted from list pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->uuid('public_id')->unique();
            $table->string('full_name', 120);
            $table->string('registration_number', 60)->nullable();
            $table->foreignId('academic_level_id')->nullable()->constrained('academic_levels')->nullOnDelete();
            $table->foreignId('programme_id')->nullable()->constrained('programmes')->nullOnDelete();
            $table->string('study_level', 80)->nullable();
            $table->string('programme', 80)->nullable();
            $table->string('country_code', 8);
            $table->string('phone', 20);
            $table->string('contact', 40);
            $table->text('message');
            $table->string('list_type', 40)->default('graduands');
            $table->string('searched_name', 120)->nullable();
            $table->string('status', 40)->default('pending');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['list_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
