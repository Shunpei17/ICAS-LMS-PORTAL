<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentRequest extends Model
{
    protected $fillable = ['user_id', 'document_type', 'purpose', 'urgency', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('document_request', $model->id, 'created'));
        });

        // only broadcast when status changes (important) to reduce noise
        static::updated(function ($model) {
            if ($model->isDirty('status')) {
                event(new \App\Events\AdminModelChanged('document_request', $model->id, 'status_changed'));
            }
        });

        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('document_request', $model->id, 'deleted'));
        });
    }
}
