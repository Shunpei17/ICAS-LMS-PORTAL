<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\AdminModelChanged;

use Illuminate\Support\Facades\Event;

class ForumThread extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'content',
        'category',
        'status',
        'views',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new AdminModelChanged('forum_thread', $model->id, 'created'));
        });

        // keep updates quieter; only broadcast create/delete
        static::deleted(function ($model) {
            event(new AdminModelChanged('forum_thread', $model->id, 'deleted'));
        });
    }
}
