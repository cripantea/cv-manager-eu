<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Training extends Model
{
    protected $table = 'trainings';

    protected $fillable = [
        'cv_id',
        'training_name',
        'company_institute',
        'date_followed',
        'certificate_obtained',
        'order',
    ];

    public function cv(): BelongsTo
    {
        return $this->belongsTo(Cv::class);
    }
}
