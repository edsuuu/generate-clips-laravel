<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

final class Transcript extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    protected $fillable = [
        'uuid', 'video_id', 'language', 'duration_seconds', 'raw_text',
        'validated_text', 'edited_text', 'active_text_source',
        'is_validated_by_ai', 'is_confirmed_by_user', 'confirmed_at', 'confirmed_by',
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

    /** Texto que vale agora: editado > validado > bruto. */
    public function activeText(): ?string
    {
        return $this->edited_text ?: ($this->validated_text ?: $this->raw_text);
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
            'duration_seconds' => 'float',
            'is_validated_by_ai' => 'boolean',
            'is_confirmed_by_user' => 'boolean',
            'confirmed_at' => 'datetime',
        ];
    }
}
