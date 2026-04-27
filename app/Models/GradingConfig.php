<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradingConfig extends Model
{
    protected $fillable = [
        'quiz_weight',
        'assignment_weight',
        'exam_weight',
        'passing_grade',
        'grading_scale',
    ];
}
