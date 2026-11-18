<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Academic levels (PhD → Certificate).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique();
            $table->string('code', 40)->nullable()->unique();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_levels');
    }
};
