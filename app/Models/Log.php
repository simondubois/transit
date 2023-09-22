<?php

namespace App\Models;

use App\Enums\LogStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property-read \Carbon\Carbon $created_at
 */
class Log extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'job_started_at' => 'datetime',
        'status' =>  LogStatus::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'job_type',
        'job_started_at',
        'status',
    ];

    /**
     * Get the parent holder model.
     * @return MorphTo<Model, Log>
     */
    public function holder(): MorphTo
    {
        return $this->morphTo();
    }
}
