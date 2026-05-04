<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'quiz',
        'assignment',
        'exam',
        'average',
        'remarks',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('grade', $model->id, 'created'));
        });

        // avoid noisy updates for grade edits; only broadcast create/delete
        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('grade', $model->id, 'deleted'));
        });
    }
}
