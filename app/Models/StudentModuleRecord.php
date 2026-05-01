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
        'section',
        'enrollment_status',
        'grade_percent',
        'grade_verified',
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
            'grade_verified' => 'boolean',
            'upcoming_assessment_due_date' => 'date',
            'enrollment_status' => 'string',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::updating(function (StudentModuleRecord $record) {
            if ($record->getOriginal('grade_verified') === true && $record->isDirty('grade_percent')) {
                throw new \Exception('Cannot modify a verified grade.');
            }
        });
    }
}
