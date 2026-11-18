<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Academic programme linked to an academic level.
 */
class Programme extends Model
{
    protected $fillable = [
        'academic_level_id',
        'name',
        'short_code',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
