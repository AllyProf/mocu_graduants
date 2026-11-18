<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Academic year calendar (e.g. 2025/2026).
 */
class AcademicYear extends Model
{
    protected $fillable = [
        'name',
        'starts_on',
        'ends_on',
        'is_current',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'starts_on' => 'date',
            'ends_on' => 'date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
