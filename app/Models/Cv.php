<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cv extends Model
{
    protected $fillable = [
        'user_id',
        'status',
        'ai_import_count',
        'first_name',
        'last_name',
        'birth_date',
        'nationality',
        'current_function',
        'profile_for',
        'date_available',
        'education_level',
        'years_after_secondary',
        'it_career_start',
        'profile_summary',
        'languages',
        'contract_type',
        'proposed_level',
        'standards_certificates',
    ];

    protected function casts(): array
    {
        return [
            'languages'       => 'array',
            'birth_date'      => 'date:Y-m-d',
            'it_career_start' => 'date:Y-m-d',
            'date_available'  => 'date:Y-m-d',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->orderBy('order');
    }

    public function educations(): HasMany
    {
        return $this->hasMany(Education::class)->orderBy('order');
    }

    public function trainings(): HasMany
    {
        return $this->hasMany(Training::class)->orderBy('order');
    }
}
