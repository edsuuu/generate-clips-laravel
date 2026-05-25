<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Status extends Model
{
    /** @use HasFactory<Factory> */
    use HasFactory;

    protected $fillable = ['key', 'label', 'description', 'sort_order'];

    public static function idFor(string $key): int
    {
        $id = self::query()->where('key', $key)->value('id');

        return is_numeric($id) ? (int) $id : 0;
    }

    public static function findByKey(string $key): ?self
    {
        return self::query()->where('key', $key)->first();
    }
}
