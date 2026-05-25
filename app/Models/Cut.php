<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Str;

final class Cut extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'video_id', 'index', 'name', 'type', 'source',
        'start_seconds', 'end_seconds', 'duration_seconds', 'score', 'reason',
        'status_id', 'title', 'description', 'hashtags', 'rendered_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * @return BelongsTo<Video, $this>
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    /**
     * @return BelongsTo<Status, $this>
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class);
    }

    /**
     * @return HasMany<File, $this>
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * @return MorphMany<StatusLog, $this>
     */
    public function statusLogs(): MorphMany
    {
        return $this->morphMany(StatusLog::class, 'statusable');
    }

    protected static function booted(): void
    {
        self::creating(function (self $model): void {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'index' => 'integer',
            'start_seconds' => 'float',
            'end_seconds' => 'float',
            'duration_seconds' => 'float',
            'score' => 'float',
            'hashtags' => 'array',
            'rendered_at' => 'datetime',
        ];
    }
}
