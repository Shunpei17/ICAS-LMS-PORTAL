<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'content',
        'audience',
        'attachment_path',
        'created_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function scopeVisibleToAudience(Builder $query, string $audience): Builder
    {
        if ($audience === 'all') {
            return $query;
        }

        return $query->whereIn('audience', ['all', $audience]);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('announcement', $model->id, 'created'));
        });

        static::updated(function ($model) {
            event(new \App\Events\AdminModelChanged('announcement', $model->id, 'updated'));
        });

        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('announcement', $model->id, 'deleted'));
        });
    }
}
