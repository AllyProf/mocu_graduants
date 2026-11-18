<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Year of study lookup (First Year, Second Year, Third Year).
 */
class YearOfStudy extends Model
{
    protected $table = 'years_of_study';

    protected $fillable = [
        'name',
        'year_number',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'year_number' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }
}
