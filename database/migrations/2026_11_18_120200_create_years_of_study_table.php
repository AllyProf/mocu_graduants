<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Year of study (First Year, Second Year, Third Year).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('years_of_study', function (Blueprint $table) {
            $table->id();
            $table->string('name', 40)->unique();
            $table->unsignedTinyInteger('year_number')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('years_of_study');
    }
};
