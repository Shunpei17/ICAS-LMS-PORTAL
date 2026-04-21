<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentModuleRecord extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'module_name',
        'module_code',
        'instructor',
        'schedule',
        'grade_percent',
        'documents_count',
        'upcoming_assessment_title',
        'upcoming_assessment_points',
        'upcoming_assessment_due_date',
        'upcoming_assessment_duration_minutes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'grade_percent' => 'decimal:2',
            'upcoming_assessment_due_date' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
