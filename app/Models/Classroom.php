<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Classroom extends Model
{
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'faculty_user_id',
        'name',
        'code',
        'schedule',
        'description',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'string',
        ];
    }

    public function faculty(): BelongsTo
    {
        return $this->belongsTo(User::class, 'faculty_user_id');
    }

    /**
     * Students enrolled in this classroom.
     */
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'classroom_students', 'classroom_id', 'user_id')
            ->withPivot('enrolled_at')
            ->withTimestamps();
    }

    /**
     * Count of enrolled students.
     */
    public function getStudentCountAttribute(): int
    {
        return $this->students()->count();
    }

    /**
     * Average grade % sourced from student_module_records matching this classroom's code.
     */
    public function getAverageGradeAttribute(): ?float
    {
        $avg = StudentModuleRecord::where('module_code', $this->code)
            ->whereNotNull('grade_percent')
            ->avg('grade_percent');

        return $avg !== null ? (float) $avg : null;
    }

    protected static function booted(): void
    {
        static::created(function ($model) {
            event(new \App\Events\AdminModelChanged('classroom', $model->id, 'created'));
        });

        // only notify on create/delete to avoid frequent update noise
        static::deleted(function ($model) {
            event(new \App\Events\AdminModelChanged('classroom', $model->id, 'deleted'));
        });
    }
}
