<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Student / graduand record. Students do not have login passwords.
 */
class Student extends Model
{
    protected $fillable = [
        'registration_number',
        'full_name',
        'gender',
        'programme_id',
        'academic_level_id',
        'year_of_study_id',
        'academic_year_id',
        'gpa',
        'is_best_student',
        'best_award',
        'award',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'gpa' => 'float',
            'is_best_student' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function yearOfStudy(): BelongsTo
    {
        return $this->belongsTo(YearOfStudy::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    /**
     * Shape used by existing Blade/PDF templates (legacy field names).
     */
    public function toListObject(): object
    {
        return (object) [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'study_level' => $this->academicLevel?->name,
            'year_of_study' => $this->yearOfStudy?->name,
            'programme' => $this->programme?->name,
            'programme_short' => $this->programme?->short_code,
            'gpa' => $this->gpa,
            'is_best_student' => $this->is_best_student,
            'best_award' => $this->best_award,
            'award' => $this->award,
            'registration_number' => $this->registration_number,
            'academic_year' => $this->academicYear?->name,
        ];
    }
}
