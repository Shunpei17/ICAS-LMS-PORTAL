<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Events\AdminModelChanged;

class ForumReply extends Model
{
    protected $fillable = [
        'user_id',
        'forum_thread_id',
        'content',
        'is_visible',
        'is_flagged',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(ForumThread::class, 'forum_thread_id');
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new AdminModelChanged('forum_reply', $model->id, 'created'));
        });

        static::deleted(function ($model) {
            event(new AdminModelChanged('forum_reply', $model->id, 'deleted'));
        });
    }
}
