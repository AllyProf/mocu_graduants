<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Students / graduands. No password — students do not authenticate.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('registration_number', 60)->nullable()->unique();
            $table->string('full_name', 150);
            $table->string('gender', 20)->nullable();

            $table->foreignId('programme_id')
                ->constrained('programmes')
                ->restrictOnDelete();
            $table->foreignId('academic_level_id')
                ->constrained('academic_levels')
                ->restrictOnDelete();
            $table->foreignId('year_of_study_id')
                ->nullable()
                ->constrained('years_of_study')
                ->nullOnDelete();
            $table->foreignId('academic_year_id')
                ->constrained('academic_years')
                ->restrictOnDelete();

            $table->decimal('gpa', 4, 2)->nullable();
            $table->boolean('is_best_student')->default(false);
            $table->string('best_award', 40)->nullable(); // overall | programme
            $table->string('award', 120)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['academic_level_id', 'programme_id']);
            $table->index(['is_best_student', 'gpa']);
            $table->index('full_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
