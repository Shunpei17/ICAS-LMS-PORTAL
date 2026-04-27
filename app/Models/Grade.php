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
}
