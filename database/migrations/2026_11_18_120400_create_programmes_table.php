<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Academic programmes linked to an academic level.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('programmes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_level_id')
                ->constrained('academic_levels')
                ->restrictOnDelete();
            $table->string('name', 180);
            $table->string('short_code', 40);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['short_code', 'academic_level_id']);
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('programmes');
    }
};
