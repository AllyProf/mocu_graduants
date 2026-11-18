<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Academic level lookup (PhD, Masters, Bachelor Degree, …).
 */
class AcademicLevel extends Model
{
    protected $fillable = [
        'name',
        'code',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(Programme::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
