<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

final class StatusLog extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'statusable_type', 'statusable_id', 'from_status_id', 'to_status_id',
        'message', 'context', 'created_by',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function statusable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Status, $this>
     */
    public function fromStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'from_status_id');
    }

    /**
     * @return BelongsTo<Status, $this>
     */
    public function toStatus(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'to_status_id');
    }

    protected function casts(): array
    {
        return ['context' => 'array'];
    }
}
