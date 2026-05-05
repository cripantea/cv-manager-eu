<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'cv_id',
        'project_name',
        'employer',
        'client',
        'start_date',
        'end_date',
        'project_size',
        'description',
        'roles',
        'responsibilities',
        'order',
    ];

    protected function casts(): array
    {
        return [
            'start_date'       => 'date',
            'end_date'         => 'date',
            'roles'            => 'array',
            'responsibilities' => 'array',
        ];
    }

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }

    public function technologies(): HasMany
    {
        return $this->hasMany(ProjectTechnology::class);
    }

    public function effectiveMonths(): int
    {
        $end = $this->end_date ?? Carbon::now();

        return (int) $this->start_date->diffInMonths($end);
    }
}
