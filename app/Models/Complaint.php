<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Missing-name complaint submitted from graduands / best-students pages.
 */
class Complaint extends Model
{
    protected $fillable = [
        'public_id',
        'full_name',
        'registration_number',
        'academic_level_id',
        'programme_id',
        'study_level',
        'programme',
        'country_code',
        'phone',
        'contact',
        'message',
        'list_type',
        'searched_name',
        'status',
        'ip_address',
    ];

    public function academicLevel(): BelongsTo
    {
        return $this->belongsTo(AcademicLevel::class);
    }

    public function programmeRelation(): BelongsTo
    {
        return $this->belongsTo(Programme::class, 'programme_id');
    }
}
