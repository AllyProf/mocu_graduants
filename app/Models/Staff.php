<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Staff profile. Authentication password is stored on the related User only.
 */
class Staff extends Model
{
    protected $table = 'staff';

    protected $fillable = [
        'user_id',
        'staff_number',
        'full_name',
        'email',
        'phone',
        'gender',
        'department',
        'designation',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
