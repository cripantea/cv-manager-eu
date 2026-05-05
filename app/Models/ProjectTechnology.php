<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectTechnology extends Model
{
    protected $table = 'project_technologies';

    protected $fillable = [
        'project_id',
        'technology_name',
        'competence',
    ];

    protected function casts(): array
    {
        return [
            'competence' => 'integer',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
